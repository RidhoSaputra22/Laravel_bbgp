<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\ChoiceOptionNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentAttemptService
{
    public function __construct(
        private readonly AssessmentScoringService $scoringService,
        private readonly AssessmentAutoScoringService $autoScoringService
    ) {}

    public function submit(AssessmentAttempt $attempt, array $answers, array $files): AssessmentAttempt
    {
        if ($attempt->status === 'submitted') {
            return $attempt->load([
                'answers',
                'target.assignment.assessments.forms.fields',
                'target.session',
                'target.guru',
            ]);
        }

        $snapshot = $attempt->structure_snapshot ?? [];
        $fields = $this->flattenFields($snapshot);
        $normalizedAnswers = $this->validateAndNormalizeAnswers($attempt, $fields, $answers, $files);
        $submittedAt = now();

        DB::transaction(function () use ($attempt, $normalizedAnswers, $snapshot, $submittedAt) {
            $submittedFieldIds = array_keys($normalizedAnswers);

            if ($submittedFieldIds === []) {
                $attempt->answers()->delete();
            } else {
                $attempt->answers()
                    ->whereNotIn('assessment_form_field_id', $submittedFieldIds)
                    ->delete();
            }

            foreach ($normalizedAnswers as $fieldId => $normalizedAnswer) {
                $persistedAnswer = $this->prepareAnswerForPersistence($attempt, $normalizedAnswer);

                AssessmentAttemptAnswer::updateOrCreate(
                    [
                        'assessment_attempt_id' => $attempt->id,
                        'assessment_form_field_id' => $fieldId,
                    ],
                    [
                        'assessment_id' => $persistedAnswer['assessment_id'],
                        'assessment_form_id' => $persistedAnswer['assessment_form_id'],
                        'answer_text' => $persistedAnswer['answer_text'],
                        'answer_payload' => $persistedAnswer['answer_payload'],
                        'answer_file_path' => $persistedAnswer['answer_file_path'],
                        'answered_at' => $submittedAt,
                    ]
                );
            }

            $freshAnswers = $attempt->answers()->get();
            $attempt->setRelation('answers', $freshAnswers);
            $this->autoScoringService->scoreAttempt($attempt);
            $freshAnswers = $attempt->answers()->get();
            $attempt->setRelation('answers', $freshAnswers);

            $summary = $this->buildSummaryFromSnapshot(
                $snapshot,
                $freshAnswers,
                $attempt->started_at ?: $submittedAt,
                $submittedAt
            );
            $scoringSummary = $this->scoringService->buildSummary($attempt);

            $attempt->forceFill([
                'status' => 'submitted',
                'result_summary' => $summary,
                'scoring_summary' => $scoringSummary,
                'answered_questions' => (int) $summary['answered_questions'],
                'answered_required_questions' => (int) $summary['answered_required_questions'],
                'submitted_at' => $submittedAt,
                'last_answered_at' => $submittedAt,
            ])->save();

            $target = $attempt->target;

            if ($target) {
                $target->forceFill([
                    'status' => 'selesai',
                    'started_at' => $target->started_at ?: $attempt->started_at ?: $submittedAt,
                    'submitted_at' => $submittedAt,
                ])->save();
            }
        });

        return $attempt->fresh([
            'answers',
            'target.assignment.assessments.forms.fields',
            'target.session',
            'target.guru',
        ]);
    }

    public function buildResultSummary(AssessmentAttempt $attempt): array
    {
        if ($attempt->result_summary) {
            return $attempt->result_summary;
        }

        return $this->buildSummaryFromSnapshot(
            $attempt->structure_snapshot ?? [],
            $attempt->answers,
            $attempt->started_at,
            $attempt->submitted_at
        );
    }

    public function buildScoringSummary(AssessmentAttempt $attempt): array
    {
        if ($attempt->scoring_summary) {
            return $attempt->scoring_summary;
        }

        $summary = $this->scoringService->buildSummary($attempt->loadMissing('answers'));

        if ($attempt->exists) {
            $attempt->forceFill([
                'scoring_summary' => $summary,
            ])->save();
        }

        return $summary;
    }

    public function refreshScoringSummary(AssessmentAttempt $attempt): array
    {
        $summary = $this->scoringService->buildSummary($attempt->loadMissing('answers'));

        if ($attempt->exists) {
            $attempt->forceFill([
                'scoring_summary' => $summary,
            ])->save();
        }

        return $summary;
    }

    public function buildAnswerLookup(AssessmentAttempt $attempt): array
    {
        return $attempt->answers
            ->mapWithKeys(function (AssessmentAttemptAnswer $answer) {
                return [
                    $answer->assessment_form_field_id => [
                        'text' => $answer->answer_text,
                        'payload' => $answer->answer_payload ?? [],
                        'file_path' => $answer->answer_file_path,
                        'file_url' => $answer->answer_file_path ? asset('storage/'.$answer->answer_file_path) : null,
                        'rows' => data_get($answer->answer_payload ?? [], 'rows', []),
                        'columns' => data_get($answer->answer_payload ?? [], 'columns', []),
                        'auto_score' => $answer->auto_score,
                        'auto_score_reason' => $answer->auto_score_reason,
                        'auto_score_metadata' => $answer->auto_score_metadata ?? [],
                        'auto_score_confidence' => data_get($answer->auto_score_metadata ?? [], 'confidence'),
                        'assessor_score' => $answer->assessor_score,
                        'assessor_notes' => $answer->assessor_notes,
                        'assessor_score_label' => $answer->assessor_score
                            ? \App\Enum\LevelKompetensi::tryFrom((int) $answer->assessor_score)?->label()
                            : null,
                        'final_score' => is_numeric($answer->assessor_score) ? (float) $answer->assessor_score : $answer->auto_score,
                        'final_score_label' => is_numeric($answer->assessor_score)
                            ? \App\Enum\LevelKompetensi::fromScore((float) $answer->assessor_score)?->label()
                            : \App\Enum\LevelKompetensi::fromScore((float) $answer->auto_score)?->label(),
                        'answered_at' => $answer->answered_at?->format('d M Y H:i'),
                    ],
                ];
            })
            ->all();
    }

    private function validateAndNormalizeAnswers(
        AssessmentAttempt $attempt,
        array $fields,
        array $answers,
        array $files
    ): array {
        $messages = [];
        $normalized = [];

        foreach ($fields as $field) {
            $fieldId = (string) $field['id'];
            $fieldKey = 'answers.'.$fieldId;
            $fieldType = $field['tipe_field'];
            $fieldLabel = $field['label'];
            $isRequired = (bool) ($field['is_required'] ?? false);
            $uploadedFile = $files[$fieldId] ?? null;

            if ($fieldType === 'file') {
                if ($isRequired && ! $uploadedFile) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} wajib diunggah.";

                    continue;
                }

                if (! $uploadedFile) {
                    continue;
                }

                if (! $uploadedFile instanceof UploadedFile || ! $uploadedFile->isValid()) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                    $messages[$fieldKey] = "File untuk pertanyaan {$fieldLabel} maksimal 5 MB.";

                    continue;
                }

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => $uploadedFile->getClientOriginalName(),
                    'answer_payload' => [
                        'type' => 'file',
                        'original_name' => $uploadedFile->getClientOriginalName(),
                    ],
                    'answer_file_path' => null,
                    'uploaded_file' => $uploadedFile,
                ];

                continue;
            }

            if ($fieldType === 'checkbox') {
                $selectedValues = collect(Arr::wrap($answers[$fieldId] ?? []))
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();

                if ($isRequired && $selectedValues === []) {
                    $messages[$fieldKey] = "Minimal pilih satu jawaban untuk pertanyaan {$fieldLabel}.";

                    continue;
                }

                if ($selectedValues === []) {
                    continue;
                }

                $normalizedOptions = ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []);
                $allowedValues = collect($normalizedOptions)
                    ->flatMap(fn (array $option) => $option['aliases'] ?? [])
                    ->map(fn ($value) => (string) $value)
                    ->unique()
                    ->all();

                $invalidValues = array_diff($selectedValues, $allowedValues);

                if ($invalidValues !== []) {
                    $messages[$fieldKey] = "Ada pilihan yang tidak valid pada pertanyaan {$fieldLabel}.";

                    continue;
                }

                $selectedOptions = collect($normalizedOptions)
                    ->filter(function (array $option) use ($selectedValues) {
                        return $selectedValues->contains(fn ($selectedValue) => in_array((string) $selectedValue, $option['aliases'] ?? [], true));
                    })
                    ->values();

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => implode(', ', $selectedValues),
                    'answer_payload' => [
                        'type' => 'checkbox',
                        'values' => $selectedValues,
                        'selected_options' => $selectedOptions->map(fn (array $option) => array_filter([
                            'label' => $option['label'] ?? null,
                            'value' => $option['value'] ?? null,
                            'score' => $option['score'] ?? null,
                        ], static fn ($value) => $value !== null && $value !== ''))->all(),
                    ],
                    'answer_file_path' => null,
                ];

                continue;
            }

            if ($fieldType === 'repeater') {
                $normalizedRepeater = $this->normalizeRepeaterAnswer($field, $answers[$fieldId] ?? null);

                if ($normalizedRepeater['message']) {
                    $messages[$fieldKey] = $normalizedRepeater['message'];

                    continue;
                }

                if ($normalizedRepeater['rows'] === []) {
                    continue;
                }

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => count($normalizedRepeater['rows']).' entri',
                    'answer_payload' => [
                        'type' => 'repeater',
                        'rows' => $normalizedRepeater['rows'],
                        'columns' => $normalizedRepeater['columns'],
                        'row_count' => count($normalizedRepeater['rows']),
                    ],
                    'answer_file_path' => null,
                ];

                continue;
            }

            $value = $answers[$fieldId] ?? null;
            $textValue = is_array($value) ? '' : trim((string) ($value ?? ''));
            $matchedOption = null;

            if ($isRequired && $textValue === '') {
                $messages[$fieldKey] = "Jawaban untuk pertanyaan {$fieldLabel} wajib diisi.";

                continue;
            }

            if ($textValue === '') {
                continue;
            }

            if ($fieldType === 'email' && ! filter_var($textValue, FILTER_VALIDATE_EMAIL)) {
                $messages[$fieldKey] = "Format email pada pertanyaan {$fieldLabel} tidak valid.";

                continue;
            }

            if ($fieldType === 'number' && ! is_numeric($textValue)) {
                $messages[$fieldKey] = "Jawaban pada pertanyaan {$fieldLabel} harus berupa angka.";

                continue;
            }

            if ($fieldType === 'date') {
                try {
                    $date = Carbon::createFromFormat('Y-m-d', $textValue);
                } catch (\Throwable $exception) {
                    $date = null;
                }

                if (! $date || $date->format('Y-m-d') !== $textValue) {
                    $messages[$fieldKey] = "Format tanggal pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }
            }

            if ($fieldType === 'radio') {
                $matchedOption = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
                    ->first(function (array $option) use ($textValue) {
                        $aliases = collect($option['aliases'] ?? [])
                            ->map(fn ($value) => trim((string) $value))
                            ->filter(fn ($value) => $value !== '')
                            ->all();

                        return in_array($textValue, $aliases, true);
                    });

                if (! is_array($matchedOption)) {
                    $messages[$fieldKey] = "Pilihan jawaban pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                $textValue = trim((string) ($matchedOption['value'] ?? $textValue));

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => $textValue,
                    'answer_payload' => array_filter([
                        'type' => 'radio',
                        'value' => $textValue,
                        'label' => trim((string) ($matchedOption['label'] ?? '')) ?: null,
                        'score' => is_numeric($matchedOption['score'] ?? null) ? (float) $matchedOption['score'] : null,
                        'level_kompetensi' => $matchedOption['level_kompetensi'] ?? null,
                        'level_kompetensi_label' => $matchedOption['level_kompetensi_label'] ?? null,
                    ], static fn ($value) => $value !== null && $value !== ''),
                    'answer_file_path' => null,
                ];

                continue;
            }

            if ($fieldType === 'select') {
                $matchedOption = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
                    ->first(function (array $option) use ($textValue) {
                        return in_array($textValue, $option['aliases'] ?? [], true);
                    });

                if (! is_array($matchedOption)) {
                    $messages[$fieldKey] = "Pilihan jawaban pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }

                $textValue = trim((string) ($matchedOption['value'] ?? $textValue));
            }

            $normalized[(int) $fieldId] = [
                'assessment_id' => $field['assessment_id'],
                'assessment_form_id' => $field['assessment_form_id'],
                'answer_text' => $textValue,
                'answer_payload' => array_filter([
                    'type' => $fieldType,
                    'value' => $textValue,
                    'label' => isset($matchedOption) && is_array($matchedOption)
                        ? (trim((string) ($matchedOption['label'] ?? '')) ?: null)
                        : null,
                    'score' => isset($matchedOption) && is_array($matchedOption) && is_numeric($matchedOption['score'] ?? null)
                        ? (float) $matchedOption['score']
                        : null,
                ], static fn ($value) => $value !== null && $value !== ''),
                'answer_file_path' => null,
            ];
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return $normalized;
    }

    private function normalizeRepeaterAnswer(array $field, mixed $value): array
    {
        $config = is_array($field['opsi_field'] ?? null) ? $field['opsi_field'] : [];
        $columns = collect($config['columns'] ?? [])
            ->filter(fn ($column) => is_array($column))
            ->map(function (array $column) {
                $columnName = trim((string) ($column['nama_field'] ?? ''));
                $columnLabel = trim((string) ($column['label'] ?? ''));

                return [
                    'label' => $columnLabel !== '' ? $columnLabel : $columnName,
                    'nama_field' => $columnName,
                    'tipe_field' => trim((string) ($column['tipe_field'] ?? 'text')) ?: 'text',
                    'opsi_field' => is_array($column['opsi_field'] ?? null) ? $column['opsi_field'] : [],
                    'placeholder' => trim((string) ($column['placeholder'] ?? '')),
                    'is_required' => (bool) ($column['is_required'] ?? false),
                ];
            })
            ->filter(fn ($column) => $column['nama_field'] !== '')
            ->values()
            ->all();

        if ($columns === []) {
            return [
                'rows' => [],
                'columns' => [],
                'message' => "Konfigurasi tabel untuk pertanyaan {$field['label']} belum valid.",
            ];
        }

        $rows = collect(is_array($value) ? $value : [])
            ->filter(fn ($row) => is_array($row))
            ->values();
        $normalizedRows = [];
        $minRows = max((int) ($config['min_rows'] ?? 0), 0);
        $maxRows = max((int) ($config['max_rows'] ?? 0), 0);

        foreach ($rows as $rowIndex => $row) {
            $normalizedRow = [];
            $hasContent = false;

            foreach ($columns as $column) {
                $columnName = $column['nama_field'];
                $columnValue = is_array($row[$columnName] ?? null)
                    ? ''
                    : trim((string) ($row[$columnName] ?? ''));

                if ($columnValue !== '') {
                    $hasContent = true;
                }

                if ($columnValue !== '') {
                    if ($column['tipe_field'] === 'email' && ! filter_var($columnValue, FILTER_VALIDATE_EMAIL)) {
                        return [
                            'rows' => [],
                            'columns' => $columns,
                            'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                        ];
                    }

                    if ($column['tipe_field'] === 'number' && ! is_numeric($columnValue)) {
                        return [
                            'rows' => [],
                            'columns' => $columns,
                            'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} harus berupa angka.",
                        ];
                    }

                    if ($column['tipe_field'] === 'date') {
                        try {
                            $date = Carbon::createFromFormat('Y-m-d', $columnValue);
                        } catch (\Throwable $exception) {
                            $date = null;
                        }

                        if (! $date || $date->format('Y-m-d') !== $columnValue) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                            ];
                        }
                    }

                    if ($column['tipe_field'] === 'select') {
                        $allowedValues = collect($column['opsi_field'] ?? [])
                            ->map(fn ($optionValue) => (string) $optionValue)
                            ->all();

                        if ($allowedValues !== [] && ! in_array($columnValue, $allowedValues, true)) {
                            return [
                                'rows' => [],
                                'columns' => $columns,
                                'message' => "Pilihan {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} tidak valid.",
                            ];
                        }
                    }
                }

                $normalizedRow[$columnName] = $columnValue;
            }

            if (! $hasContent) {
                continue;
            }

            foreach ($columns as $column) {
                if ($column['is_required'] && ($normalizedRow[$column['nama_field']] ?? '') === '') {
                    return [
                        'rows' => [],
                        'columns' => $columns,
                        'message' => "Kolom {$column['label']} pada baris ".($rowIndex + 1)." untuk pertanyaan {$field['label']} wajib diisi.",
                    ];
                }
            }

            $normalizedRows[] = $normalizedRow;
        }

        if ((bool) ($field['is_required'] ?? false) && $normalizedRows === []) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Minimal isi satu baris pada pertanyaan {$field['label']}.",
            ];
        }

        if ($minRows > 0 && count($normalizedRows) < $minRows) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Pertanyaan {$field['label']} minimal harus memiliki {$minRows} baris terisi.",
            ];
        }

        if ($maxRows > 0 && count($normalizedRows) > $maxRows) {
            return [
                'rows' => [],
                'columns' => $columns,
                'message' => "Pertanyaan {$field['label']} maksimal hanya boleh memiliki {$maxRows} baris terisi.",
            ];
        }

        return [
            'rows' => $normalizedRows,
            'columns' => $columns,
            'message' => null,
        ];
    }

    private function prepareAnswerForPersistence(AssessmentAttempt $attempt, array $normalizedAnswer): array
    {
        $uploadedFile = $normalizedAnswer['uploaded_file'] ?? null;

        unset($normalizedAnswer['uploaded_file']);

        if ($uploadedFile instanceof UploadedFile) {
            $storedPath = $uploadedFile->store('assessment/attempts/'.$attempt->id, 'public');

            $normalizedAnswer['answer_payload']['path'] = $storedPath;
            $normalizedAnswer['answer_file_path'] = $storedPath;
        }

        return $normalizedAnswer;
    }

    private function buildSummaryFromSnapshot(
        array $snapshot,
        Collection $answers,
        ?Carbon $startedAt,
        ?Carbon $submittedAt
    ): array {
        $answerMap = $answers->keyBy('assessment_form_field_id');
        $assessmentBreakdown = [];
        $totalQuestions = 0;
        $requiredQuestions = 0;
        $answeredQuestions = 0;
        $answeredRequiredQuestions = 0;

        foreach ($snapshot['assessments'] ?? [] as $assessment) {
            $assessmentTotal = 0;
            $assessmentRequired = 0;
            $assessmentAnswered = 0;
            $assessmentAnsweredRequired = 0;
            $forms = [];

            foreach ($assessment['forms'] ?? [] as $form) {
                $formTotal = 0;
                $formRequired = 0;
                $formAnswered = 0;
                $formAnsweredRequired = 0;

                foreach ($form['fields'] ?? [] as $field) {
                    $formTotal++;
                    $assessmentTotal++;
                    $totalQuestions++;

                    $isRequired = (bool) ($field['is_required'] ?? false);

                    if ($isRequired) {
                        $formRequired++;
                        $assessmentRequired++;
                        $requiredQuestions++;
                    }

                    $answer = $answerMap->get($field['id']);

                    if ($this->answerHasContent($answer)) {
                        $formAnswered++;
                        $assessmentAnswered++;
                        $answeredQuestions++;

                        if ($isRequired) {
                            $formAnsweredRequired++;
                            $assessmentAnsweredRequired++;
                            $answeredRequiredQuestions++;
                        }
                    }
                }

                $forms[] = [
                    'id' => $form['id'],
                    'judul_form' => $form['judul_form'],
                    'kode_form' => $form['kode_form'],
                    'total_questions' => $formTotal,
                    'required_questions' => $formRequired,
                    'answered_questions' => $formAnswered,
                    'answered_required_questions' => $formAnsweredRequired,
                ];
            }

            $assessmentBreakdown[] = [
                'id' => $assessment['id'],
                'kode_assessment' => $assessment['kode_assessment'],
                'judul' => $assessment['judul'],
                'total_questions' => $assessmentTotal,
                'required_questions' => $assessmentRequired,
                'answered_questions' => $assessmentAnswered,
                'answered_required_questions' => $assessmentAnsweredRequired,
                'forms' => $forms,
            ];
        }

        $completionPercentage = $totalQuestions > 0
            ? (int) round(($answeredQuestions / $totalQuestions) * 100)
            : 0;

        $durationMinutes = ($startedAt && $submittedAt)
            ? $startedAt->diffInMinutes($submittedAt)
            : 0;

        return [
            'total_questions' => $totalQuestions,
            'required_questions' => $requiredQuestions,
            'answered_questions' => $answeredQuestions,
            'answered_required_questions' => $answeredRequiredQuestions,
            'completion_percentage' => $completionPercentage,
            'duration_minutes' => $durationMinutes,
            'submitted_at' => optional($submittedAt)->toIso8601String(),
            'assessment_breakdown' => $assessmentBreakdown,
        ];
    }

    private function answerHasContent(?AssessmentAttemptAnswer $answer): bool
    {
        if (! $answer) {
            return false;
        }

        if (filled($answer->answer_text) || filled($answer->answer_file_path)) {
            return true;
        }

        $payload = $answer->answer_payload ?? [];

        if (filled($payload['value'] ?? null)) {
            return true;
        }

        if (collect($payload['values'] ?? [])->filter(fn ($value) => filled($value))->isNotEmpty()) {
            return true;
        }

        return collect($payload['rows'] ?? [])
            ->filter(fn ($row) => is_array($row) && collect($row)->filter(fn ($value) => filled($value))->isNotEmpty())
            ->isNotEmpty();
    }

    private function flattenFields(array $snapshot): array
    {
        return collect($snapshot['assessments'] ?? [])
            ->flatMap(fn ($assessment) => $assessment['forms'] ?? [])
            ->flatMap(fn ($form) => $form['fields'] ?? [])
            ->values()
            ->all();
    }
}

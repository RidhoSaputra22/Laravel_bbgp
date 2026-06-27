<?php

namespace App\Services\Assesment;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentAttemptService
{
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
            $summary = $this->buildSummaryFromSnapshot(
                $snapshot,
                $freshAnswers,
                $attempt->started_at ?: $submittedAt,
                $submittedAt
            );

            $attempt->forceFill([
                'status' => 'submitted',
                'result_summary' => $summary,
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

    public function buildAnswerLookup(AssessmentAttempt $attempt): array
    {
        return $attempt->answers
            ->mapWithKeys(function (AssessmentAttemptAnswer $answer) {
                return [
                    $answer->assessment_form_field_id => [
                        'text' => $answer->answer_text,
                        'payload' => $answer->answer_payload ?? [],
                        'file_path' => $answer->answer_file_path,
                        'file_url' => $answer->answer_file_path ? asset('upload/'.$answer->answer_file_path) : null,
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

                $allowedValues = collect($field['opsi_field'] ?? [])
                    ->pluck('value')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                $invalidValues = array_diff($selectedValues, $allowedValues);

                if ($invalidValues !== []) {
                    $messages[$fieldKey] = "Ada pilihan yang tidak valid pada pertanyaan {$fieldLabel}.";

                    continue;
                }

                $normalized[(int) $fieldId] = [
                    'assessment_id' => $field['assessment_id'],
                    'assessment_form_id' => $field['assessment_form_id'],
                    'answer_text' => implode(', ', $selectedValues),
                    'answer_payload' => [
                        'type' => 'checkbox',
                        'values' => $selectedValues,
                    ],
                    'answer_file_path' => null,
                ];

                continue;
            }

            $value = $answers[$fieldId] ?? null;
            $textValue = is_array($value) ? '' : trim((string) ($value ?? ''));

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

            if (in_array($fieldType, ['select', 'radio'], true)) {
                $allowedValues = collect($field['opsi_field'] ?? [])
                    ->pluck('value')
                    ->map(fn ($optionValue) => (string) $optionValue)
                    ->all();

                if (! in_array($textValue, $allowedValues, true)) {
                    $messages[$fieldKey] = "Pilihan jawaban pada pertanyaan {$fieldLabel} tidak valid.";

                    continue;
                }
            }

            $normalized[(int) $fieldId] = [
                'assessment_id' => $field['assessment_id'],
                'assessment_form_id' => $field['assessment_form_id'],
                'answer_text' => $textValue,
                'answer_payload' => [
                    'type' => $fieldType,
                    'value' => $textValue,
                ],
                'answer_file_path' => null,
            ];
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return $normalized;
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

        return collect($payload['values'] ?? [])->filter(fn ($value) => filled($value))->isNotEmpty();
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

<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use App\Enum\LevelKompetensi;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Illuminate\Support\Collection;

class AssessmentScoringService
{
    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver
    ) {}

    public function buildSummary(AssessmentAttempt $attempt): array
    {
        $snapshot = $attempt->structure_snapshot ?? [];
        $answers = $attempt->relationLoaded('answers')
            ? $attempt->answers
            : $attempt->answers()->get();
        $answerMap = $answers->keyBy('assessment_form_field_id');
        $formSummaries = [];
        $totalScorableItems = 0;
        $scoredItems = 0;
        $pendingManualItems = 0;

        foreach ($snapshot['assessments'] ?? [] as $assessmentData) {
            $assessmentMeta = $this->metadataResolver->decorateAssessment($assessmentData);
            $instrument = AssessmentInstrumentType::tryFromMixed($assessmentMeta['instrument_type'] ?? null);

            foreach ($assessmentMeta['forms'] ?? [] as $formData) {
                $formMeta = $this->metadataResolver->decorateForm($formData, $assessmentMeta);
                $kompetensi = KompetensiGuru::tryFromMixed($formMeta['kompetensi'] ?? null);

                if (! ($formMeta['is_scoreable'] ?? false) || ! $instrument || ! $kompetensi) {
                    continue;
                }

                $fieldItems = [];
                $availableScores = [];
                $displayScores = [];
                $manualPendingCount = 0;
                $answeredScorableCount = 0;

                foreach ($formMeta['fields'] ?? [] as $fieldData) {
                    $answer = $answerMap->get($fieldData['id']);
                    $itemSummary = $this->buildFieldScoreSummary(
                        $instrument,
                        $formMeta,
                        $fieldData,
                        $answer
                    );

                    $fieldItems[] = $itemSummary;

                    if (! $itemSummary['answered']) {
                        continue;
                    }

                    $totalScorableItems++;
                    $answeredScorableCount++;

                    if ($itemSummary['manual_pending']) {
                        $pendingManualItems++;
                        $manualPendingCount++;
                    }

                    if ($itemSummary['score'] !== null) {
                        $scoredItems++;
                        $availableScores[] = $itemSummary['score'];
                        $displayScores[] = $itemSummary['score'];
                    }
                }

                $indicator = [
                    'kode' => $formMeta['indikator_kode'] ?? null,
                    'label' => $formMeta['indikator_label'] ?? null,
                ];
                $formScore = $manualPendingCount === 0
                    ? $this->average($availableScores)
                    : null;
                $displayFormScore = $this->average($displayScores);

                $formSummaries[] = [
                    'assessment_id' => $assessmentMeta['id'] ?? null,
                    'assessment_title' => $assessmentMeta['judul'] ?? 'Assessment',
                    'assessment_code' => $assessmentMeta['kode_assessment'] ?? null,
                    'instrument_type' => $instrument->value,
                    'instrument_label' => $instrument->label(),
                    'kompetensi' => $kompetensi->value,
                    'kompetensi_label' => $kompetensi->label(),
                    'indikator_kode' => $indicator['kode'],
                    'indikator_label' => $indicator['label'],
                    'form_id' => $formMeta['id'] ?? null,
                    'form_title' => $formMeta['judul_form'] ?? 'Form',
                    'form_code' => $formMeta['kode_form'] ?? null,
                    'score' => $formScore,
                    'display_score' => $displayFormScore,
                    'formatted_score' => $this->formatScore($formScore),
                    'display_formatted_score' => $this->formatScore($displayFormScore),
                    'level' => $this->serializeLevel($formScore),
                    'answered_items' => $answeredScorableCount,
                    'scored_items' => count($availableScores),
                    'pending_manual_items' => $manualPendingCount,
                    'is_complete' => $answeredScorableCount > 0 && $manualPendingCount === 0,
                    'items' => $fieldItems,
                ];
            }
        }

        $indicatorSummaries = $this->buildIndicatorSummaries($formSummaries);
        $instrumentSummaries = $this->buildInstrumentSummaries($indicatorSummaries);
        $competencySummaries = $this->buildCompetencySummaries($instrumentSummaries);
        $overallSummary = $this->buildOverallSummary($competencySummaries);

        return [
            'status' => $this->resolveScoringStatus($totalScorableItems, $pendingManualItems),
            'status_label' => $this->resolveScoringStatusLabel($totalScorableItems, $pendingManualItems),
            'status_description' => $this->resolveScoringStatusDescription($totalScorableItems, $pendingManualItems),
            'manual_review' => [
                'total_items' => $totalScorableItems,
                'scored_items' => $scoredItems,
                'pending_items' => $pendingManualItems,
                'completed_items' => max($totalScorableItems - $pendingManualItems, 0),
            ],
            'weight_reference' => collect(AssessmentInstrumentType::cases())
                ->map(fn (AssessmentInstrumentType $instrument) => [
                    'key' => $instrument->value,
                    'label' => $instrument->label(),
                    'weight' => $instrument->weight(),
                    'weight_percent' => (int) round($instrument->weight() * 100),
                ])
                ->values()
                ->all(),
            'overall' => $overallSummary,
            'competencies' => $competencySummaries,
            'forms' => array_values($formSummaries),
            'indicators' => array_values($indicatorSummaries),
            'instruments' => array_values($instrumentSummaries),
            'development_recommendations' => $this->buildDevelopmentRecommendations($competencySummaries),
            'narrative' => $this->buildNarrative($overallSummary, $competencySummaries, $pendingManualItems),
            'career_recommendations' => $this->buildCareerRecommendations($overallSummary, $competencySummaries),
            'radar_chart' => [
                'max_score' => 5,
                'labels' => collect(KompetensiGuru::cases())->map(fn ($case) => $case->label())->all(),
                'datasets' => collect(KompetensiGuru::cases())->map(function (KompetensiGuru $kompetensi) use ($competencySummaries) {
                    $summary = collect($competencySummaries)->firstWhere('key', $kompetensi->value);

                    return [
                        'key' => $kompetensi->value,
                        'label' => $kompetensi->label(),
                        'score' => $summary['score'] ?? 0.0,
                        'formatted_score' => $summary['formatted_score'] ?? null,
                        'is_available' => $summary['score'] !== null,
                    ];
                })->all(),
            ],
        ];
    }

    private function buildFieldScoreSummary(
        AssessmentInstrumentType $instrument,
        array $form,
        array $field,
        ?AssessmentAttemptAnswer $answer
    ): array {
        $answered = $this->answerHasContent($answer);
        $score = null;
        $scoreSource = null;
        $manualPending = false;

        if ($answered && $instrument === AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS) {
            $score = $this->normalizeScoreValue(data_get($answer?->answer_payload ?? [], 'level_kompetensi'));
            $scoreSource = $score !== null ? 'auto_option_level' : null;
        }

        if ($answered && $score === null && $instrument->requiresManualReview()) {
            $score = $this->normalizeScoreValue($answer?->assessor_score);
            $scoreSource = $score !== null ? 'manual_assessor' : null;
            $manualPending = $score === null;
        }

        return [
            'field_id' => $field['id'] ?? null,
            'field_label' => $field['label'] ?? 'Pertanyaan',
            'field_type' => $field['tipe_field'] ?? 'text',
            'answered' => $answered,
            'score' => $score,
            'formatted_score' => $this->formatScore($score),
            'level' => $this->serializeLevel($score),
            'score_source' => $scoreSource,
            'manual_pending' => $manualPending,
        ];
    }

    private function buildIndicatorSummaries(array $formSummaries): array
    {
        return collect($formSummaries)
            ->groupBy(function (array $formSummary) {
                return implode('|', [
                    $formSummary['instrument_type'],
                    $formSummary['kompetensi'],
                    $formSummary['indikator_kode'] ?: 'form-'.$formSummary['form_id'],
                ]);
            })
            ->map(function (Collection $forms) {
                $first = $forms->first();
                $availableFormScores = $forms
                    ->pluck('score')
                    ->filter(fn ($score) => $score !== null)
                    ->values()
                    ->all();
                $displayScores = $forms
                    ->pluck('display_score')
                    ->filter(fn ($score) => $score !== null)
                    ->values()
                    ->all();
                $score = $this->average($availableFormScores);
                $displayScore = $this->average($displayScores);

                return [
                    'instrument_type' => $first['instrument_type'],
                    'instrument_label' => $first['instrument_label'],
                    'kompetensi' => $first['kompetensi'],
                    'kompetensi_label' => $first['kompetensi_label'],
                    'indikator_kode' => $first['indikator_kode'],
                    'indikator_label' => $first['indikator_label'] ?: $first['form_title'],
                    'score' => $score,
                    'display_score' => $displayScore,
                    'formatted_score' => $this->formatScore($score),
                    'display_formatted_score' => $this->formatScore($displayScore),
                    'level' => $this->serializeLevel($score),
                    'forms' => $forms->values()->all(),
                    'pending_manual_items' => (int) $forms->sum('pending_manual_items'),
                    'is_complete' => $forms->every(fn ($form) => (bool) $form['is_complete']),
                ];
            })
            ->values()
            ->all();
    }

    private function buildInstrumentSummaries(array $indicatorSummaries): array
    {
        return collect($indicatorSummaries)
            ->groupBy(function (array $indicatorSummary) {
                return implode('|', [
                    $indicatorSummary['instrument_type'],
                    $indicatorSummary['kompetensi'],
                ]);
            })
            ->map(function (Collection $indicators) {
                $first = $indicators->first();
                $availableIndicatorScores = $indicators
                    ->pluck('score')
                    ->filter(fn ($score) => $score !== null)
                    ->values()
                    ->all();
                $displayScores = $indicators
                    ->pluck('display_score')
                    ->filter(fn ($score) => $score !== null)
                    ->values()
                    ->all();
                $instrument = AssessmentInstrumentType::tryFromMixed($first['instrument_type']);
                $score = $this->average($availableIndicatorScores);
                $displayScore = $this->average($displayScores);

                return [
                    'instrument_type' => $first['instrument_type'],
                    'instrument_label' => $first['instrument_label'],
                    'kompetensi' => $first['kompetensi'],
                    'kompetensi_label' => $first['kompetensi_label'],
                    'base_weight' => $instrument?->weight(),
                    'score' => $score,
                    'display_score' => $displayScore,
                    'formatted_score' => $this->formatScore($score),
                    'display_formatted_score' => $this->formatScore($displayScore),
                    'level' => $this->serializeLevel($score),
                    'indicators' => $indicators->values()->all(),
                    'pending_manual_items' => (int) $indicators->sum('pending_manual_items'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildCompetencySummaries(array $instrumentSummaries): array
    {
        return collect(KompetensiGuru::cases())
            ->map(function (KompetensiGuru $kompetensi) use ($instrumentSummaries) {
                $items = collect($instrumentSummaries)
                    ->where('kompetensi', $kompetensi->value)
                    ->values();
                $availableItems = $items
                    ->filter(fn ($item) => $item['score'] !== null)
                    ->values();
                $activeWeightTotal = $availableItems->sum('base_weight');
                $weightedScore = $activeWeightTotal > 0
                    ? $availableItems->sum(function ($item) use ($activeWeightTotal) {
                        return $item['score'] * ($item['base_weight'] / $activeWeightTotal);
                    })
                    : null;
                $recommendationCategory = $this->resolveRecommendationCategory($weightedScore);

                return [
                    'key' => $kompetensi->value,
                    'label' => $kompetensi->label(),
                    'score' => $weightedScore !== null ? round($weightedScore, 2) : null,
                    'formatted_score' => $this->formatScore($weightedScore),
                    'level' => $this->serializeLevel($weightedScore),
                    'active_weight_total' => $activeWeightTotal > 0 ? round($activeWeightTotal, 2) : 0.0,
                    'active_weight_percent' => $activeWeightTotal > 0 ? (int) round($activeWeightTotal * 100) : 0,
                    'recommendation_category' => $recommendationCategory,
                    'recommendation_description' => $this->resolveRecommendationDescription($kompetensi, $weightedScore),
                    'instruments' => $items->map(function (array $item) use ($activeWeightTotal) {
                        $normalizedWeight = $activeWeightTotal > 0 && $item['score'] !== null
                            ? round($item['base_weight'] / $activeWeightTotal, 4)
                            : null;

                        return array_merge($item, [
                            'active_weight' => $normalizedWeight,
                            'active_weight_percent' => $normalizedWeight !== null
                                ? (int) round($normalizedWeight * 100)
                                : null,
                        ]);
                    })->values()->all(),
                    'pending_manual_items' => (int) $items->sum('pending_manual_items'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildOverallSummary(array $competencySummaries): array
    {
        $availableScores = collect($competencySummaries)
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->values()
            ->all();
        $overallScore = $this->average($availableScores);

        return [
            'score' => $overallScore,
            'formatted_score' => $this->formatScore($overallScore),
            'level' => $this->serializeLevel($overallScore),
            'available_competencies' => count($availableScores),
        ];
    }

    private function buildDevelopmentRecommendations(array $competencySummaries): array
    {
        return collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortBy('score')
            ->map(function (array $item) {
                return [
                    'kompetensi' => $item['key'],
                    'label' => $item['label'],
                    'score' => $item['score'],
                    'formatted_score' => $item['formatted_score'],
                    'category' => $item['recommendation_category'],
                    'description' => $item['recommendation_description'],
                ];
            })
            ->values()
            ->all();
    }

    private function buildNarrative(array $overallSummary, array $competencySummaries, int $pendingManualItems): string
    {
        if ($overallSummary['score'] === null) {
            return 'Hasil penilaian belum dapat dihitung karena belum ada komponen skor yang tersedia pada assessment ini.';
        }

        $availableCompetencies = collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortBy('score')
            ->values();
        $lowest = $availableCompetencies->first();
        $highest = $availableCompetencies->last();
        $overallLevel = $overallSummary['level']['short_label'] ?? 'Belum terpetakan';
        $overallScore = $overallSummary['formatted_score'] ?? '-';
        $parts = [
            "Secara umum profil kompetensi guru berada pada {$overallLevel} dengan skor {$overallScore}.",
        ];

        if ($highest && $lowest) {
            $parts[] = "Area yang paling menonjol berada pada kompetensi {$highest['label']}, sedangkan fokus penguatan utama saat ini ada pada kompetensi {$lowest['label']}.";
        }

        if ($pendingManualItems > 0) {
            $parts[] = 'Sebagian instrumen masih menunggu penilaian assessor, sehingga ringkasan ini akan semakin lengkap setelah seluruh skor manual diinput.';
        }

        return implode(' ', $parts);
    }

    private function buildCareerRecommendations(array $overallSummary, array $competencySummaries): array
    {
        if ($overallSummary['score'] === null) {
            return [];
        }

        $availableCompetencies = collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortByDesc('score')
            ->values();
        $topLabels = $availableCompetencies->take(2)->pluck('key')->all();
        $recommendations = [];

        if (in_array(KompetensiGuru::PEDAGOGIK->value, $topLabels, true) && in_array(KompetensiGuru::PROFESIONAL->value, $topLabels, true)) {
            $recommendations[] = [
                'title' => 'Guru Inti / Fasilitator Pembelajaran',
                'reason' => 'Kekuatan pedagogik dan profesional mendukung peran pembinaan pembelajaran, pendampingan sejawat, dan penguatan praktik kelas.',
            ];
        }

        if (in_array(KompetensiGuru::SOSIAL->value, $topLabels, true) && in_array(KompetensiGuru::KEPRIBADIAN->value, $topLabels, true)) {
            $recommendations[] = [
                'title' => 'Wali Kelas / Mentor Guru',
                'reason' => 'Kombinasi kompetensi sosial dan kepribadian kuat untuk peran pendampingan, komunikasi, dan penguatan budaya belajar yang sehat.',
            ];
        }

        if ($overallSummary['score'] !== null && $overallSummary['score'] >= 4.20) {
            $recommendations[] = [
                'title' => 'Calon Kepala Sekolah / Pengembang Program',
                'reason' => 'Capaian keseluruhan yang tinggi menunjukkan kesiapan untuk mengambil peran kepemimpinan akademik dan pengembangan program pendidikan.',
            ];
        }

        if ($recommendations === []) {
            $recommendations[] = [
                'title' => 'Penggerak Komunitas Belajar',
                'reason' => 'Profil kompetensi yang berkembang dapat diarahkan untuk memperkuat komunitas belajar, kolaborasi sejawat, dan perbaikan praktik secara bertahap.',
            ];
        }

        return collect($recommendations)
            ->unique('title')
            ->values()
            ->all();
    }

    private function resolveScoringStatus(int $totalScorableItems, int $pendingManualItems): string
    {
        if ($totalScorableItems === 0) {
            return 'not_ready';
        }

        if ($pendingManualItems > 0) {
            return 'partial';
        }

        return 'complete';
    }

    private function resolveScoringStatusLabel(int $totalScorableItems, int $pendingManualItems): string
    {
        return match ($this->resolveScoringStatus($totalScorableItems, $pendingManualItems)) {
            'complete' => 'Penilaian Lengkap',
            'partial' => 'Menunggu Review Assessor',
            default => 'Belum Ada Skor',
        };
    }

    private function resolveScoringStatusDescription(int $totalScorableItems, int $pendingManualItems): string
    {
        return match ($this->resolveScoringStatus($totalScorableItems, $pendingManualItems)) {
            'complete' => 'Semua komponen skor yang tersedia sudah dapat dihitung.',
            'partial' => "Masih ada {$pendingManualItems} jawaban yang menunggu penilaian manual assessor.",
            default => 'Instrumen yang ada belum menghasilkan komponen skor yang bisa dihitung.',
        };
    }

    private function resolveRecommendationCategory(float|int|string|null $score): ?string
    {
        if (! is_numeric($score)) {
            return null;
        }

        $numericScore = round((float) $score, 2);

        return match (true) {
            $numericScore <= 2.60 => 'Prioritas segera dilakukan',
            $numericScore <= 3.40 => 'Perlu dikuatkan rutin',
            default => 'Perlu dipertahankan secara konsisten',
        };
    }

    private function resolveRecommendationDescription(KompetensiGuru $kompetensi, float|int|string|null $score): ?string
    {
        $category = $this->resolveRecommendationCategory($score);

        if (! $category) {
            return null;
        }

        return match ($category) {
            'Prioritas segera dilakukan' => "Kompetensi {$kompetensi->label()} perlu menjadi fokus intervensi utama dalam pendampingan, pelatihan, dan praktik harian.",
            'Perlu dikuatkan rutin' => "Kompetensi {$kompetensi->label()} sudah mulai terbentuk, tetapi masih perlu penguatan yang konsisten melalui latihan, refleksi, dan umpan balik.",
            default => "Kompetensi {$kompetensi->label()} sudah relatif kuat dan perlu dijaga konsistensinya melalui praktik berkelanjutan serta berbagi praktik baik.",
        };
    }

    private function serializeLevel(float|int|string|null $score): ?array
    {
        $level = LevelKompetensi::fromScore($score);

        if (! $level) {
            return null;
        }

        return [
            'value' => $level->value,
            'label' => $level->label(),
            'short_label' => $level->shortLabel(),
        ];
    }

    private function average(array $values): ?float
    {
        $numericValues = collect($values)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($numericValues->isEmpty()) {
            return null;
        }

        return round($numericValues->avg(), 2);
    }

    private function formatScore(float|int|string|null $score): ?string
    {
        if (! is_numeric($score)) {
            return null;
        }

        return number_format((float) $score, 2, '.', '');
    }

    private function normalizeScoreValue(float|int|string|null $score): ?float
    {
        if (! is_numeric($score)) {
            return null;
        }

        $numericScore = round((float) $score, 2);

        if ($numericScore < 1 || $numericScore > 5) {
            return null;
        }

        return $numericScore;
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

        return collect($payload['rows'] ?? [])->filter(fn ($row) => is_array($row) && collect($row)->filter(fn ($value) => filled($value))->isNotEmpty())->isNotEmpty();
    }
}

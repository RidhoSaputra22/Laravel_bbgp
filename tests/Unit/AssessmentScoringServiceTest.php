<?php

namespace Tests\Unit;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Assessment\AssessmentScoringService;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AssessmentScoringServiceTest extends TestCase
{
    public function test_it_builds_weighted_competency_scores_with_adaptive_instrument_weights(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-PG',
                        'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Guru',
                        'instrument_type' => 'pilihan_ganda_kompleks',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Form Pedagogik',
                                'kode_form' => 'FORM-PED-1',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => '1.1',
                                'indikator_label' => 'Indikator Pedagogik',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 301, 'label' => 'Soal 1', 'tipe_field' => 'radio'],
                                    ['id' => 302, 'label' => 'Soal 2', 'tipe_field' => 'radio'],
                                ],
                            ],
                            [
                                'id' => 202,
                                'judul_form' => 'Form Sosial',
                                'kode_form' => 'FORM-SOS-1',
                                'kompetensi' => 'sosial',
                                'indikator_kode' => '3.1',
                                'indikator_label' => 'Indikator Sosial',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 303, 'label' => 'Soal 3', 'tipe_field' => 'radio'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 102,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Pemetaan Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'forms' => [
                            [
                                'id' => 203,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'SK-PED',
                                'indikator_label' => 'Kasus Pedagogik',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 304, 'label' => 'Jawaban Pedagogik', 'tipe_field' => 'textarea'],
                                ],
                            ],
                            [
                                'id' => 204,
                                'judul_form' => 'Kasus Sosial',
                                'kode_form' => 'SK-SOS',
                                'kompetensi' => 'sosial',
                                'indikator_kode' => 'SK-SOS',
                                'indikator_label' => 'Kasus Sosial',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 305, 'label' => 'Jawaban Sosial', 'tipe_field' => 'textarea'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'B',
                'answer_payload' => ['level_kompetensi' => 3],
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 302,
                'answer_text' => 'C',
                'answer_payload' => ['level_kompetensi' => 4],
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 303,
                'answer_text' => 'D',
                'answer_payload' => ['level_kompetensi' => 4],
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 304,
                'answer_text' => 'Analisis pedagogik',
                'assessor_score' => 5,
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 305,
                'answer_text' => 'Analisis sosial',
                'assessor_score' => 2,
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $competencies = collect($summary['competencies'])->keyBy('key');

        $this->assertSame('complete', $summary['status']);
        $this->assertSame('4.25', data_get($competencies->get('pedagogik'), 'formatted_score'));
        $this->assertSame('3.00', data_get($competencies->get('sosial'), 'formatted_score'));
        $this->assertSame('3.63', data_get($summary, 'overall.formatted_score'));
        $this->assertSame('Mumpuni', data_get($summary, 'overall.level.short_label'));
        $this->assertSame('Perlu dikuatkan rutin', data_get($competencies->get('sosial'), 'recommendation_category'));
    }

    public function test_it_marks_manual_instruments_as_pending_until_assessor_scores_are_filled(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-PG',
                        'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Guru',
                        'instrument_type' => 'pilihan_ganda_kompleks',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Form Pedagogik',
                                'kode_form' => 'FORM-PED-1',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => '1.1',
                                'indikator_label' => 'Indikator Pedagogik',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 301, 'label' => 'Soal 1', 'tipe_field' => 'radio'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 102,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Pemetaan Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'forms' => [
                            [
                                'id' => 203,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'SK-PED',
                                'indikator_label' => 'Kasus Pedagogik',
                                'is_scoreable' => true,
                                'fields' => [
                                    ['id' => 304, 'label' => 'Jawaban Pedagogik', 'tipe_field' => 'textarea'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'D',
                'answer_payload' => ['level_kompetensi' => 4],
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 304,
                'answer_text' => 'Analisis pedagogik',
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $pedagogik = collect($summary['competencies'])->firstWhere('key', 'pedagogik');

        $this->assertSame('partial', $summary['status']);
        $this->assertSame(1, data_get($summary, 'manual_review.pending_items'));
        $this->assertSame('4.00', data_get($pedagogik, 'formatted_score'));
        $this->assertSame('Menunggu Review Assessor', $summary['status_label']);
    }

    private function makeService(): AssessmentScoringService
    {
        return new AssessmentScoringService(new AssessmentStructureMetadataResolver);
    }
}

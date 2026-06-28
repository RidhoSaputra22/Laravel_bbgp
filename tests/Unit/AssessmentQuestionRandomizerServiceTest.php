<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AssessmentQuestionRandomizerServiceTest extends TestCase
{
    public function test_it_keeps_competency_level_metadata_in_snapshot_options(): void
    {
        $field = new AssessmentFormField([
            'label' => 'Soal 1',
            'deskripsi' => 'Deskripsi soal',
            'nama_field' => 'soal_1',
            'tipe_field' => 'radio',
            'placeholder' => null,
            'bantuan' => 'Pilih satu jawaban',
            'opsi_field' => [
                [
                    'label' => 'A',
                    'value' => 'Mengenali kondisi belajar peserta didik.',
                    'score' => 1,
                    'level_kompetensi' => 1,
                ],
                [
                    'label' => 'B',
                    'value' => 'Mengembangkan strategi belajar yang adaptif.',
                    'score' => 5,
                    'level_kompetensi' => 5,
                ],
            ],
            'scoring_config' => [
                'enabled' => true,
                'method' => 'choice_option_score',
            ],
            'is_required' => true,
            'is_active' => true,
        ]);
        $field->id = 301;

        $form = new AssessmentForm([
            'judul_form' => 'Form Kompetensi',
            'kode_form' => 'FORM-1',
            'deskripsi' => 'Deskripsi form',
            'scoring_config' => ['profile' => 'pilihan_ganda_kompleks'],
            'is_active' => true,
        ]);
        $form->id = 201;
        $form->setRelation('fields', new Collection([$field]));

        $assessment = new Assessment([
            'kode_assessment' => 'ASM-1',
            'judul' => 'Assessment Kompetensi',
            'deskripsi' => 'Deskripsi assessment',
            'petunjuk' => 'Petunjuk',
            'scoring_config' => ['weight' => 0.40],
            'is_active' => true,
        ]);
        $assessment->id = 101;
        $assessment->setRelation('forms', new Collection([$form]));

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-ASM-01',
            'judul_penugasan' => 'Penugasan Kompetensi',
        ]);
        $assignment->id = 11;
        $assignment->setRelation('assessments', new Collection([$assessment]));

        $target = new AssessmentAssignmentTarget;
        $target->id = 21;
        $target->setRelation('assignment', $assignment);

        $snapshot = app(AssessmentQuestionRandomizerService::class)->buildSnapshot($target);
        $options = data_get($snapshot, 'assessments.0.forms.0.fields.0.opsi_field');

        $this->assertSame(1, $options[0]['level_kompetensi']);
        $this->assertSame(1.0, $options[0]['score']);
        $this->assertSame('Level 1: Paham', $options[0]['level_kompetensi_label']);
        $this->assertSame('Mengenali kondisi belajar peserta didik.', $options[0]['label']);
        $this->assertSame('A', $options[0]['value']);

        $this->assertSame(5, $options[1]['level_kompetensi']);
        $this->assertSame(5.0, $options[1]['score']);
        $this->assertSame('Level 5: Ahli', $options[1]['level_kompetensi_label']);
        $this->assertSame('B', $options[1]['value']);
        $this->assertTrue((bool) data_get($snapshot, 'assessments.0.forms.0.fields.0.scoring_config.enabled'));
        $this->assertSame('choice_option_score', data_get($snapshot, 'assessments.0.forms.0.fields.0.scoring_config.method'));
    }
}

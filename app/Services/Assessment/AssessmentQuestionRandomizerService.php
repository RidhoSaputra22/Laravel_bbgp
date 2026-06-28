<?php

namespace App\Services\Assessment;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentFormField;
use App\Support\Assessment\ChoiceOptionNormalizer;

class AssessmentQuestionRandomizerService
{
    public function buildSnapshot(AssessmentAssignmentTarget $target): array
    {
        $assignment = $target->assignment;

        $assessments = $assignment->assessments
            ->filter(fn ($assessment) => (bool) $assessment->is_active)
            ->values()
            ->map(function ($assessment) {
                $forms = $assessment->forms
                    ->filter(fn ($form) => (bool) $form->is_active)
                    ->values()
                    ->map(function ($form) use ($assessment) {
                        $fields = $form->fields
                            ->filter(fn ($field) => (bool) $field->is_active)
                            ->shuffle()
                            ->values()
                            ->map(fn ($field) => $this->mapField($field, $assessment->id, $form->id))
                            ->all();

                        if ($fields === []) {
                            return null;
                        }

                        return [
                            'id' => $form->id,
                            'assessment_id' => $assessment->id,
                            'judul_form' => $form->judul_form,
                            'kode_form' => $form->kode_form,
                            'deskripsi' => $form->deskripsi,
                            'fields' => $fields,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($forms === []) {
                    return null;
                }

                return [
                    'id' => $assessment->id,
                    'kode_assessment' => $assessment->kode_assessment,
                    'judul' => $assessment->judul,
                    'deskripsi' => $assessment->deskripsi,
                    'petunjuk' => $assessment->petunjuk,
                    'forms' => $forms,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $allFields = collect($assessments)
            ->flatMap(fn ($assessment) => $assessment['forms'] ?? [])
            ->flatMap(fn ($form) => $form['fields'] ?? []);

        return [
            'generated_at' => now()->toIso8601String(),
            'assignment' => [
                'id' => $assignment->id,
                'kode_penugasan' => $assignment->kode_penugasan,
                'judul_penugasan' => $assignment->judul_penugasan,
            ],
            'assessments' => $assessments,
            'meta' => [
                'total_questions' => $allFields->count(),
                'required_questions' => $allFields->where('is_required', true)->count(),
            ],
        ];
    }

    private function mapField(AssessmentFormField $field, int $assessmentId, int $formId): array
    {
        return [
            'id' => $field->id,
            'assessment_id' => $assessmentId,
            'assessment_form_id' => $formId,
            'label' => $field->label,
            'deskripsi' => $field->deskripsi,
            'nama_field' => $field->nama_field,
            'tipe_field' => $field->tipe_field,
            'placeholder' => $field->placeholder,
            'bantuan' => $field->bantuan,
            'opsi_field' => $this->normalizeOptions($field->opsi_field),
            'is_required' => (bool) $field->is_required,
        ];
    }

    private function normalizeOptions(?array $options): array
    {
        return collect(ChoiceOptionNormalizer::normalizeMany($options ?? []))
            ->map(fn ($option) => [
                'label' => $option['label'],
                'value' => $option['value'],
            ])
            ->filter(fn ($option) => $option['value'] !== '')
            ->values()
            ->all();
    }
}

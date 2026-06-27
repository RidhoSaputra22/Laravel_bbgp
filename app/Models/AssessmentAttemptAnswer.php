<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_attempt_id',
        'assessment_id',
        'assessment_form_id',
        'assessment_form_field_id',
        'answer_text',
        'answer_payload',
        'answer_file_path',
        'answered_at',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'answered_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function form()
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }

    public function field()
    {
        return $this->belongsTo(AssessmentFormField::class, 'assessment_form_field_id');
    }
}

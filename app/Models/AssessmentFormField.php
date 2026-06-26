<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_form_id',
        'label',
        'nama_field',
        'tipe_field',
        'placeholder',
        'bantuan',
        'opsi_field',
        'nilai_default',
        'validasi',
        'lebar_kolom',
        'urutan',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'opsi_field' => 'array',
        'validasi' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(AssessmentForm::class, 'assessment_form_id');
    }
}

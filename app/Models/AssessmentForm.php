<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'judul_form',
        'kode_form',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function fields()
    {
        return $this->hasMany(AssessmentFormField::class)->orderBy('urutan');
    }
}

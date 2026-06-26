<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_assessment',
        'judul',
        'slug',
        'deskripsi',
        'petunjuk',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function forms()
    {
        return $this->hasMany(AssessmentForm::class)->orderBy('urutan');
    }

    public function assignments()
    {
        return $this->hasMany(AssessmentAssignment::class)->orderByDesc('id');
    }
}

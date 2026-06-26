<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignmentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_assignment_id',
        'nomor_sesi',
        'label_sesi',
        'kapasitas_peserta',
        'total_peserta',
        'durasi_sesi_jam',
    ];

    protected $casts = [
        'nomor_sesi' => 'integer',
        'kapasitas_peserta' => 'integer',
        'total_peserta' => 'integer',
        'durasi_sesi_jam' => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssessmentAssignment::class, 'assessment_assignment_id');
    }

    public function targets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class)->orderBy('id');
    }
}

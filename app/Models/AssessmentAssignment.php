<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'kode_penugasan',
        'judul_penugasan',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'kapasitas_per_sesi',
        'durasi_sesi_jam',
        'total_sesi',
        'status_distribusi',
        'total_target',
        'total_ditugaskan',
        'assigned_by',
        'job_batch_id',
        'processed_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'processed_at' => 'datetime',
        'kapasitas_per_sesi' => 'integer',
        'durasi_sesi_jam' => 'integer',
        'total_sesi' => 'integer',
        'total_target' => 'integer',
        'total_ditugaskan' => 'integer',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function targets()
    {
        return $this->hasMany(AssessmentAssignmentTarget::class)
            ->orderBy('assessment_assignment_session_id')
            ->orderBy('id');
    }

    public function sessions()
    {
        return $this->hasMany(AssessmentAssignmentSession::class)
            ->orderBy('nomor_sesi');
    }
}

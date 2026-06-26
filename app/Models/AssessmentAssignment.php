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
        return $this->hasMany(AssessmentAssignmentTarget::class)->orderByDesc('id');
    }
}

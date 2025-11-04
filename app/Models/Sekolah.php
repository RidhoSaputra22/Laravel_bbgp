<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_sekolah',
        'npsn_sekolah',
        'bp_sekolah',
        'status_sekolah',
        'provinsi',
        'kecamatan',
        'kabupaten',
        'alamat',
        'akreditasi',
        'no_telepon',
        'email',
        'website_url',
        'tahun_berdiri',
        'koordinat',
    ];
}

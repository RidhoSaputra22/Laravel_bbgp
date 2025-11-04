<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();
            $table->integer('guru_id'); // id kepala sekolah
            $table->string('nama_sekolah');
            $table->string('npsn_sekolah');
            $table->string('bp_sekolah');
            $table->string('status_sekolah');
            $table->string('provinsi')->default('-');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->text('alamat')->default('-');
            $table->string('akreditasi')->default('-');
            $table->string('no_telepon')->default('-');
            $table->string('email')->default('-');
            $table->string('website_url')->default('-');
            $table->string('tahun_berdiri')->default('-');
            $table->string('koordinat')->default('-');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};

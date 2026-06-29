<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('internals')) {
            return;
        }

        Schema::table('internals', function (Blueprint $table) {
            if (! Schema::hasColumn('internals', 'nik')) {
                $table->string('nik')->nullable();
            }

            if (! Schema::hasColumn('internals', 'kota')) {
                $table->string('kota')->nullable();
            }

            if (! Schema::hasColumn('internals', 'tgl_selesai_kegiatan')) {
                $table->date('tgl_selesai_kegiatan')->nullable();
            }

            if (! Schema::hasColumn('internals', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable();
            }

            if (! Schema::hasColumn('internals', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable();
            }

            if (! Schema::hasColumn('internals', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }

            if (! Schema::hasColumn('internals', 'hotel')) {
                $table->string('hotel')->nullable();
            }

            if (! Schema::hasColumn('internals', 'transport_pergi')) {
                $table->unsignedBigInteger('transport_pergi')->default(0);
            }

            if (! Schema::hasColumn('internals', 'transport_pulang')) {
                $table->unsignedBigInteger('transport_pulang')->default(0);
            }

            if (! Schema::hasColumn('internals', 'bill_penginapan')) {
                $table->unsignedBigInteger('bill_penginapan')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_1')) {
                $table->unsignedBigInteger('hari_1')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_2')) {
                $table->unsignedBigInteger('hari_2')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_3')) {
                $table->unsignedBigInteger('hari_3')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_4')) {
                $table->unsignedBigInteger('hari_4')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_5')) {
                $table->unsignedBigInteger('hari_5')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_6')) {
                $table->unsignedBigInteger('hari_6')->default(0);
            }

            if (! Schema::hasColumn('internals', 'hari_7')) {
                $table->unsignedBigInteger('hari_7')->default(0);
            }

            if (! Schema::hasColumn('internals', 'bukti_bill')) {
                $table->string('bukti_bill')->nullable();
            }
        });

        DB::statement('ALTER TABLE internals MODIFY tempat VARCHAR(255) NULL');
        DB::statement('ALTER TABLE internals MODIFY jabatan VARCHAR(255) NULL');
        DB::statement('ALTER TABLE internals MODIFY golongan VARCHAR(255) NULL');
        DB::statement("ALTER TABLE internals MODIFY is_verif ENUM('sudah','belum') NOT NULL DEFAULT 'belum'");

        if (Schema::hasColumn('internals', 'kota')) {
            DB::table('internals')
                ->whereNull('kota')
                ->update(['kota' => DB::raw('tempat')]);
        }

        if (Schema::hasColumn('internals', 'tgl_selesai_kegiatan')) {
            DB::table('internals')
                ->whereNull('tgl_selesai_kegiatan')
                ->update(['tgl_selesai_kegiatan' => DB::raw('tgl_kegiatan')]);
        }

        if (Schema::hasColumn('internals', 'jam_mulai')) {
            DB::table('internals')
                ->whereNull('jam_mulai')
                ->update(['jam_mulai' => '00:00:00']);
        }

        if (Schema::hasColumn('internals', 'jam_selesai')) {
            DB::table('internals')
                ->whereNull('jam_selesai')
                ->update(['jam_selesai' => '23:59:59']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. The base internals migration has been updated to match the
        // runtime schema, so dropping these columns here would break fresh
        // installs that already created them in the original table definition.
    }
};

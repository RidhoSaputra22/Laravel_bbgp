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

        $this->normalizeIsVerifColumn();

        $this->addColumnIfMissing('internals', 'nik', fn (Blueprint $table) => $table->string('nik')->nullable());
        $this->addColumnIfMissing('internals', 'kota', fn (Blueprint $table) => $table->string('kota')->nullable());
        $this->addColumnIfMissing('internals', 'tgl_selesai_kegiatan', fn (Blueprint $table) => $table->date('tgl_selesai_kegiatan')->nullable());
        $this->addColumnIfMissing('internals', 'jam_mulai', fn (Blueprint $table) => $table->time('jam_mulai')->nullable());
        $this->addColumnIfMissing('internals', 'jam_selesai', fn (Blueprint $table) => $table->time('jam_selesai')->nullable());
        $this->addColumnIfMissing('internals', 'deskripsi', fn (Blueprint $table) => $table->text('deskripsi')->nullable());
        $this->addColumnIfMissing('internals', 'hotel', fn (Blueprint $table) => $table->string('hotel')->nullable());
        $this->addColumnIfMissing('internals', 'transport_pergi', fn (Blueprint $table) => $table->unsignedBigInteger('transport_pergi')->default(0));
        $this->addColumnIfMissing('internals', 'transport_pulang', fn (Blueprint $table) => $table->unsignedBigInteger('transport_pulang')->default(0));
        $this->addColumnIfMissing('internals', 'bill_penginapan', fn (Blueprint $table) => $table->unsignedBigInteger('bill_penginapan')->default(0));
        $this->addColumnIfMissing('internals', 'hari_1', fn (Blueprint $table) => $table->unsignedBigInteger('hari_1')->default(0));
        $this->addColumnIfMissing('internals', 'hari_2', fn (Blueprint $table) => $table->unsignedBigInteger('hari_2')->default(0));
        $this->addColumnIfMissing('internals', 'hari_3', fn (Blueprint $table) => $table->unsignedBigInteger('hari_3')->default(0));
        $this->addColumnIfMissing('internals', 'hari_4', fn (Blueprint $table) => $table->unsignedBigInteger('hari_4')->default(0));
        $this->addColumnIfMissing('internals', 'hari_5', fn (Blueprint $table) => $table->unsignedBigInteger('hari_5')->default(0));
        $this->addColumnIfMissing('internals', 'hari_6', fn (Blueprint $table) => $table->unsignedBigInteger('hari_6')->default(0));
        $this->addColumnIfMissing('internals', 'hari_7', fn (Blueprint $table) => $table->unsignedBigInteger('hari_7')->default(0));
        $this->addColumnIfMissing('internals', 'bukti_bill', fn (Blueprint $table) => $table->string('bukti_bill')->nullable());
        $this->addColumnIfMissing('internals', 'is_verif', fn (Blueprint $table) => $table->string('is_verif', 20)->default('belum'));

        if (Schema::hasColumn('internals', 'tempat')) {
            DB::statement('ALTER TABLE internals MODIFY tempat VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('internals', 'jabatan')) {
            DB::statement('ALTER TABLE internals MODIFY jabatan VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('internals', 'golongan')) {
            DB::statement('ALTER TABLE internals MODIFY golongan VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('internals', 'kota') && Schema::hasColumn('internals', 'tempat')) {
            DB::table('internals')
                ->whereNull('kota')
                ->update(['kota' => DB::raw('tempat')]);
        }

        if (
            Schema::hasColumn('internals', 'tgl_selesai_kegiatan') &&
            Schema::hasColumn('internals', 'tgl_kegiatan')
        ) {
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

    private function normalizeIsVerifColumn(): void
    {
        if (! Schema::hasColumn('internals', 'is_verif')) {
            return;
        }

        DB::statement("
            UPDATE internals
            SET is_verif = 'belum'
            WHERE is_verif IS NULL
               OR TRIM(is_verif) = ''
               OR is_verif NOT IN ('sudah', 'belum')
        ");

        try {
            DB::statement("ALTER TABLE internals MODIFY is_verif ENUM('sudah','belum') NOT NULL DEFAULT 'belum'");
        } catch (\Throwable $exception) {
            DB::statement("ALTER TABLE internals MODIFY is_verif VARCHAR(20) NOT NULL DEFAULT 'belum'");
        }
    }

    private function addColumnIfMissing(string $tableName, string $column, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition) {
            $definition($table);
        });
    }
};

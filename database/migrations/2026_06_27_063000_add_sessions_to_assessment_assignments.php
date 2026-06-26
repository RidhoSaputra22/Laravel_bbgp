<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_TARGETS_PER_SESSION = 41;

    private const DEFAULT_DURATION_HOURS = 3;

    private const TARGET_SESSION_FOREIGN_KEY = 'aat_session_fk';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assessment_assignments')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                if (! Schema::hasColumn('assessment_assignments', 'kapasitas_per_sesi')) {
                    $table->unsignedInteger('kapasitas_per_sesi')->default(self::DEFAULT_TARGETS_PER_SESSION)->after('tanggal_selesai');
                }

                if (! Schema::hasColumn('assessment_assignments', 'durasi_sesi_jam')) {
                    $table->unsignedSmallInteger('durasi_sesi_jam')->default(self::DEFAULT_DURATION_HOURS)->after('kapasitas_per_sesi');
                }

                if (! Schema::hasColumn('assessment_assignments', 'total_sesi')) {
                    $table->unsignedInteger('total_sesi')->default(0)->after('durasi_sesi_jam');
                }
            });
        }

        if (! Schema::hasTable('assessment_assignment_sessions')) {
            Schema::create('assessment_assignment_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_assignment_id')->constrained('assessment_assignments')->cascadeOnDelete();
                $table->unsignedInteger('nomor_sesi');
                $table->string('label_sesi');
                $table->unsignedInteger('kapasitas_peserta')->default(self::DEFAULT_TARGETS_PER_SESSION);
                $table->unsignedInteger('total_peserta')->default(0);
                $table->unsignedSmallInteger('durasi_sesi_jam')->default(self::DEFAULT_DURATION_HOURS);
                $table->timestamps();

                $table->unique(
                    ['assessment_assignment_id', 'nomor_sesi'],
                    'assessment_assignment_sessions_unique'
                );
            });
        }

        if (Schema::hasTable('assessment_assignment_targets')) {
            if (! Schema::hasColumn('assessment_assignment_targets', 'assessment_assignment_session_id')) {
                Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                    $table->unsignedBigInteger('assessment_assignment_session_id')
                        ->nullable()
                        ->after('assessment_assignment_id');
                });
            }

            if (! $this->foreignKeyExists('assessment_assignment_targets', self::TARGET_SESSION_FOREIGN_KEY)) {
                Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                    $table->foreign('assessment_assignment_session_id', self::TARGET_SESSION_FOREIGN_KEY)
                        ->references('id')
                        ->on('assessment_assignment_sessions')
                        ->nullOnDelete();
                });
            }
        }

        $this->backfillExistingAssignments();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('assessment_assignment_targets') &&
            Schema::hasColumn('assessment_assignment_targets', 'assessment_assignment_session_id')
        ) {
            $hasTargetSessionForeign = $this->foreignKeyExists(
                'assessment_assignment_targets',
                self::TARGET_SESSION_FOREIGN_KEY
            );

            Schema::table('assessment_assignment_targets', function (Blueprint $table) use ($hasTargetSessionForeign) {
                if ($hasTargetSessionForeign) {
                    $table->dropForeign(self::TARGET_SESSION_FOREIGN_KEY);
                }
                $table->dropColumn('assessment_assignment_session_id');
            });
        }

        Schema::dropIfExists('assessment_assignment_sessions');

        if (Schema::hasTable('assessment_assignments')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $columnsToDrop = [];

                foreach (['kapasitas_per_sesi', 'durasi_sesi_jam', 'total_sesi'] as $column) {
                    if (Schema::hasColumn('assessment_assignments', $column)) {
                        $columnsToDrop[] = $column;
                    }
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    private function backfillExistingAssignments(): void
    {
        if (
            ! Schema::hasTable('assessment_assignments') ||
            ! Schema::hasTable('assessment_assignment_sessions') ||
            ! Schema::hasTable('assessment_assignment_targets')
        ) {
            return;
        }

        $now = now();

        $assignments = DB::table('assessment_assignments')
            ->orderBy('id')
            ->get();

        foreach ($assignments as $assignment) {
            $targetIds = DB::table('assessment_assignment_targets')
                ->where('assessment_assignment_id', $assignment->id)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $plannedTarget = max((int) $assignment->total_target, count($targetIds));
            $totalSessions = $plannedTarget > 0
                ? (int) ceil($plannedTarget / self::DEFAULT_TARGETS_PER_SESSION)
                : 0;

            DB::table('assessment_assignments')
                ->where('id', $assignment->id)
                ->update([
                    'kapasitas_per_sesi' => self::DEFAULT_TARGETS_PER_SESSION,
                    'durasi_sesi_jam' => self::DEFAULT_DURATION_HOURS,
                    'total_sesi' => $totalSessions,
                    'updated_at' => $assignment->updated_at ?? $now,
                ]);

            if ($totalSessions === 0) {
                continue;
            }

            DB::table('assessment_assignment_targets')
                ->where('assessment_assignment_id', $assignment->id)
                ->update([
                    'assessment_assignment_session_id' => null,
                    'updated_at' => $now,
                ]);

            DB::table('assessment_assignment_sessions')
                ->where('assessment_assignment_id', $assignment->id)
                ->delete();

            $sessionRows = [];
            $remainingTargets = $plannedTarget;

            for ($sessionNumber = 1; $sessionNumber <= $totalSessions; $sessionNumber++) {
                $allocatedTargets = min(self::DEFAULT_TARGETS_PER_SESSION, $remainingTargets);

                $sessionRows[] = [
                    'assessment_assignment_id' => $assignment->id,
                    'nomor_sesi' => $sessionNumber,
                    'label_sesi' => 'Sesi '.$sessionNumber,
                    'kapasitas_peserta' => self::DEFAULT_TARGETS_PER_SESSION,
                    'total_peserta' => $allocatedTargets,
                    'durasi_sesi_jam' => self::DEFAULT_DURATION_HOURS,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $remainingTargets -= self::DEFAULT_TARGETS_PER_SESSION;
            }

            DB::table('assessment_assignment_sessions')->insert($sessionRows);

            $sessionIds = DB::table('assessment_assignment_sessions')
                ->where('assessment_assignment_id', $assignment->id)
                ->orderBy('nomor_sesi')
                ->pluck('id')
                ->all();

            foreach ($targetIds as $index => $targetId) {
                $sessionIndex = intdiv($index, self::DEFAULT_TARGETS_PER_SESSION);

                if (! isset($sessionIds[$sessionIndex])) {
                    continue;
                }

                DB::table('assessment_assignment_targets')
                    ->where('id', $targetId)
                    ->update([
                        'assessment_assignment_session_id' => $sessionIds[$sessionIndex],
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};

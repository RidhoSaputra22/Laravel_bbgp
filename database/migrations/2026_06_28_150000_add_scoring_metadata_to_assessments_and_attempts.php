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
        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'instrument_type')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->string('instrument_type')->nullable()->after('petunjuk');
            });
        }

        if (Schema::hasTable('assessment_forms') && ! Schema::hasColumn('assessment_forms', 'kompetensi')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->string('kompetensi')->nullable()->after('deskripsi');
                $table->string('indikator_kode')->nullable()->after('kompetensi');
                $table->string('indikator_label')->nullable()->after('indikator_kode');
                $table->boolean('is_scoreable')->default(true)->after('indikator_label');
            });
        }

        if (Schema::hasTable('assessment_attempts') && ! Schema::hasColumn('assessment_attempts', 'scoring_summary')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->json('scoring_summary')->nullable()->after('result_summary');
            });
        }

        if (Schema::hasTable('assessment_attempt_answers') && ! Schema::hasColumn('assessment_attempt_answers', 'assessor_score')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->unsignedTinyInteger('assessor_score')->nullable()->after('answer_payload');
                $table->text('assessor_notes')->nullable()->after('assessor_score');
                $table->foreignId('assessor_user_id')
                    ->nullable()
                    ->after('assessor_notes')
                    ->constrained('users')
                    ->nullOnDelete();
                $table->timestamp('assessor_scored_at')->nullable()->after('assessor_user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_attempt_answers') && Schema::hasColumn('assessment_attempt_answers', 'assessor_score')) {
            Schema::table('assessment_attempt_answers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assessor_user_id');
                $table->dropColumn([
                    'assessor_score',
                    'assessor_notes',
                    'assessor_scored_at',
                ]);
            });
        }

        if (Schema::hasTable('assessment_attempts') && Schema::hasColumn('assessment_attempts', 'scoring_summary')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->dropColumn('scoring_summary');
            });
        }

        if (Schema::hasTable('assessment_forms') && Schema::hasColumn('assessment_forms', 'kompetensi')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->dropColumn([
                    'kompetensi',
                    'indikator_kode',
                    'indikator_label',
                    'is_scoreable',
                ]);
            });
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'instrument_type')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn('instrument_type');
            });
        }
    }
};

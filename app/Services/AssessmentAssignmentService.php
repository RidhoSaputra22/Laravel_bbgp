<?php

namespace App\Services;

use App\Jobs\ProcessAssessmentAssignmentTargetsJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\Guru;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AssessmentAssignmentService
{
    public const BATCH_THRESHOLD = 5;

    public const CHUNK_SIZE = 50;

    public function createAssignment(array $payload, ?int $assignedBy = null): AssessmentAssignment
    {
        $guruIds = $this->normalizeGuruIds($payload['guru_ids'] ?? []);
        $shouldBatch = count($guruIds) > self::BATCH_THRESHOLD;

        $assignment = DB::transaction(function () use ($payload, $guruIds, $assignedBy, $shouldBatch) {
            $assessment = Assessment::findOrFail($payload['assessment_id']);

            $assignment = AssessmentAssignment::create([
                'assessment_id' => $assessment->id,
                'kode_penugasan' => $payload['kode_penugasan'] ?: $this->generateUniqueCode(),
                'judul_penugasan' => $payload['judul_penugasan'],
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'status_distribusi' => $shouldBatch ? 'diproses' : 'draft',
                'total_target' => count($guruIds),
                'total_ditugaskan' => 0,
                'assigned_by' => $assignedBy ?: null,
            ]);

            if (! $shouldBatch) {
                $this->storeTargetRows($assignment->id, $guruIds);
                $this->refreshAssignmentSummary($assignment->id);
            }

            return $assignment;
        });

        if ($shouldBatch) {
            $this->dispatchBatch($assignment, $guruIds);
            $assignment->refresh();
        }

        return $assignment->load(['assessment', 'creator'])->loadCount('targets');
    }

    public function processTargetChunk(int $assignmentId, array $guruIds): void
    {
        $assignment = AssessmentAssignment::find($assignmentId);

        if (! $assignment) {
            return;
        }

        $this->storeTargetRows($assignmentId, $this->normalizeGuruIds($guruIds));
        $this->refreshAssignmentSummary($assignmentId);
    }

    public function markAsFailed(int $assignmentId): void
    {
        AssessmentAssignment::whereKey($assignmentId)->update([
            'status_distribusi' => 'gagal',
        ]);
    }

    public function refreshAssignmentSummary(int $assignmentId): void
    {
        $assignment = AssessmentAssignment::withCount('targets')->find($assignmentId);

        if (! $assignment) {
            return;
        }

        $totalAssigned = (int) $assignment->targets_count;
        $currentStatus = $assignment->status_distribusi;
        $isComplete = $assignment->total_target > 0 && $totalAssigned >= $assignment->total_target;

        $assignment->forceFill([
            'total_ditugaskan' => $totalAssigned,
            'status_distribusi' => $currentStatus === 'gagal' ? 'gagal' : ($isComplete ? 'selesai' : 'diproses'),
            'processed_at' => $isComplete ? now() : ($currentStatus === 'gagal' ? $assignment->processed_at : null),
        ])->save();
    }

    private function dispatchBatch(AssessmentAssignment $assignment, array $guruIds): void
    {
        $jobs = collect(array_chunk($guruIds, self::CHUNK_SIZE))
            ->map(fn (array $chunk) => new ProcessAssessmentAssignmentTargetsJob($assignment->id, $chunk))
            ->all();

        try {
            $batch = Bus::batch($jobs)
                ->name('Penugasan Assessment '.$assignment->kode_penugasan)
                ->allowFailures()
                ->dispatch();

            $assignment->update([
                'job_batch_id' => $batch->id,
            ]);

            $this->refreshAssignmentSummary($assignment->id);
        } catch (Throwable $exception) {
            $assignment->update([
                'status_distribusi' => 'gagal',
            ]);

            throw $exception;
        }
    }

    private function storeTargetRows(int $assignmentId, array $guruIds): void
    {
        if ($guruIds === []) {
            return;
        }

        $now = now();
        $rows = Guru::query()
            ->whereIn('id', $guruIds)
            ->pluck('id')
            ->map(function ($guruId) use ($assignmentId, $now) {
                return [
                    'assessment_assignment_id' => $assignmentId,
                    'guru_id' => $guruId,
                    'status' => 'ditugaskan',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        DB::table('assessment_assignment_targets')->upsert(
            $rows,
            ['assessment_assignment_id', 'guru_id'],
            ['status', 'assigned_at', 'updated_at']
        );
    }

    private function normalizeGuruIds(array $guruIds): array
    {
        return array_values(array_unique(array_map('intval', $guruIds)));
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'TGS-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentAssignment::where('kode_penugasan', $code)->exists());

        return $code;
    }
}

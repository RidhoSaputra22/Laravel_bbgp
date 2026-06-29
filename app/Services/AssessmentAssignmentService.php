<?php

namespace App\Services;

use App\Jobs\ProcessAssessmentAssignmentTargetsJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AssessmentAssignmentService
{
    public const BATCH_THRESHOLD = 25;

    public const CHUNK_SIZE = 50;

    public const TARGETS_PER_SESSION = 41;

    public const DEFAULT_SESSION_DURATION_HOURS = 3;

    public const SESSION_DURATION_OPTIONS = [1, 2, 3, 4, 5, 6, 7, 8];

    public function createAssignment(array $payload, ?int $assignedBy = null): AssessmentAssignment
    {
        $assessmentIds = $this->normalizeAssessmentIds($payload['assessment_ids'] ?? []);
        $guruIds = $this->normalizeGuruIds($payload['guru_ids'] ?? []);
        $sessionDurationHours = (int) ($payload['durasi_sesi_jam'] ?? self::DEFAULT_SESSION_DURATION_HOURS);
        $startTime = $this->normalizeStartTime($payload['jam_mulai'] ?? null);
        $shouldBatch = count($guruIds) > self::BATCH_THRESHOLD;

        $assignmentData = DB::transaction(function () use (
            $payload,
            $assessmentIds,
            $guruIds,
            $assignedBy,
            $shouldBatch,
            $sessionDurationHours,
            $startTime
        ) {
            $assessmentSyncData = $this->buildAssessmentSyncData($assessmentIds);
            $totalSessions = $this->calculateTotalSessions(count($guruIds));

            $assignment = AssessmentAssignment::create([
                'kode_penugasan' => $this->generateUniqueCode(),
                'judul_penugasan' => $payload['judul_penugasan'],
                'deskripsi' => $payload['deskripsi'] ?? null,
                'tanggal_mulai' => $payload['tanggal_mulai'] ?? null,
                'jam_mulai' => $startTime,
                'tanggal_selesai' => $payload['tanggal_selesai'] ?? null,
                'kapasitas_per_sesi' => self::TARGETS_PER_SESSION,
                'durasi_sesi_jam' => $sessionDurationHours,
                'total_sesi' => $totalSessions,
                'status_distribusi' => $shouldBatch ? 'diproses' : 'draft',
                'total_target' => count($guruIds),
                'total_ditugaskan' => 0,
                'assigned_by' => $assignedBy ?: null,
            ]);

            $assignment->assessments()->sync($assessmentSyncData);

            $sessionRows = $this->createSessions(
                $assignment,
                count($guruIds),
                $sessionDurationHours,
                $this->resolveFirstSessionStartAt($payload, $startTime)
            );

            $targetRows = $this->buildTargetRows($assignment->id, $guruIds, $sessionRows);

            if (! $shouldBatch) {
                $this->storeTargetRows($targetRows);
                $this->refreshAssignmentSummary($assignment->id);
            }

            return [
                'assignment' => $assignment,
                'target_rows' => $targetRows,
            ];
        });

        /** @var \App\Models\AssessmentAssignment $assignment */
        $assignment = $assignmentData['assignment'];
        $targetRows = $assignmentData['target_rows'];

        if ($shouldBatch) {
            $this->dispatchBatch($assignment, $targetRows);
            $assignment->refresh();
        }

        return $assignment->load(['assessments', 'creator', 'sessions'])->loadCount('targets');
    }

    public function processTargetChunk(int $assignmentId, array $targetRows): void
    {
        $assignment = AssessmentAssignment::find($assignmentId);

        if (! $assignment) {
            return;
        }

        $this->storeTargetRows($targetRows);
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

    private function dispatchBatch(AssessmentAssignment $assignment, array $targetRows): void
    {
        $jobs = collect(array_chunk($targetRows, self::CHUNK_SIZE))
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

    private function storeTargetRows(array $targetRows): void
    {
        if ($targetRows === []) {
            return;
        }

        DB::table('assessment_assignment_targets')->upsert(
            $targetRows,
            ['assessment_assignment_id', 'guru_id'],
            [
                'assessment_assignment_session_id',
                'status',
                'assigned_at',
                'updated_at',
            ]
        );
    }

    private function normalizeGuruIds(array $guruIds): array
    {
        return array_values(array_unique(array_map('intval', $guruIds)));
    }

    private function normalizeAssessmentIds(array $assessmentIds): array
    {
        return array_values(array_unique(array_map('intval', $assessmentIds)));
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'TGS-ASM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (AssessmentAssignment::where('kode_penugasan', $code)->exists());

        return $code;
    }

    private function createSessions(
        AssessmentAssignment $assignment,
        int $totalTargets,
        int $sessionDurationHours,
        ?Carbon $firstSessionStartAt = null
    ): array {
        $totalSessions = $this->calculateTotalSessions($totalTargets);

        if ($totalSessions === 0) {
            return [];
        }

        $remainingTargets = $totalTargets;
        $sessions = [];

        for ($sessionNumber = 1; $sessionNumber <= $totalSessions; $sessionNumber++) {
            $sessionStartAt = $firstSessionStartAt
                ? $firstSessionStartAt->copy()->addHours(($sessionNumber - 1) * $sessionDurationHours)
                : null;
            $sessionEndAt = $sessionStartAt
                ? $sessionStartAt->copy()->addHours($sessionDurationHours)
                : null;

            $sessions[] = $assignment->sessions()->create([
                'nomor_sesi' => $sessionNumber,
                'label_sesi' => 'Sesi '.$sessionNumber,
                'waktu_mulai' => $sessionStartAt,
                'waktu_selesai' => $sessionEndAt,
                'kapasitas_peserta' => self::TARGETS_PER_SESSION,
                'total_peserta' => min(self::TARGETS_PER_SESSION, $remainingTargets),
                'durasi_sesi_jam' => $sessionDurationHours,
            ]);

            $remainingTargets -= self::TARGETS_PER_SESSION;
        }

        return $sessions;
    }

    private function buildTargetRows(
        int $assignmentId,
        array $guruIds,
        array $sessions
    ): array {
        if ($guruIds === []) {
            return [];
        }

        $now = now();

        return collect($guruIds)
            ->values()
            ->map(function (int $guruId, int $index) use ($assignmentId, $sessions, $now) {
                $sessionIndex = intdiv($index, self::TARGETS_PER_SESSION);

                return [
                    'assessment_assignment_id' => $assignmentId,
                    'assessment_assignment_session_id' => $sessions[$sessionIndex]->id ?? null,
                    'guru_id' => $guruId,
                    'status' => 'ditugaskan',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();
    }

    private function calculateTotalSessions(int $totalTargets): int
    {
        if ($totalTargets <= 0) {
            return 0;
        }

        return (int) ceil($totalTargets / self::TARGETS_PER_SESSION);
    }

    private function buildAssessmentSyncData(array $assessmentIds): array
    {
        if ($assessmentIds === []) {
            return [];
        }

        $validAssessmentIds = Assessment::query()
            ->whereKey($assessmentIds)
            ->where('is_active', true)
            ->whereIn('status', ['draft', 'publish'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validAssessmentIds) !== count($assessmentIds)) {
            throw (new ModelNotFoundException)->setModel(Assessment::class, $assessmentIds);
        }

        return collect($assessmentIds)
            ->values()
            ->mapWithKeys(fn (int $assessmentId, int $index) => [
                $assessmentId => [
                    'urutan' => $index + 1,
                ],
            ])
            ->all();
    }

    private function normalizeStartTime(?string $startTime): ?string
    {
        if (! $startTime) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $startTime)->format('H:i:s');
    }

    private function resolveFirstSessionStartAt(array $payload, ?string $startTime): ?Carbon
    {
        $startDate = $payload['tanggal_mulai'] ?? null;

        if (! $startDate || ! $startTime) {
            return null;
        }

        return Carbon::parse($startDate.' '.$startTime);
    }
}

<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Enrollment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class EnrollmentPromotionService
{
    /**
     * Promote selected enrollments into a target batch.
     *
     * @param  array<int, int>  $enrollmentIds
     * @return array{count:int}
     */
    public function promote(array $enrollmentIds, int $targetBatchId, CarbonInterface $startDate, ?CarbonInterface $endedAt, int $actorId): array
    {
        $targetBatch = Batch::query()->findOrFail($targetBatchId);
        $sourceEndDate = $endedAt?->toDateString() ?? $startDate->copy()->subDay()->toDateString();

        return DB::transaction(function () use ($enrollmentIds, $targetBatch, $startDate, $sourceEndDate, $actorId) {
            $enrollments = Enrollment::query()
                ->with('student')
                ->whereIn('id', $enrollmentIds)
                ->lockForUpdate()
                ->get();

            foreach ($enrollments as $enrollment) {
                $enrollment->student?->update([
                    'class_id' => $targetBatch->class_id,
                ]);

                $enrollment->update([
                    'status' => 'completed',
                    'end_date' => $sourceEndDate,
                ]);

                Enrollment::create([
                    'student_id' => $enrollment->student_id,
                    'batch_id' => $targetBatch->id,
                    'start_date' => $startDate->toDateString(),
                    'status' => 'active',
                    'created_by' => $actorId,
                ]);
            }

            return ['count' => $enrollments->count()];
        });
    }
}

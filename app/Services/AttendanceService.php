<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function saveBatchAttendance(
        User $actor,
        Batch $batch,
        Carbon $attendanceDate,
        array $records,
        ?string $notes = null,
    ): AttendanceSession {
        return DB::transaction(function () use ($actor, $batch, $attendanceDate, $records, $notes): AttendanceSession {
            $session = AttendanceSession::query()->updateOrCreate(
                [
                    'tenant_id' => $batch->tenant_id,
                    'batch_id' => $batch->getKey(),
                    'attendance_date' => $attendanceDate->toDateString(),
                ],
                [
                    'owner_teacher_id' => $batch->owner_teacher_id,
                    'taken_by' => $actor->getKey(),
                    'notes' => $notes,
                ],
            );

            foreach ($records as $record) {
                $session->records()->updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                    ],
                    [
                        'tenant_id' => $batch->tenant_id,
                        'student_enrollment_id' => $record['student_enrollment_id'] ?? null,
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? null,
                    ],
                );
            }

            return $session->load(['batch', 'ownerTeacher', 'takenBy', 'records.student']);
        });
    }
}

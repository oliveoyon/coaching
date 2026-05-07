<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class AttendanceSessionService
{
    /**
     * Create or refresh the roster for a batch attendance session.
     */
    public function open(Batch $batch, string $attendanceDate, int $userId, string $mode = 'manual'): AttendanceSession
    {
        return DB::transaction(function () use ($batch, $attendanceDate, $userId, $mode): AttendanceSession {
            $session = AttendanceSession::query()->firstOrCreate(
                [
                    'batch_id' => $batch->id,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'mode' => $mode,
                    'status' => 'in_progress',
                    'created_by' => $userId,
                    'started_at' => now(),
                ],
            );

            if ($session->status === 'completed') {
                $session->update([
                    'status' => 'in_progress',
                    'completed_at' => null,
                ]);
            }

            $session->update([
                'mode' => $mode,
                'started_at' => $session->started_at ?: now(),
            ]);

            $activeEnrollments = Enrollment::query()
                ->with('student')
                ->where('batch_id', $batch->id)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $attendanceDate)
                ->where(function ($query) use ($attendanceDate) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $attendanceDate);
                })
                ->get();

            $keepEnrollmentIds = $activeEnrollments->pluck('id')->all();

            if ($keepEnrollmentIds === []) {
                $session->records()->delete();
            } else {
                $session->records()
                    ->whereNotIn('enrollment_id', $keepEnrollmentIds)
                    ->delete();
            }

            foreach ($activeEnrollments as $enrollment) {
                $session->records()->firstOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'student_id' => $enrollment->student_id,
                        'status' => 'pending',
                    ],
                );
            }

            return $session->fresh(['batch.academicClass', 'batch.subject', 'batch.teachers.user']);
        });
    }
}

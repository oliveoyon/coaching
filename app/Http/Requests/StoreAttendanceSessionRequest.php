<?php

namespace App\Http\Requests;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\StudentEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AttendanceSession::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'batch_id' => ['required', 'integer', Rule::exists('batches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'attendance_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'records.*.student_enrollment_id' => ['nullable', 'integer', Rule::exists('student_enrollments', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'records.*.status' => ['required', Rule::in(AttendanceRecord::statuses())],
            'records.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $batch = Batch::query()->find($this->integer('batch_id'));
                $user = $this->user();

                if (! $batch) {
                    return;
                }

                if ($user->isTeacher() && $user->teacher && $batch->owner_teacher_id !== $user->teacher->getKey()) {
                    $validator->errors()->add('batch_id', 'You can only take attendance for your own batches.');
                }

                $enrollmentIds = collect($this->input('records', []))
                    ->pluck('student_enrollment_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $enrollments = StudentEnrollment::query()
                    ->whereIn('id', $enrollmentIds)
                    ->get()
                    ->keyBy('id');

                foreach ($this->input('records', []) as $index => $record) {
                    $enrollmentId = isset($record['student_enrollment_id']) ? (int) $record['student_enrollment_id'] : null;
                    $studentId = isset($record['student_id']) ? (int) $record['student_id'] : null;

                    if (! $enrollmentId) {
                        continue;
                    }

                    $enrollment = $enrollments->get($enrollmentId);

                    if (! $enrollment || $enrollment->batch_id !== $batch->getKey() || $enrollment->student_id !== $studentId) {
                        $validator->errors()->add("records.$index.student_enrollment_id", 'Attendance row does not match the selected batch enrollment.');
                    }
                }
            },
        ];
    }
}

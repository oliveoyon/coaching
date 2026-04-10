<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBatchScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BatchSchedule::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'batch_id' => ['required', 'integer', Rule::exists('batches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('status', Teacher::STATUS_ACTIVE))],
            'day_of_week' => ['required', Rule::in(BatchSchedule::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'session_type' => ['required', Rule::in(BatchSchedule::sessionTypes())],
            'is_extra' => ['nullable', 'boolean'],
            'room_name' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $batch = Batch::query()->find($this->integer('batch_id'));
                $teacher = Teacher::query()->find($this->integer('teacher_id'));
                $subjectId = $this->integer('subject_id');
                $user = $this->user();

                if (! $batch || ! $teacher) {
                    return;
                }

                if ($this->input('start_time') >= $this->input('end_time')) {
                    $validator->errors()->add('end_time', 'End time must be after start time.');
                }

                if ($user->isTeacher() && $user->teacher && $batch->owner_teacher_id !== $user->teacher->getKey()) {
                    $validator->errors()->add('batch_id', 'You can only manage schedules for your own batches.');
                }

                if ($user->isTeacher() && $user->teacher && $teacher->getKey() !== $user->teacher->getKey()) {
                    $validator->errors()->add('teacher_id', 'You can only assign yourself on your own schedule.');
                }

                if ($subjectId && $batch->subject_id && $subjectId !== (int) $batch->subject_id) {
                    $validator->errors()->add('subject_id', 'Routine subject should match the selected batch subject for now.');
                }

                $this->validateConflicts($validator, $batch, $teacher);
            },
        ];
    }

    protected function validateConflicts($validator, Batch $batch, Teacher $teacher): void
    {
        $scheduleId = $this->route('schedule')?->getKey();
        $tenantId = $this->user()->tenant_id;
        $day = (string) $this->input('day_of_week');
        $start = (string) $this->input('start_time');
        $end = (string) $this->input('end_time');
        $room = trim((string) $this->input('room_name'));

        $overlap = fn ($query) => $query
            ->where('day_of_week', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($scheduleId, fn ($inner) => $inner->where('id', '!=', $scheduleId));

        $batchConflict = BatchSchedule::query()
            ->where('tenant_id', $tenantId)
            ->where('batch_id', $batch->getKey())
            ->where($overlap)
            ->exists();

        if ($batchConflict) {
            $validator->errors()->add('start_time', 'This batch already has an overlapping class on the selected day and time.');
        }

        $teacherConflict = BatchSchedule::query()
            ->where('tenant_id', $tenantId)
            ->where('teacher_id', $teacher->getKey())
            ->where($overlap)
            ->exists();

        if ($teacherConflict) {
            $validator->errors()->add('teacher_id', 'This teacher already has another overlapping class on the selected day and time.');
        }

        if ($room !== '') {
            $roomConflict = BatchSchedule::query()
                ->where('tenant_id', $tenantId)
                ->where('room_name', $room)
                ->where($overlap)
                ->exists();

            if ($roomConflict) {
                $validator->errors()->add('room_name', 'This room is already scheduled for another class in the same time slot.');
            }
        }
    }
}

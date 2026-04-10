<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $batch = $this->route('batch');

        return $batch instanceof Batch
            ? ($this->user()?->can('update', $batch) ?? false)
            : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        /** @var Batch|null $batch */
        $batch = $this->route('batch');

        return [
            'program_id' => [
                'nullable',
                'integer',
                Rule::exists('programs', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'subject_id' => [
                'nullable',
                'integer',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'owner_teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('can_own_batches', true)
                    ->where('status', Teacher::STATUS_ACTIVE)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('batches', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($batch?->getKey()),
            ],
            'status' => ['required', Rule::in([Batch::STATUS_ACTIVE, Batch::STATUS_INACTIVE, Batch::STATUS_COMPLETED])],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'room_name' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'notes' => ['nullable', 'string'],
            'schedule_days' => ['nullable', 'array'],
            'schedule_days.*' => ['nullable', Rule::in(BatchSchedule::DAYS)],
            'schedule_start_times' => ['nullable', 'array'],
            'schedule_start_times.*' => ['nullable', 'date_format:H:i'],
            'schedule_end_times' => ['nullable', 'array'],
            'schedule_end_times.*' => ['nullable', 'date_format:H:i'],
            'schedule_rooms' => ['nullable', 'array'],
            'schedule_rooms.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $days = $this->input('schedule_days', []);
                $starts = $this->input('schedule_start_times', []);
                $ends = $this->input('schedule_end_times', []);
                $rooms = $this->input('schedule_rooms', []);
                $tenantId = $this->user()->tenant_id;
                $teacherId = (int) ($this->user()->isTeacher() && $this->user()->teacher
                    ? $this->user()->teacher->getKey()
                    : $this->input('owner_teacher_id'));
                $batchId = $this->route('batch')?->getKey();
                $draftRows = [];

                foreach ($days as $index => $day) {
                    $start = $starts[$index] ?? null;
                    $end = $ends[$index] ?? null;
                    $room = $rooms[$index] ?? null;
                    $hasAny = filled($day) || filled($start) || filled($end);

                    if (! $hasAny) {
                        continue;
                    }

                    if (! filled($day) || ! filled($start) || ! filled($end)) {
                        $validator->errors()->add('schedule_days', 'Each schedule row must include day, start time, and end time.');
                    }

                    if (filled($start) && filled($end) && $start >= $end) {
                        $validator->errors()->add('schedule_end_times', 'Schedule end time must be after start time.');
                    }

                    if (filled($day) && filled($start) && filled($end)) {
                        $draftRows[] = [
                            'day' => $day,
                            'start' => $start,
                            'end' => $end,
                            'room' => filled($room) ? (string) $room : null,
                        ];
                    }
                }

                foreach ($draftRows as $index => $row) {
                    foreach ($draftRows as $compareIndex => $compareRow) {
                        if ($index >= $compareIndex) {
                            continue;
                        }

                        if ($row['day'] === $compareRow['day'] && $row['start'] < $compareRow['end'] && $row['end'] > $compareRow['start']) {
                            $validator->errors()->add('schedule_days', 'Routine rows inside the same batch cannot overlap on the same day.');
                        }
                    }

                    $teacherConflict = BatchSchedule::query()
                        ->where('tenant_id', $tenantId)
                        ->where('teacher_id', $teacherId)
                        ->where('batch_id', '!=', $batchId)
                        ->where('day_of_week', $row['day'])
                        ->where('start_time', '<', $row['end'])
                        ->where('end_time', '>', $row['start'])
                        ->exists();

                    if ($teacherConflict) {
                        $validator->errors()->add('owner_teacher_id', 'Teacher has an overlapping schedule in another batch for one of these routine rows.');
                    }

                    if ($row['room']) {
                        $roomConflict = BatchSchedule::query()
                            ->where('tenant_id', $tenantId)
                            ->where('batch_id', '!=', $batchId)
                            ->where('room_name', $row['room'])
                            ->where('day_of_week', $row['day'])
                            ->where('start_time', '<', $row['end'])
                            ->where('end_time', '>', $row['start'])
                            ->exists();

                        if ($roomConflict) {
                            $validator->errors()->add('schedule_rooms', 'One of the routine rooms already has another overlapping class.');
                        }
                    }
                }
            },
        ];
    }
}

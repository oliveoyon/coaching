<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\BatchSchedule;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Batch::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

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
            'code' => ['required', 'string', 'max:50', Rule::unique('batches', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
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

                foreach ($days as $index => $day) {
                    $start = $starts[$index] ?? null;
                    $end = $ends[$index] ?? null;
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
                }
            },
        ];
    }
}

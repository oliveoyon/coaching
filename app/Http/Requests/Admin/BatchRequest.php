<?php

namespace App\Http\Requests\Admin;

use App\Models\Teacher;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage batches') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'subject_id' => [
                'nullable',
                'integer',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'distribution_type' => ['required', Rule::in(['single', 'equal'])],
            'schedule_days' => ['nullable', 'array'],
            'schedule_days.*' => ['required', Rule::in(['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'])],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['required', 'integer', 'distinct', Rule::exists('teachers', 'id')],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                $batchId = $this->route('batch')?->id;
                $teacherIds = collect($this->input('teacher_ids', []))
                    ->filter()
                    ->unique()
                    ->values();

                if ($this->input('distribution_type') === 'single' && $teacherIds->count() !== 1) {
                    $validator->errors()->add('teacher_ids', 'Single distribution batches must have exactly one teacher.');
                }

                if ($this->input('distribution_type') === 'equal' && $teacherIds->count() < 1) {
                    $validator->errors()->add('teacher_ids', 'Equal distribution batches must have at least one teacher.');
                }

                if ($teacherIds->isEmpty()) {
                    // continue schedule validation even if teachers fail
                }

                $startTime = $this->input('start_time');
                $endTime = $this->input('end_time');
                $scheduleDays = collect($this->input('schedule_days', []))->filter()->values();

                if (($startTime && ! $endTime) || (! $startTime && $endTime)) {
                    $validator->errors()->add('start_time', 'Both start time and end time are required if schedule time is provided.');
                }

                if ($startTime && $endTime && $startTime >= $endTime) {
                    $validator->errors()->add('end_time', 'End time must be later than start time.');
                }

                if (($startTime || $endTime) && $scheduleDays->isEmpty()) {
                    $validator->errors()->add('schedule_days', 'Select at least one class day when schedule time is provided.');
                }

                if ($scheduleDays->isNotEmpty() && (! $startTime || ! $endTime)) {
                    $validator->errors()->add('start_time', 'Start time and end time are required when class days are selected.');
                }

                if ($teacherIds->isEmpty()) {
                    return;
                }

                $matchedTeachers = Teacher::query()
                    ->where('status', 'active')
                    ->whereIn('id', $teacherIds)
                    ->count();

                if ($matchedTeachers !== $teacherIds->count()) {
                    $validator->errors()->add('teacher_ids', 'Please select active teacher records only.');
                }

                $duplicateBatchExists = \App\Models\Batch::query()
                    ->where('name', $this->input('name'))
                    ->where('class_id', $this->input('class_id'))
                    ->where(function ($query) {
                        if ($this->filled('subject_id')) {
                            $query->where('subject_id', $this->input('subject_id'));
                        } else {
                            $query->whereNull('subject_id');
                        }
                    })
                    ->when($batchId, fn ($query) => $query->where('id', '!=', $batchId))
                    ->exists();

                if ($duplicateBatchExists) {
                    $validator->errors()->add('name', 'A batch with the same name, class, and subject already exists.');
                }
            },
        ];
    }
}

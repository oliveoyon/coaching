<?php

namespace App\Http\Requests\Admin;

use App\Models\Batch;
use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PromoteEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage enrollments') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_batch_id' => ['required', Rule::exists('batches', 'id')],
            'target_batch_id' => ['required', 'different:source_batch_id', Rule::exists('batches', 'id')],
            'start_date' => ['required', 'date'],
            'source_end_date' => ['nullable', 'date', 'before_or_equal:start_date'],
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => [Rule::exists('enrollments', 'id')],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $sourceBatch = Batch::query()->find($this->integer('source_batch_id'));
            $targetBatch = Batch::query()->find($this->integer('target_batch_id'));

            if (! $sourceBatch || ! $targetBatch) {
                return;
            }

            if ($targetBatch->status !== 'active') {
                $validator->errors()->add('target_batch_id', 'Only active batches can receive promoted students.');
            }

            if ($targetBatch->batchFees()->count() === 0) {
                $validator->errors()->add('target_batch_id', 'Set up target batch fees first. If this is a free batch, add the fee items with amount 0.');
            }

            $selectedIds = collect($this->input('enrollment_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($selectedIds->isEmpty()) {
                return;
            }

            $enrollments = Enrollment::query()
                ->with('student')
                ->whereIn('id', $selectedIds)
                ->get();

            if ($enrollments->count() !== $selectedIds->count()) {
                $validator->errors()->add('enrollment_ids', 'Some selected students could not be found.');

                return;
            }

            $wrongSource = $enrollments->first(fn (Enrollment $enrollment) => (int) $enrollment->batch_id !== (int) $sourceBatch->id);

            if ($wrongSource) {
                $validator->errors()->add('enrollment_ids', 'Only students from the selected source batch can be promoted together.');
            }

            $inactiveSource = $enrollments->first(fn (Enrollment $enrollment) => $enrollment->status !== 'active');

            if ($inactiveSource) {
                $validator->errors()->add('enrollment_ids', 'Only active enrollments can be promoted.');
            }

            $duplicateTargetStudent = $enrollments->first(function (Enrollment $enrollment) use ($targetBatch) {
                return Enrollment::query()
                    ->where('student_id', $enrollment->student_id)
                    ->where('batch_id', $targetBatch->id)
                    ->where('status', 'active')
                    ->exists();
            });

            if ($duplicateTargetStudent) {
                $validator->errors()->add('target_batch_id', $duplicateTargetStudent->student?->name.' already has an active enrollment in the target batch.');
            }
        });
    }
}

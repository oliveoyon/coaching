<?php

namespace App\Http\Requests\Admin;

use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends FormRequest
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
            'student_id' => ['required', Rule::exists('students', 'id')],
            'batch_id' => ['required', Rule::exists('batches', 'id')],
            'start_date' => ['required', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $student = Student::query()->find($this->integer('student_id'));
            $batch = Batch::query()->find($this->integer('batch_id'));

            if (! $student || ! $batch) {
                return;
            }

            if ($student->status !== 'active') {
                $validator->errors()->add('student_id', 'Only active students can be enrolled.');
            }

            if ($batch->status !== 'active') {
                $validator->errors()->add('batch_id', 'Only active batches can receive new enrollments.');
            }

            if ($student->class_id !== $batch->class_id) {
                $validator->errors()->add('batch_id', 'Student class and batch class must match.');
            }

            $alreadyEnrolled = Enrollment::query()
                ->where('student_id', $student->id)
                ->where('batch_id', $batch->id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyEnrolled) {
                $validator->errors()->add('student_id', 'This student already has an active enrollment in the selected batch.');
            }
        });
    }
}

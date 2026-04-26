<?php

namespace App\Http\Requests\Admin;

use App\Models\AdmissionRequest as AdmissionRequestModel;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ApproveAdmissionRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'existing_student_id' => ['nullable', Rule::exists('students', 'id')],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var AdmissionRequestModel|null $admissionRequest */
            $admissionRequest = $this->route('admissionRequest');

            if (! $admissionRequest || $admissionRequest->status !== 'pending') {
                return;
            }

            if (! $this->filled('existing_student_id')) {
                return;
            }

            $student = Student::query()->find($this->integer('existing_student_id'));

            if (! $student) {
                return;
            }

            if ($student->class_id !== $admissionRequest->batch->class_id) {
                $validator->errors()->add('existing_student_id', 'Selected student class does not match the requested batch class.');
            }

            $alreadyEnrolled = Enrollment::query()
                ->where('student_id', $student->id)
                ->where('batch_id', $admissionRequest->batch_id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyEnrolled) {
                $validator->errors()->add('existing_student_id', 'Selected student already has an active enrollment in this batch.');
            }
        });
    }
}

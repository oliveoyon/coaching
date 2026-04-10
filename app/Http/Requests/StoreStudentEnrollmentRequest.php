<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudentEnrollment::class) ?? false;
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
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('student_enrollments')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('batch_id', $this->input('batch_id'))),
            ],
            'batch_id' => [
                'required',
                'integer',
                Rule::exists('batches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'enrolled_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                StudentEnrollment::STATUS_ACTIVE,
                StudentEnrollment::STATUS_INACTIVE,
            ])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $user = $this->user();
                $student = Student::query()->find($this->integer('student_id'));
                $batch = Batch::query()->find($this->integer('batch_id'));

                if (! $student || ! $batch) {
                    return;
                }

                if ($student->tenant_id !== $user->tenant_id || $batch->tenant_id !== $user->tenant_id) {
                    $validator->errors()->add('student_id', 'Student and batch must belong to your tenant.');

                    return;
                }

                if ($user->isTeacher() && $user->teacher) {
                    if ($student->owner_teacher_id !== $user->teacher->getKey()) {
                        $validator->errors()->add('student_id', 'You can only enroll students within your own scope.');
                    }

                    if ($batch->owner_teacher_id !== $user->teacher->getKey()) {
                        $validator->errors()->add('batch_id', 'You can only enroll students into your own batches.');
                    }
                }
            },
        ];
    }
}

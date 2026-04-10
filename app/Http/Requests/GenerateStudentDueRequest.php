<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\StudentDue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateStudentDueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('generate', StudentDue::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'billing_period_key' => ['required', 'string', 'max:50'],
            'student_id' => ['nullable', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if (! $this->filled('student_id')) {
                    return;
                }

                $student = Student::query()->find($this->integer('student_id'));

                if (! $student) {
                    return;
                }

                if ($student->status !== Student::STATUS_ACTIVE) {
                    $validator->errors()->add('student_id', 'Dues can only be generated for active students.');
                }
            },
        ];
    }
}

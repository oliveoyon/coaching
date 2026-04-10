<?php

namespace App\Http\Requests;

use App\Models\Guardian;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('student') !== null
            && ($this->user()?->can('update', $this->route('student')) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Student $student */
        $student = $this->route('student');
        $tenantId = $this->user()->tenant_id;

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('students', 'user_id')->ignore($student->getKey()),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $user = User::query()->find($value);

                    if (! $user || ! $user->hasRole(Role::STUDENT)) {
                        $fail('The selected linked user must have the student role.');
                    }
                },
            ],
            'owner_teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('can_own_batches', true)
                    ->where('status', Teacher::STATUS_ACTIVE)),
            ],
            'student_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($student->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'admission_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                Student::STATUS_ACTIVE,
                Student::STATUS_INACTIVE,
                Student::STATUS_DROPOUT,
                Student::STATUS_COMPLETED,
            ])],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'institution_class' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'guardian_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $user = User::query()->find($value);

                    if (! $user || ! $user->hasRole(Role::GUARDIAN)) {
                        $fail('The selected linked guardian user must have the guardian role.');
                    }
                },
            ],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relation_type' => ['nullable', Rule::in(Guardian::relationTypes())],
            'guardian_occupation' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['nullable', 'string'],
            'guardian_notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $guardianFields = [
                    $this->input('guardian_user_id'),
                    $this->input('guardian_name'),
                    $this->input('guardian_phone'),
                    $this->input('guardian_email'),
                    $this->input('guardian_relation_type'),
                    $this->input('guardian_occupation'),
                    $this->input('guardian_address'),
                ];

                $hasGuardianData = collect($guardianFields)->contains(fn ($value) => filled($value));

                if (! $hasGuardianData) {
                    return;
                }

                if (! filled($this->input('guardian_name'))) {
                    $validator->errors()->add('guardian_name', 'Guardian name is required when guardian information is provided.');
                }

                if (! filled($this->input('guardian_phone'))) {
                    $validator->errors()->add('guardian_phone', 'Guardian phone is required when guardian information is provided.');
                }

                if (! filled($this->input('guardian_relation_type'))) {
                    $validator->errors()->add('guardian_relation_type', 'Guardian relation is required when guardian information is provided.');
                }
            },
        ];
    }
}

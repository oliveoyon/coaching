<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Teacher::class) ?? false;
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
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
                Rule::unique('teachers', 'user_id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $user = User::query()->find($value);

                    if (! $user || ! $user->hasRole(Role::TEACHER)) {
                        $fail('The selected linked user must have the teacher role.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in([Teacher::STATUS_ACTIVE, Teacher::STATUS_INACTIVE])],
            'subject_specializations' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'can_own_batches' => ['nullable', 'boolean'],
            'can_collect_fees' => ['nullable', 'boolean'],
            'joined_at' => ['nullable', 'date'],
        ];
    }
}

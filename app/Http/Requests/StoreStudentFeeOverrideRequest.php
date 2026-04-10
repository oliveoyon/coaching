<?php

namespace App\Http\Requests;

use App\Models\StudentFeeOverride;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentFeeOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudentFeeOverride::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'fee_structure_id' => ['required', 'integer', Rule::exists('fee_structures', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'reason' => ['nullable', 'string'],
        ];
    }
}

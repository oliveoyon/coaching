<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantBillingConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isAdmin()
            && $user->can('tenant.settings.manage')
            && $user->can('fees.structure.manage');
    }

    public function rules(): array
    {
        return [
            'billing_model' => ['required', Rule::in([
                Tenant::BILLING_MODEL_PER_STUDENT,
                Tenant::BILLING_MODEL_PER_COURSE,
                Tenant::BILLING_MODEL_PER_BATCH,
            ])],
            'billing_period' => ['required', Rule::in(['monthly', 'custom'])],
            'unique_student_per_period' => ['nullable', 'boolean'],
            'count_each_batch_separately' => ['nullable', 'boolean'],
            'count_each_course_separately' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}

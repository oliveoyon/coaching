<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\FeeStructure;
use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('fee_structure') !== null
            && ($this->user()?->can('update', $this->route('fee_structure')) ?? false);
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'fee_head_id' => ['required', 'integer', Rule::exists('fee_heads', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'title' => ['required', 'string', 'max:255'],
            'billing_model' => ['nullable', Rule::in([
                Tenant::BILLING_MODEL_PER_STUDENT,
                Tenant::BILLING_MODEL_PER_COURSE,
                Tenant::BILLING_MODEL_PER_BATCH,
            ])],
            'applicable_type' => ['required', Rule::in(FeeStructure::applicableTypes())],
            'applicable_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $tenantId = $this->user()->tenant_id;
                $type = $this->input('applicable_type');
                $id = $this->input('applicable_id');

                if ($type === FeeStructure::APPLICABLE_TENANT) {
                    return;
                }

                if ($type === FeeStructure::APPLICABLE_PROGRAM && ! Program::query()->where('tenant_id', $tenantId)->whereKey($id)->exists()) {
                    $validator->errors()->add('applicable_id', 'The selected program is invalid for this tenant.');
                }

                if ($type === FeeStructure::APPLICABLE_BATCH && ! Batch::query()->where('tenant_id', $tenantId)->whereKey($id)->exists()) {
                    $validator->errors()->add('applicable_id', 'The selected batch is invalid for this tenant.');
                }

                if ($type === FeeStructure::APPLICABLE_COURSE && ! filled($id)) {
                    $validator->errors()->add('applicable_id', 'A course reference ID will be required when the course module is added.');
                }
            },
        ];
    }
}

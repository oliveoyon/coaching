<?php

namespace App\Http\Requests;

use App\Models\FeeHead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', FeeHead::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('fee_heads', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'type' => ['required', Rule::in(FeeHead::types())],
            'frequency' => ['required', Rule::in(FeeHead::frequencies())],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage fee setup') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $batchFeeId = $this->route('batch_fee')?->id;
        $batchId = $this->route('batch')->id;

        return [
            'fee_type_id' => [
                'required',
                Rule::exists('fee_types', 'id'),
                Rule::unique('batch_fees', 'fee_type_id')->where(fn ($query) => $query->where('batch_id', $batchId))->ignore($batchFeeId),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}

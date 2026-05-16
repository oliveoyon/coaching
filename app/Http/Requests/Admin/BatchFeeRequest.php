<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchFeeRequest extends FormRequest
{
    /**
     * Normalize amount before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('amount')) {
            $this->merge([
                'amount' => number_format((float) $this->input('amount'), 2, '.', ''),
            ]);
        }
    }

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
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from_month' => ['nullable', 'date_format:Y-m'],
            'effective_to_month' => ['nullable', 'date_format:Y-m'],
            'apply_from_month' => ['nullable', 'date_format:Y-m'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $from = (string) $this->string('effective_from_month');
            $to = (string) $this->string('effective_to_month');
            $applyFrom = (string) $this->string('apply_from_month');
            $currentBatchFee = $this->route('batch_fee');

            if ($from !== '' && $to !== '' && $to < $from) {
                $validator->errors()->add('effective_to_month', 'End month must be after or equal to the start month.');
            }

            if ($applyFrom !== '' && $to !== '' && $to < $applyFrom) {
                $validator->errors()->add('effective_to_month', 'End month must be after or equal to the apply from month.');
            }

            if ($applyFrom !== '' && $currentBatchFee && $currentBatchFee->effective_from_month && $applyFrom <= $currentBatchFee->effective_from_month) {
                $validator->errors()->add('apply_from_month', 'Apply from month must be after the current start month.');
            }

            $batchId = $this->route('batch')->id;
            $feeTypeId = (int) $this->input('fee_type_id');
            $batchFeeId = $this->route('batch_fee')?->id;

            if ($feeTypeId <= 0) {
                return;
            }

            $newFrom = $applyFrom !== '' ? $applyFrom : ($from !== '' ? $from : '0000-01');
            $newTo = $to !== '' ? $to : '9999-12';

            $conflictExists = \App\Models\BatchFee::query()
                ->where('batch_id', $batchId)
                ->where('fee_type_id', $feeTypeId)
                ->when($batchFeeId, fn ($query) => $query->where('id', '!=', $batchFeeId))
                ->get()
                ->contains(function (\App\Models\BatchFee $existing) use ($newFrom, $newTo) {
                    $existingFrom = $existing->effective_from_month ?: '0000-01';
                    $existingTo = $existing->effective_to_month ?: '9999-12';

                    return $existingFrom <= $newTo && $existingTo >= $newFrom;
                });

            if ($conflictExists) {
                $validator->errors()->add('effective_from_month', 'This fee already exists for an overlapping month range.');
            }
        });
    }
}

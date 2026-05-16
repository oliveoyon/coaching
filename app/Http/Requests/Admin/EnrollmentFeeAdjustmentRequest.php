<?php

namespace App\Http\Requests\Admin;

use App\Models\BatchFee;
use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EnrollmentFeeAdjustmentRequest extends FormRequest
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
        return [
            'batch_fee_id' => ['required', Rule::exists('batch_fees', 'id')],
            'adjustment_type' => ['required', Rule::in(['discount', 'waiver'])],
            'value_type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'effective_from_month' => ['nullable', 'date_format:Y-m'],
            'effective_to_month' => ['nullable', 'date_format:Y-m'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Enrollment|null $enrollment */
            $enrollment = $this->route('enrollment');

            if (! $enrollment) {
                return;
            }

            $batchFee = BatchFee::query()->find($this->integer('batch_fee_id'));

            if ($batchFee && $batchFee->batch_id !== $enrollment->batch_id) {
                $validator->errors()->add('batch_fee_id', 'Selected fee does not belong to this enrollment batch.');
            }

            $from = (string) $this->string('effective_from_month');
            $to = (string) $this->string('effective_to_month');

            if ($from !== '' && $to !== '' && $to < $from) {
                $validator->errors()->add('effective_to_month', 'End month must be after or equal to the start month.');
            }

            if ($this->string('value_type')->toString() === 'percent' && (float) $this->input('value') > 100) {
                $validator->errors()->add('value', 'Percent adjustment cannot be more than 100.');
            }
        });
    }
}

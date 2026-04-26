<?php

namespace App\Http\Requests\Admin;

use App\Models\BatchFee;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('collect payments') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', Rule::exists('students', 'id')],
            'payment_date' => ['required', 'date'],
            'method' => ['required', Rule::in(['cash', 'bkash', 'nagad'])],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'month' => ['nullable', 'date_format:Y-m'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.enrollment_id' => ['required', Rule::exists('enrollments', 'id')],
            'items.*.batch_fee_id' => ['required', Rule::exists('batch_fees', 'id')],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $method = $this->string('method')->toString();
            $transactionId = $this->string('transaction_id')->toString();
            $month = $this->string('month')->toString();
            $items = collect($this->input('items', []));
            $collectingItems = $items
                ->map(fn ($item, $index) => ['index' => $index, 'data' => $item])
                ->filter(fn ($item) => (float) Arr::get($item['data'], 'amount', 0) > 0);

            $studentId = $this->integer('student_id');

            if ($collectingItems->isEmpty()) {
                $validator->errors()->add('items', 'Enter at least one payment amount greater than zero.');
            }

            if (in_array($method, ['bkash', 'nagad'], true) && $transactionId === '') {
                $validator->errors()->add('transaction_id', 'Transaction ID is required for bKash and Nagad payments.');
            }

            if ($method === 'cash' && $transactionId !== '') {
                $validator->errors()->add('transaction_id', 'Transaction ID is not needed for cash payment.');
            }

            $enrollmentIds = $collectingItems->pluck('data.enrollment_id')->filter()->unique()->values();
            $batchFeeIds = $collectingItems->pluck('data.batch_fee_id')->filter()->unique()->values();

            $enrollments = Enrollment::query()
                ->with('batch')
                ->whereIn('id', $enrollmentIds)
                ->get()
                ->keyBy('id');

            $batchFees = BatchFee::query()
                ->with('feeType')
                ->whereIn('id', $batchFeeIds)
                ->get()
                ->keyBy('id');

            foreach ($collectingItems as $item) {
                $index = $item['index'];
                $data = $item['data'];
                $amount = (float) Arr::get($data, 'amount', 0);
                $enrollment = $enrollments->get((int) Arr::get($data, 'enrollment_id'));
                $batchFee = $batchFees->get((int) Arr::get($data, 'batch_fee_id'));

                if (! $enrollment || ! $batchFee) {
                    continue;
                }

                if ($enrollment->student_id !== $studentId) {
                    $validator->errors()->add("items.{$index}.amount", 'Selected enrollment does not belong to the chosen student.');
                    continue;
                }

                if ($batchFee->batch_id !== $enrollment->batch_id) {
                    $validator->errors()->add("items.{$index}.amount", 'Selected fee item does not belong to the selected batch enrollment.');
                    continue;
                }

                if ($enrollment->status !== 'active') {
                    $validator->errors()->add("items.{$index}.amount", 'Payments can only be collected for active enrollments.');
                    continue;
                }

                if ($this->user()?->hasRole('Teacher')) {
                    $teacher = $this->user()->teacherProfile;

                    if (! $teacher || ! $enrollment->batch()->whereHas('teachers', fn ($query) => $query->where('teachers.id', $teacher->id))->exists()) {
                        $validator->errors()->add("items.{$index}.amount", 'You can only collect for your own assigned batch students.');
                        continue;
                    }
                }

                $frequency = $batchFee->feeType?->frequency;

                if ($frequency === 'monthly' && $month === '') {
                    $validator->errors()->add('month', 'Billing month is required when collecting monthly fee items.');
                    continue;
                }

                if ($frequency === 'monthly') {
                    $periodStart = $month.'-01';
                    $periodEnd = date('Y-m-t', strtotime($periodStart));

                    if ($enrollment->start_date->format('Y-m-d') > $periodEnd) {
                        $validator->errors()->add("items.{$index}.amount", 'This enrollment starts after the selected billing month.');
                        continue;
                    }

                    if ($enrollment->end_date && $enrollment->end_date->format('Y-m-d') < $periodStart) {
                        $validator->errors()->add("items.{$index}.amount", 'This enrollment was withdrawn before the selected billing month.');
                        continue;
                    }
                }

                $existingPayment = Payment::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->where('batch_fee_id', $batchFee->id)
                    ->when($frequency === 'monthly', fn ($query) => $query->where('month', $month))
                    ->when($frequency !== 'monthly', fn ($query) => $query->whereNull('month'))
                    ->whereIn('status', ['pending', 'approved'])
                    ->sum('amount');

                $remaining = max(0, (float) $batchFee->amount - (float) $existingPayment);

                if ($remaining <= 0) {
                    $validator->errors()->add("items.{$index}.amount", 'This fee item is already fully covered.');
                    continue;
                }

                if ($amount > $remaining) {
                    $validator->errors()->add("items.{$index}.amount", 'Amount cannot exceed the remaining due of '.number_format($remaining, 2).'.');
                }
            }
        });
    }
}

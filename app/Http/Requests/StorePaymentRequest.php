<?php

namespace App\Http\Requests;

use App\Models\Batch;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Payment::class) ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'student_enrollment_id' => ['nullable', 'integer', Rule::exists('student_enrollments', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'batch_id' => ['nullable', 'integer', Rule::exists('batches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'fee_structure_id' => ['required', 'integer', Rule::exists('fee_structures', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('is_active', true))],
            'payment_method' => ['required', Rule::in(Payment::methods())],
            'collected_on' => ['required', 'date'],
            'billing_period_type' => ['required', Rule::in(PaymentItem::periodTypes())],
            'billing_period_key' => ['required', 'string', 'max:50'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_notes' => ['nullable', 'string'],
            'item_notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $user = $this->user();
                $student = Student::query()->find($this->integer('student_id'));
                $enrollment = StudentEnrollment::query()->find($this->integer('student_enrollment_id'));
                $batch = Batch::query()->find($this->integer('batch_id'));
                $feeStructure = FeeStructure::query()->find($this->integer('fee_structure_id'));

                if (! $student || ! $feeStructure) {
                    return;
                }

                if ($student->tenant_id !== $user->tenant_id || $feeStructure->tenant_id !== $user->tenant_id) {
                    $validator->errors()->add('student_id', 'Payment data must belong to your tenant.');

                    return;
                }

                if ($enrollment) {
                    if ($enrollment->tenant_id !== $user->tenant_id || $enrollment->student_id !== $student->getKey()) {
                        $validator->errors()->add('student_enrollment_id', 'The selected enrollment is invalid for this student.');
                    }

                    if ($batch && $enrollment->batch_id !== $batch->getKey()) {
                        $validator->errors()->add('batch_id', 'Batch does not match the selected enrollment.');
                    }
                }

                if ($batch && $batch->tenant_id !== $user->tenant_id) {
                    $validator->errors()->add('batch_id', 'The selected batch is invalid for this tenant.');
                }

                if ($user->isTeacher() && $user->teacher) {
                    if ($student->owner_teacher_id !== $user->teacher->getKey()) {
                        $validator->errors()->add('student_id', 'You can only collect payment for students within your own scope.');
                    }

                    $resolvedBatch = $batch ?? $enrollment?->batch;

                    if ($resolvedBatch && $resolvedBatch->owner_teacher_id !== $user->teacher->getKey()) {
                        $validator->errors()->add('batch_id', 'You can only collect payment for your own batch ownership scope.');
                    }
                }

                if ($feeStructure->applicable_type === FeeStructure::APPLICABLE_BATCH && ! ($batch || $enrollment)) {
                    $validator->errors()->add('batch_id', 'A batch-linked fee requires a batch or enrollment reference.');
                }

                $resolvedBatch = $batch ?? $enrollment?->batch;

                if ($feeStructure->applicable_type === FeeStructure::APPLICABLE_BATCH) {
                    if (! $resolvedBatch || $resolvedBatch->getKey() !== (int) $feeStructure->applicable_id) {
                        $validator->errors()->add('fee_structure_id', 'The selected fee structure does not belong to the resolved batch context.');
                    }
                }

                if ($feeStructure->applicable_type === FeeStructure::APPLICABLE_PROGRAM) {
                    $programId = $resolvedBatch?->program_id
                        ?? $student->enrollments()->where('status', StudentEnrollment::STATUS_ACTIVE)->with('batch')->get()->pluck('batch.program_id')->filter()->first();

                    if ((int) $programId !== (int) $feeStructure->applicable_id) {
                        $validator->errors()->add('fee_structure_id', 'The selected fee structure does not match the student program context.');
                    }
                }
            },
        ];
    }
}

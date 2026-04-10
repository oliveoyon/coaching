<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Events\PaymentReceived;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeeCollectionService
{
    public function __construct(
        protected FeeDueCalculator $feeDueCalculator,
        protected ReceiptNumberService $receiptNumberService,
        protected DueLedgerService $dueLedgerService,
    ) {
    }

    public function collect(
        User $collector,
        Student $student,
        FeeStructure $feeStructure,
        float $paidAmount,
        string $paymentMethod,
        Carbon $collectedOn,
        string $billingPeriodType,
        string $billingPeriodKey,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        ?StudentEnrollment $enrollment = null,
        ?Batch $batch = null,
        ?string $paymentNotes = null,
        ?string $itemNotes = null,
    ): Payment {
        return DB::transaction(function () use (
            $collector,
            $student,
            $feeStructure,
            $paidAmount,
            $paymentMethod,
            $collectedOn,
            $billingPeriodType,
            $billingPeriodKey,
            $periodStart,
            $periodEnd,
            $enrollment,
            $batch,
            $paymentNotes,
            $itemNotes
        ): Payment {
            $resolvedBatch = $batch ?? $enrollment?->batch;
            $ownerTeacher = $this->resolveOwnerTeacher($student, $feeStructure, $resolvedBatch);

            if (! $ownerTeacher) {
                throw new InvalidArgumentException('Unable to resolve the academic owner for this payment.');
            }

            $periodDate = $periodStart ?? $collectedOn;
            $due = $this->dueLedgerService->ensureDue(
                student: $student,
                feeStructure: $feeStructure,
                billingPeriodKey: $billingPeriodKey,
                enrollment: $enrollment,
                batch: $resolvedBatch,
                billingPeriodType: $billingPeriodType,
                periodStart: $periodStart,
                periodEnd: $periodEnd,
            );
            $chargeAmount = (float) $due->charge_amount;
            $dueBefore = (float) $due->due_amount;

            if ($dueBefore <= 0) {
                throw new InvalidArgumentException('There is no outstanding due for the selected fee period.');
            }

            if ($paidAmount > $dueBefore) {
                throw new InvalidArgumentException('Paid amount cannot exceed the outstanding due for the selected fee period.');
            }

            $payment = Payment::query()->create([
                'tenant_id' => $student->tenant_id,
                'receipt_no' => $this->receiptNumberService->generate($student->tenant, $collectedOn),
                'student_id' => $student->getKey(),
                'student_enrollment_id' => $enrollment?->getKey(),
                'batch_id' => $resolvedBatch?->getKey(),
                'owner_teacher_id' => $ownerTeacher->getKey(),
                'collector_id' => $collector->getKey(),
                'collector_role' => $this->collectorRole($collector),
                'payment_method' => $paymentMethod,
                'collected_on' => $collectedOn,
                'total_amount' => $paidAmount,
                'status' => Payment::STATUS_RECEIVED,
                'notes' => $paymentNotes,
            ]);

            $payment->items()->create([
                'tenant_id' => $student->tenant_id,
                'student_due_id' => $due->getKey(),
                'fee_head_id' => $feeStructure->fee_head_id,
                'fee_structure_id' => $feeStructure->getKey(),
                'billing_period_type' => $billingPeriodType,
                'billing_period_key' => $billingPeriodKey,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'is_advance' => $periodStart ? $periodStart->isFuture() : false,
                'charge_amount' => $chargeAmount,
                'due_before' => $dueBefore,
                'paid_amount' => $paidAmount,
                'due_after' => max($dueBefore - $paidAmount, 0),
                'notes' => $itemNotes,
            ]);

            $this->dueLedgerService->syncDue($due);
            $payment = $payment->load(['student', 'tenant', 'batch', 'ownerTeacher', 'collector', 'items.feeHead', 'items.feeStructure']);

            event(new PaymentReceived($payment));

            return $payment;
        });
    }

    protected function resolveOwnerTeacher(Student $student, FeeStructure $feeStructure, ?Batch $batch): ?Teacher
    {
        if ($batch && in_array($feeStructure->applicable_type, [FeeStructure::APPLICABLE_BATCH, FeeStructure::APPLICABLE_COURSE], true)) {
            return $batch->ownerTeacher;
        }

        return $student->ownerTeacher;
    }

    protected function collectorRole(User $collector): string
    {
        if ($collector->isAdmin()) {
            return 'admin';
        }

        if ($collector->isTeacher()) {
            return 'teacher';
        }

        return 'user';
    }
}

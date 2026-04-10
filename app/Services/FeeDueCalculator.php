<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeOverride;
use Carbon\Carbon;

class FeeDueCalculator
{
    public function chargeAmount(Student $student, FeeStructure $feeStructure, ?Carbon $periodDate = null): float
    {
        $periodDate ??= now();

        $override = StudentFeeOverride::query()
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->getKey())
            ->where('fee_structure_id', $feeStructure->getKey())
            ->where('is_active', true)
            ->where(function ($query) use ($periodDate): void {
                $query->whereNull('starts_on')
                    ->orWhereDate('starts_on', '<=', $periodDate->toDateString());
            })
            ->where(function ($query) use ($periodDate): void {
                $query->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', $periodDate->toDateString());
            })
            ->latest('starts_on')
            ->first();

        return (float) ($override?->amount ?? $feeStructure->amount);
    }

    public function paidAmount(
        Student $student,
        FeeStructure $feeStructure,
        string $billingPeriodKey,
        ?StudentEnrollment $enrollment = null,
        ?int $ignorePaymentId = null,
    ): float {
        return (float) PaymentItem::query()
            ->where('tenant_id', $student->tenant_id)
            ->where('fee_structure_id', $feeStructure->getKey())
            ->where('billing_period_key', $billingPeriodKey)
            ->whereHas('payment', function ($paymentQuery) use ($student, $enrollment, $ignorePaymentId): void {
                $paymentQuery
                    ->where('status', Payment::STATUS_RECEIVED)
                    ->where('student_id', $student->getKey());

                if ($enrollment) {
                    $paymentQuery->where('student_enrollment_id', $enrollment->getKey());
                }

                if ($ignorePaymentId) {
                    $paymentQuery->where('id', '!=', $ignorePaymentId);
                }
            })
            ->sum('paid_amount');
    }

    public function dueAmount(
        Student $student,
        FeeStructure $feeStructure,
        string $billingPeriodKey,
        ?StudentEnrollment $enrollment = null,
        ?Carbon $periodDate = null,
        ?int $ignorePaymentId = null,
    ): float {
        $chargeAmount = $this->chargeAmount($student, $feeStructure, $periodDate);
        $paidAmount = $this->paidAmount($student, $feeStructure, $billingPeriodKey, $enrollment, $ignorePaymentId);

        return max($chargeAmount - $paidAmount, 0);
    }
}

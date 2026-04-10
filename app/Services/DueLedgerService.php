<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentDue;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DueLedgerService
{
    public function __construct(
        protected FeeDueCalculator $feeDueCalculator,
        protected FeeStructureResolver $feeStructureResolver,
    ) {
    }

    public function generateMonthlyForTenant(Tenant $tenant, string $billingPeriodKey, ?Carbon $periodDate = null): Collection
    {
        $periodDate ??= $this->periodDateFromKey($billingPeriodKey);

        return Student::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('student_code')
            ->get()
            ->flatMap(fn (Student $student) => $this->generateMonthlyForStudent($student, $billingPeriodKey, $periodDate));
    }

    public function generateMonthlyForStudent(Student $student, string $billingPeriodKey, ?Carbon $periodDate = null, ?StudentEnrollment $enrollment = null): Collection
    {
        $periodDate ??= $this->periodDateFromKey($billingPeriodKey);
        $dues = collect();

        $generalStructures = $this->feeStructureResolver
            ->forStudent($student)
            ->filter(fn (FeeStructure $feeStructure) => $feeStructure->feeHead?->frequency === FeeHead::FREQUENCY_MONTHLY)
            ->filter(fn (FeeStructure $feeStructure) => ! in_array($feeStructure->applicable_type, [FeeStructure::APPLICABLE_BATCH, FeeStructure::APPLICABLE_COURSE], true));

        foreach ($generalStructures as $feeStructure) {
            $dues->push($this->ensureDue(
                student: $student,
                feeStructure: $feeStructure,
                billingPeriodKey: $billingPeriodKey,
                enrollment: null,
                batch: null,
                billingPeriodType: PaymentItem::PERIOD_TYPE_MONTH,
                periodStart: $periodDate->copy()->startOfMonth(),
                periodEnd: $periodDate->copy()->endOfMonth(),
                generatedAutomatically: true,
            ));
        }

        $enrollments = $enrollment
            ? collect([$enrollment])
            : $student->enrollments()->with('batch')->where('status', StudentEnrollment::STATUS_ACTIVE)->get();

        foreach ($enrollments as $activeEnrollment) {
            $batchStructures = $this->feeStructureResolver
                ->forStudent($student, $activeEnrollment)
                ->filter(fn (FeeStructure $feeStructure) => $feeStructure->feeHead?->frequency === FeeHead::FREQUENCY_MONTHLY)
                ->filter(fn (FeeStructure $feeStructure) => in_array($feeStructure->applicable_type, [FeeStructure::APPLICABLE_BATCH, FeeStructure::APPLICABLE_COURSE], true));

            foreach ($batchStructures as $feeStructure) {
                $dues->push($this->ensureDue(
                    student: $student,
                    feeStructure: $feeStructure,
                    billingPeriodKey: $billingPeriodKey,
                    enrollment: $activeEnrollment,
                    batch: $activeEnrollment->batch,
                    billingPeriodType: PaymentItem::PERIOD_TYPE_MONTH,
                    periodStart: $periodDate->copy()->startOfMonth(),
                    periodEnd: $periodDate->copy()->endOfMonth(),
                    generatedAutomatically: true,
                ));
            }
        }

        return $dues
            ->unique('ledger_key')
            ->values();
    }

    public function ensureDue(
        Student $student,
        FeeStructure $feeStructure,
        string $billingPeriodKey,
        ?StudentEnrollment $enrollment = null,
        ?Batch $batch = null,
        ?string $billingPeriodType = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        bool $generatedAutomatically = false,
    ): StudentDue {
        $resolvedBatch = $batch ?? $enrollment?->batch;
        $ownerTeacher = $this->resolveOwnerTeacher($student, $feeStructure, $resolvedBatch);

        if (! $ownerTeacher) {
            throw new InvalidArgumentException('Unable to resolve the academic owner for this due.');
        }

        $periodMeta = $this->resolvePeriodMeta(
            feeStructure: $feeStructure,
            billingPeriodKey: $billingPeriodKey,
            billingPeriodType: $billingPeriodType,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
        );

        $chargeAmount = $this->feeDueCalculator->chargeAmount($student, $feeStructure, $periodMeta['period_date']);
        $ledgerKey = $this->ledgerKey($student, $feeStructure, $billingPeriodKey, $enrollment, $resolvedBatch);

        $due = StudentDue::query()->firstOrNew(['ledger_key' => $ledgerKey]);
        $due->fill([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->getKey(),
            'student_enrollment_id' => $enrollment?->getKey(),
            'batch_id' => $resolvedBatch?->getKey(),
            'owner_teacher_id' => $ownerTeacher->getKey(),
            'fee_head_id' => $feeStructure->fee_head_id,
            'fee_structure_id' => $feeStructure->getKey(),
            'billing_period_type' => $periodMeta['billing_period_type'],
            'billing_period_key' => $billingPeriodKey,
            'period_start' => $periodMeta['period_start'],
            'period_end' => $periodMeta['period_end'],
            'charge_amount' => $chargeAmount,
        ]);

        if ($generatedAutomatically && ! $due->generated_at) {
            $due->generated_at = now();
            $due->notes = $due->notes ?: 'Generated from due ledger service.';
        }

        $due->save();

        return $this->syncDue($due->fresh());
    }

    public function syncDue(StudentDue $due): StudentDue
    {
        $paidAmount = (float) PaymentItem::query()
            ->where('tenant_id', $due->tenant_id)
            ->where('fee_structure_id', $due->fee_structure_id)
            ->where('billing_period_key', $due->billing_period_key)
            ->whereHas('payment', function ($paymentQuery) use ($due): void {
                $paymentQuery
                    ->where('status', Payment::STATUS_RECEIVED)
                    ->where('student_id', $due->student_id);

                if ($due->student_enrollment_id) {
                    $paymentQuery->where('student_enrollment_id', $due->student_enrollment_id);
                }
            })
            ->sum('paid_amount');

        $dueAmount = max((float) $due->charge_amount - $paidAmount, 0);
        $status = match (true) {
            $dueAmount <= 0 => StudentDue::STATUS_PAID,
            $paidAmount > 0 => StudentDue::STATUS_PARTIAL,
            default => StudentDue::STATUS_OPEN,
        };

        $due->forceFill([
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'status' => $status,
            'last_synced_at' => now(),
        ])->save();

        if (PaymentItem::query()
            ->where('tenant_id', $due->tenant_id)
            ->where('fee_structure_id', $due->fee_structure_id)
            ->where('billing_period_key', $due->billing_period_key)
            ->whereNull('student_due_id')
            ->whereHas('payment', function ($paymentQuery) use ($due): void {
                $paymentQuery
                    ->where('status', Payment::STATUS_RECEIVED)
                    ->where('student_id', $due->student_id);

                if ($due->student_enrollment_id) {
                    $paymentQuery->where('student_enrollment_id', $due->student_enrollment_id);
                }
            })
            ->exists()) {
            PaymentItem::query()
                ->where('tenant_id', $due->tenant_id)
                ->where('fee_structure_id', $due->fee_structure_id)
                ->where('billing_period_key', $due->billing_period_key)
                ->whereNull('student_due_id')
                ->whereHas('payment', function ($paymentQuery) use ($due): void {
                    $paymentQuery
                        ->where('status', Payment::STATUS_RECEIVED)
                        ->where('student_id', $due->student_id);

                    if ($due->student_enrollment_id) {
                        $paymentQuery->where('student_enrollment_id', $due->student_enrollment_id);
                    }
                })
                ->update(['student_due_id' => $due->getKey()]);
        }

        return $due->fresh(['student', 'batch', 'ownerTeacher', 'feeHead', 'feeStructure', 'enrollment']);
    }

    public function duesForStudent(Student $student, string $billingPeriodKey, ?StudentEnrollment $enrollment = null): Collection
    {
        $this->generateMonthlyForStudent($student, $billingPeriodKey, $this->periodDateFromKey($billingPeriodKey), $enrollment);

        return StudentDue::query()
            ->with(['feeHead', 'feeStructure', 'batch', 'ownerTeacher', 'enrollment'])
            ->where('tenant_id', $student->tenant_id)
            ->where('student_id', $student->getKey())
            ->where('billing_period_key', $billingPeriodKey)
            ->when($enrollment, fn ($query) => $query->where(function ($dueQuery) use ($enrollment): void {
                $dueQuery->whereNull('student_enrollment_id')
                    ->orWhere('student_enrollment_id', $enrollment->getKey());
            }))
            ->orderBy('period_start')
            ->orderBy('id')
            ->get()
            ->map(fn (StudentDue $due) => $this->syncDue($due));
    }

    public function dueSummaryQueryForUser($user)
    {
        return StudentDue::query()->visibleTo($user);
    }

    protected function ledgerKey(Student $student, FeeStructure $feeStructure, string $billingPeriodKey, ?StudentEnrollment $enrollment, ?Batch $batch): string
    {
        return implode(':', [
            $student->tenant_id,
            $student->getKey(),
            $feeStructure->getKey(),
            $billingPeriodKey,
            $enrollment?->getKey() ?? 'none',
            $batch?->getKey() ?? 'none',
        ]);
    }

    /**
     * @return array{billing_period_type:string, period_start:?Carbon, period_end:?Carbon, period_date:Carbon}
     */
    protected function resolvePeriodMeta(
        FeeStructure $feeStructure,
        string $billingPeriodKey,
        ?string $billingPeriodType = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
    ): array {
        $billingPeriodType ??= match ($feeStructure->feeHead?->frequency) {
            FeeHead::FREQUENCY_MONTHLY => PaymentItem::PERIOD_TYPE_MONTH,
            FeeHead::FREQUENCY_ONE_TIME => PaymentItem::PERIOD_TYPE_ONE_TIME,
            default => PaymentItem::PERIOD_TYPE_CUSTOM,
        };

        if ($billingPeriodType === PaymentItem::PERIOD_TYPE_MONTH) {
            $periodDate = $periodStart ?? $this->periodDateFromKey($billingPeriodKey);

            return [
                'billing_period_type' => $billingPeriodType,
                'period_start' => $periodStart ?? $periodDate->copy()->startOfMonth(),
                'period_end' => $periodEnd ?? $periodDate->copy()->endOfMonth(),
                'period_date' => $periodDate,
            ];
        }

        $periodDate = $periodStart ?? now();

        return [
            'billing_period_type' => $billingPeriodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_date' => $periodDate,
        ];
    }

    protected function periodDateFromKey(string $billingPeriodKey): Carbon
    {
        if (preg_match('/^\d{4}\-\d{2}$/', $billingPeriodKey) === 1) {
            return Carbon::createFromFormat('Y-m', $billingPeriodKey)->startOfMonth();
        }

        return now()->startOfMonth();
    }

    protected function resolveOwnerTeacher(Student $student, FeeStructure $feeStructure, ?Batch $batch): ?Teacher
    {
        if ($batch && in_array($feeStructure->applicable_type, [FeeStructure::APPLICABLE_BATCH, FeeStructure::APPLICABLE_COURSE], true)) {
            return $batch->ownerTeacher;
        }

        return $student->ownerTeacher;
    }
}

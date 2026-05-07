<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'batch_id',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the enrolled student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the batch for the enrollment.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the user who created the enrollment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get payments collected for this enrollment.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get attendance rows for this enrollment.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get active fee items configured for the enrolled batch.
     */
    public function activeBatchFees(): HasMany
    {
        return $this->batch->batchFees()->where('status', 'active');
    }

    /**
     * Sum approved payments for a fee item and billing month.
     */
    public function approvedPaidForFee(BatchFee $batchFee, ?string $month = null): float
    {
        return (float) $this->payments
            ->where('batch_fee_id', $batchFee->id)
            ->when($month !== null, fn ($payments) => $payments->where('month', $month))
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Sum pending payments for a fee item and billing month.
     */
    public function pendingPaidForFee(BatchFee $batchFee, ?string $month = null): float
    {
        return (float) $this->payments
            ->where('batch_fee_id', $batchFee->id)
            ->when($month !== null, fn ($payments) => $payments->where('month', $month))
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * Get remaining amount for a fee item.
     */
    public function remainingForFee(BatchFee $batchFee, ?string $month = null): float
    {
        $feeAmount = (float) $batchFee->amount;
        $runningTotal = $this->approvedPaidForFee($batchFee, $month) + $this->pendingPaidForFee($batchFee, $month);

        return max(0, $feeAmount - $runningTotal);
    }

    /**
     * Decide whether a fee should be visible for the selected billing month.
     */
    public function isFeeBillableForMonth(BatchFee $batchFee, string $month): bool
    {
        $periodStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        if ($this->start_date && $this->start_date->gt($periodEnd)) {
            return false;
        }

        if ($batchFee->feeType?->frequency === 'monthly') {
            return ! $this->end_date || $this->end_date->gte($periodStart);
        }

        return true;
    }

    /**
     * Return the stored billing month for this fee type.
     */
    public function billingMonthForFee(BatchFee $batchFee, string $month): ?string
    {
        return $batchFee->feeType?->frequency === 'monthly' ? $month : null;
    }
}

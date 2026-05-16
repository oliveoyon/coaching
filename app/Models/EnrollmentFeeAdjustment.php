<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentFeeAdjustment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'batch_fee_id',
        'adjustment_type',
        'value_type',
        'value',
        'effective_from_month',
        'effective_to_month',
        'note',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    /**
     * Get the enrollment this adjustment belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the target fee item.
     */
    public function batchFee(): BelongsTo
    {
        return $this->belongsTo(BatchFee::class);
    }

    /**
     * Get the user who created this adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine whether this adjustment is active for the given month.
     */
    public function appliesToMonth(string $month): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_from_month && $month < $this->effective_from_month) {
            return false;
        }

        if ($this->effective_to_month && $month > $this->effective_to_month) {
            return false;
        }

        return true;
    }
}

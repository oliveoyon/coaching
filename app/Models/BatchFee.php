<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BatchFee extends Model
{
    protected $fillable = [
        'batch_id',
        'fee_type_id',
        'amount',
        'effective_from_month',
        'effective_to_month',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Normalize amount before save to avoid float drift.
     */
    protected static function booted(): void
    {
        static::saving(function (BatchFee $batchFee): void {
            $batchFee->amount = number_format((float) $batchFee->amount, 2, '.', '');
        });
    }

    /**
     * Get the owning batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the fee type definition.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get payments made against this fee item.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get month-specific amount overrides.
     */
    public function monthOverrides(): HasMany
    {
        return $this->hasMany(BatchFeeMonthOverride::class);
    }

    /**
     * Determine whether this fee is active for the selected month.
     */
    public function isEffectiveForMonth(string $month): bool
    {
        if ($this->effective_from_month && $month < $this->effective_from_month) {
            return false;
        }

        if ($this->effective_to_month && $month > $this->effective_to_month) {
            return false;
        }

        return true;
    }

    /**
     * Get the effective amount for a selected month.
     */
    public function amountForMonth(string $month): float
    {
        $override = $this->activeMonthOverrides()
            ->first(fn (BatchFeeMonthOverride $item) => $item->month === $month);

        if ($override) {
            return (float) $override->amount;
        }

        return (float) $this->amount;
    }

    /**
     * Get active month overrides from loaded relation or database.
     */
    public function activeMonthOverrides(): Collection
    {
        $overrides = $this->relationLoaded('monthOverrides')
            ? $this->monthOverrides
            : $this->monthOverrides()->get();

        return $overrides->where('status', 'active')->values();
    }

    /**
     * Get student-specific fee adjustments linked to this fee item.
     */
    public function feeAdjustments(): HasMany
    {
        return $this->hasMany(EnrollmentFeeAdjustment::class);
    }
}

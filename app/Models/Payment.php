<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'batch_fee_id',
        'amount',
        'month',
        'payment_date',
        'method',
        'transaction_id',
        'status',
        'collected_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    /**
     * Get the enrollment for this payment.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the fee item this payment belongs to.
     */
    public function batchFee(): BelongsTo
    {
        return $this->belongsTo(BatchFee::class);
    }

    /**
     * Get the collector user.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Get the approver user.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get income distributions created from this payment.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }
}

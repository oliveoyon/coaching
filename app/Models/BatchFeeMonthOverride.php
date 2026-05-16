<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchFeeMonthOverride extends Model
{
    protected $fillable = [
        'batch_fee_id',
        'month',
        'amount',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the parent batch fee.
     */
    public function batchFee(): BelongsTo
    {
        return $this->belongsTo(BatchFee::class);
    }
}

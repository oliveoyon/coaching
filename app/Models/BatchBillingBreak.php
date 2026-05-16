<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchBillingBreak extends Model
{
    protected $fillable = [
        'batch_id',
        'month',
        'note',
        'status',
    ];

    /**
     * Get the owning batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}

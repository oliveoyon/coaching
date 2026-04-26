<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'frequency',
        'status',
    ];

    /**
     * Get batch fee mappings using this fee type.
     */
    public function batchFees(): HasMany
    {
        return $this->hasMany(BatchFee::class);
    }
}

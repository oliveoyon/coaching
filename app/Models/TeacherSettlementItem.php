<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSettlementItem extends Model
{
    protected $fillable = [
        'teacher_settlement_id',
        'distribution_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the parent settlement.
     */
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(TeacherSettlement::class, 'teacher_settlement_id');
    }

    /**
     * Get the distribution this payout offsets.
     */
    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }
}

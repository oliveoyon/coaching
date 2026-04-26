<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherSettlement extends Model
{
    protected $fillable = [
        'teacher_id',
        'amount',
        'settlement_date',
        'paid_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'settlement_date' => 'date',
        ];
    }

    /**
     * Get the teacher receiving this payout.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the user who recorded this settlement.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Get settlement allocations against distributions.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TeacherSettlementItem::class);
    }
}

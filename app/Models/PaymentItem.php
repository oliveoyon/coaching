<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'payment_id',
    'student_due_id',
    'fee_head_id',
    'fee_structure_id',
    'billing_period_type',
    'billing_period_key',
    'period_start',
    'period_end',
    'is_advance',
    'charge_amount',
    'due_before',
    'paid_amount',
    'due_after',
    'notes',
])]
class PaymentItem extends Model
{
    use BelongsToTenant, HasFactory;

    public const PERIOD_TYPE_MONTH = 'month';
    public const PERIOD_TYPE_ONE_TIME = 'one_time';
    public const PERIOD_TYPE_CUSTOM = 'custom';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'is_advance' => 'boolean',
            'charge_amount' => 'decimal:2',
            'due_before' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_after' => 'decimal:2',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function periodTypes(): array
    {
        return [
            self::PERIOD_TYPE_MONTH,
            self::PERIOD_TYPE_ONE_TIME,
            self::PERIOD_TYPE_CUSTOM,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function studentDue(): BelongsTo
    {
        return $this->belongsTo(StudentDue::class);
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }
}

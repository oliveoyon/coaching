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
    'action_type',
    'status',
    'payload',
    'result',
    'processed_at',
    'error_message',
])]
class PaymentPostAction extends Model
{
    use BelongsToTenant, HasFactory;

    public const ACTION_PRINTABLE_RECEIPT = 'printable_receipt';
    public const ACTION_NORMAL_PRINTER = 'normal_printer';
    public const ACTION_POS_PRINTER = 'pos_printer';
    public const ACTION_SMS = 'sms';
    public const ACTION_WHATSAPP = 'whatsapp';
    public const ACTION_EMAIL = 'email';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function receiptActions(): array
    {
        return [
            self::ACTION_PRINTABLE_RECEIPT,
            self::ACTION_NORMAL_PRINTER,
            self::ACTION_POS_PRINTER,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function notificationActions(): array
    {
        return [
            self::ACTION_SMS,
            self::ACTION_WHATSAPP,
            self::ACTION_EMAIL,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

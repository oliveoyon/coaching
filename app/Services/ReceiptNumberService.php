<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;

class ReceiptNumberService
{
    public function generate(Tenant $tenant, Carbon $collectedOn): string
    {
        $prefix = 'RCT-'.$collectedOn->format('Ym').'-';

        $latestReceipt = Payment::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('receipt_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('receipt_no')
            ->value('receipt_no');

        $nextNumber = 1;

        if ($latestReceipt) {
            $lastSequence = (int) substr($latestReceipt, -4);
            $nextNumber = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

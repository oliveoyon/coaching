<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class ReceiptTemplateService
{
    public function viewFor(string $template): string
    {
        return match ($template) {
            'printable' => 'payments.receipts.printable',
            'normal' => 'payments.receipts.normal',
            'pos' => 'payments.receipts.pos',
            default => throw new InvalidArgumentException('Unknown receipt template type.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function render(Payment $payment, string $template): array
    {
        $payment->loadMissing(['student', 'batch', 'ownerTeacher', 'collector', 'items.feeHead', 'items.feeStructure']);

        $html = View::make($this->viewFor($template), [
            'payment' => $payment,
        ])->render();

        return [
            'template' => $template,
            'view' => $this->viewFor($template),
            'html' => $html,
        ];
    }
}

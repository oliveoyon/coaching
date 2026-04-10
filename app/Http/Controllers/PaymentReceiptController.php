<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\ReceiptTemplateService;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function __construct(
        protected ReceiptTemplateService $receiptTemplateService,
    ) {
    }

    public function show(Payment $payment, string $template): View
    {
        $this->authorize('view', $payment);

        abort_unless(in_array($template, ['printable', 'normal', 'pos'], true), 404);

        return view($this->receiptTemplateService->viewFor($template), [
            'payment' => $payment->load(['student', 'tenant', 'batch', 'ownerTeacher', 'collector', 'items.feeHead', 'items.feeStructure']),
        ]);
    }
}

<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Services\PaymentPostActionService;

class DispatchPostPaymentActions
{
    public function __construct(
        protected PaymentPostActionService $paymentPostActionService,
    ) {
    }

    public function handle(PaymentReceived $event): void
    {
        $this->paymentPostActionService->queueForPayment($event->payment);
    }
}

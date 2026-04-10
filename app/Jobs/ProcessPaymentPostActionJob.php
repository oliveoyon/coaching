<?php

namespace App\Jobs;

use App\Models\PaymentPostAction;
use App\Services\PaymentPostActionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaymentPostActionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PaymentPostAction $action,
    ) {
    }

    public function handle(PaymentPostActionService $paymentPostActionService): void
    {
        $paymentPostActionService->process($this->action->fresh());
    }
}

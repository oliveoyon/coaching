<?php

namespace App\Services;

use App\Contracts\EmailGateway;
use App\Contracts\SmsGateway;
use App\Contracts\WhatsAppGateway;
use App\Models\Payment;
use App\Models\PaymentPostAction;
use Illuminate\Support\Arr;
use Throwable;

class PaymentPostActionService
{
    public function __construct(
        protected PostPaymentSettingsResolver $settingsResolver,
        protected ReceiptTemplateService $receiptTemplateService,
        protected SmsGateway $smsGateway,
        protected WhatsAppGateway $whatsAppGateway,
        protected EmailGateway $emailGateway,
    ) {
    }

    public function queueForPayment(Payment $payment, bool $dispatchJobs = true): void
    {
        foreach ($this->settingsResolver->actionsForPayment($payment->loadMissing('tenant')) as $actionConfig) {
            $action = PaymentPostAction::query()->firstOrCreate(
                [
                    'tenant_id' => $payment->tenant_id,
                    'payment_id' => $payment->getKey(),
                    'action_type' => $actionConfig['type'],
                ],
                [
                    'status' => PaymentPostAction::STATUS_PENDING,
                    'payload' => $actionConfig,
                ],
            );

            if ($dispatchJobs && $action->status === PaymentPostAction::STATUS_PENDING) {
                \App\Jobs\ProcessPaymentPostActionJob::dispatch($action);
            }
        }
    }

    public function process(PaymentPostAction $action): void
    {
        $payment = $action->payment()->with(['student', 'collector', 'items'])->firstOrFail();
        $settings = $this->settingsResolver->settings($payment->tenant);

        try {
            $result = match ($action->action_type) {
                PaymentPostAction::ACTION_PRINTABLE_RECEIPT => $this->receiptTemplateService->render($payment, 'printable'),
                PaymentPostAction::ACTION_NORMAL_PRINTER => $this->receiptTemplateService->render($payment, 'normal'),
                PaymentPostAction::ACTION_POS_PRINTER => $this->receiptTemplateService->render($payment, 'pos'),
                PaymentPostAction::ACTION_SMS => $this->sendSms($payment, $settings),
                PaymentPostAction::ACTION_WHATSAPP => $this->sendWhatsApp($payment, $settings),
                PaymentPostAction::ACTION_EMAIL => $this->sendEmail($payment, $settings),
                default => throw new \InvalidArgumentException('Unsupported post-payment action type.'),
            };

            $action->forceFill([
                'status' => ($result['status'] ?? null) === 'skipped'
                    ? PaymentPostAction::STATUS_SKIPPED
                    : PaymentPostAction::STATUS_COMPLETED,
                'result' => $result,
                'processed_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (Throwable $throwable) {
            $action->forceFill([
                'status' => PaymentPostAction::STATUS_FAILED,
                'processed_at' => now(),
                'error_message' => $throwable->getMessage(),
            ])->save();
        }
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function sendSms(Payment $payment, array $settings): array
    {
        $phone = $payment->student?->phone;

        if (! filled($phone)) {
            return ['status' => 'skipped', 'reason' => 'Student phone is not available.'];
        }

        return $this->smsGateway->send(
            $phone,
            $this->interpolate($settings['templates']['sms'] ?? '', $payment),
            ['payment_id' => $payment->getKey(), 'receipt_no' => $payment->receipt_no],
        );
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function sendWhatsApp(Payment $payment, array $settings): array
    {
        $phone = $payment->student?->phone;

        if (! filled($phone)) {
            return ['status' => 'skipped', 'reason' => 'Student phone is not available.'];
        }

        return $this->whatsAppGateway->send(
            $phone,
            $this->interpolate($settings['templates']['whatsapp'] ?? '', $payment),
            ['payment_id' => $payment->getKey(), 'receipt_no' => $payment->receipt_no],
        );
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function sendEmail(Payment $payment, array $settings): array
    {
        $email = $payment->student?->email;

        if (! filled($email)) {
            return ['status' => 'skipped', 'reason' => 'Student email is not available.'];
        }

        return $this->emailGateway->send(
            $email,
            $this->interpolate($settings['templates']['email_subject'] ?? 'Payment Receipt {{receipt_no}}', $payment),
            $this->interpolate($settings['templates']['email_body'] ?? '', $payment),
            ['payment_id' => $payment->getKey(), 'receipt_no' => $payment->receipt_no],
        );
    }

    protected function interpolate(string $template, Payment $payment): string
    {
        $totalPaid = (float) $payment->items->sum('paid_amount');
        $totalDueAfter = (float) $payment->items->sum('due_after');

        $replacements = [
            '{{receipt_no}}' => $payment->receipt_no,
            '{{student_name}}' => $payment->student?->name ?? 'Student',
            '{{paid_amount}}' => number_format($totalPaid, 2),
            '{{due_amount}}' => number_format($totalDueAfter, 2),
            '{{collector_name}}' => $payment->collector?->name ?? 'Collector',
        ];

        return strtr($template, $replacements);
    }
}

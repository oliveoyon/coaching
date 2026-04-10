<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentPostAction;
use App\Models\Tenant;
use App\Support\TenantSettingsDefaults;

class PostPaymentSettingsResolver
{
    public function __construct(
        protected TenantSettingsService $tenantSettingsService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(Tenant $tenant): array
    {
        return $this->tenantSettingsService->get($tenant, 'payments.post_actions', $this->defaultSettings());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function actionsForPayment(Payment $payment): array
    {
        $settings = $this->settings($payment->tenant);
        $channels = $this->tenantSettingsService->get($payment->tenant, 'communication.channels', []);
        $events = $this->tenantSettingsService->get($payment->tenant, 'communication.events', []);

        if (! ($settings['enabled'] ?? true) || (($events['fee_payment'] ?? true) !== true)) {
            return [];
        }

        $actions = [];

        if (($settings['receipts']['printable'] ?? false) === true) {
            $actions[] = ['type' => PaymentPostAction::ACTION_PRINTABLE_RECEIPT];
        }

        if (($settings['receipts']['normal_printer'] ?? false) === true) {
            $actions[] = ['type' => PaymentPostAction::ACTION_NORMAL_PRINTER];
        }

        if (($settings['receipts']['pos_printer'] ?? false) === true) {
            $actions[] = ['type' => PaymentPostAction::ACTION_POS_PRINTER];
        }

        if (($settings['notifications']['sms'] ?? false) === true && (($channels['sms'] ?? false) === true)) {
            $actions[] = ['type' => PaymentPostAction::ACTION_SMS];
        }

        if (($settings['notifications']['whatsapp'] ?? false) === true && (($channels['whatsapp'] ?? false) === true)) {
            $actions[] = ['type' => PaymentPostAction::ACTION_WHATSAPP];
        }

        if (($settings['notifications']['email'] ?? false) === true && (($channels['email'] ?? false) === true) && (($events['fee_payment'] ?? true) === true)) {
            $actions[] = ['type' => PaymentPostAction::ACTION_EMAIL];
        }

        return $actions;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultSettings(): array
    {
        return TenantSettingsDefaults::paymentPostActions();
    }
}

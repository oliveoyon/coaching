<?php

namespace App\Support;

use App\Models\Tenant;

class TenantSettingsDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function communicationChannels(?Tenant $tenant = null): array
    {
        return [
            'sms' => false,
            'whatsapp' => false,
            'email' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function communicationEvents(?Tenant $tenant = null): array
    {
        return [
            'admission' => true,
            'fee_payment' => true,
            'due_reminder' => false,
            'attendance_alert' => false,
            'exam_notice' => false,
            'result_publish' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paymentPostActions(?Tenant $tenant = null): array
    {
        return [
            'enabled' => true,
            'receipts' => [
                'printable' => true,
                'normal_printer' => true,
                'pos_printer' => false,
            ],
            'notifications' => [
                'sms' => false,
                'whatsapp' => false,
                'email' => true,
            ],
            'templates' => [
                'receipt_default' => 'printable',
                'sms' => 'Thank you {{student_name}}. Receipt {{receipt_no}} paid {{paid_amount}}. Due {{due_amount}}.',
                'whatsapp' => 'Receipt {{receipt_no}} for {{student_name}}: paid {{paid_amount}}, due {{due_amount}}.',
                'email_subject' => 'Payment Receipt {{receipt_no}}',
                'email_body' => 'Hello, payment of {{paid_amount}} received for {{student_name}}. Remaining due: {{due_amount}}.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function billingDefaults(Tenant $tenant): array
    {
        return [
            'model' => $tenant->billing_model,
            'currency' => $tenant->currency,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(Tenant $tenant): array
    {
        return [
            'communication.channels' => self::communicationChannels($tenant),
            'communication.events' => self::communicationEvents($tenant),
            'payments.post_actions' => self::paymentPostActions($tenant),
            'billing.defaults' => self::billingDefaults($tenant),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantPostPaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isAdmin()
            && $user->can('tenant.settings.manage');
    }

    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'receipts.printable' => ['nullable', 'boolean'],
            'receipts.normal_printer' => ['nullable', 'boolean'],
            'receipts.pos_printer' => ['nullable', 'boolean'],
            'notifications.sms' => ['nullable', 'boolean'],
            'notifications.whatsapp' => ['nullable', 'boolean'],
            'notifications.email' => ['nullable', 'boolean'],
            'templates.receipt_default' => ['required', Rule::in(['printable', 'normal', 'pos'])],
            'templates.sms' => ['nullable', 'string', 'max:500'],
            'templates.whatsapp' => ['nullable', 'string', 'max:500'],
            'templates.email_subject' => ['nullable', 'string', 'max:255'],
            'templates.email_body' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

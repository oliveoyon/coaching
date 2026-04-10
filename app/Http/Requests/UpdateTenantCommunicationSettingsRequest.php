<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantCommunicationSettingsRequest extends FormRequest
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
            'channels.sms' => ['nullable', 'boolean'],
            'channels.whatsapp' => ['nullable', 'boolean'],
            'channels.email' => ['nullable', 'boolean'],
            'events.admission' => ['nullable', 'boolean'],
            'events.fee_payment' => ['nullable', 'boolean'],
            'events.due_reminder' => ['nullable', 'boolean'],
            'events.attendance_alert' => ['nullable', 'boolean'],
            'events.exam_notice' => ['nullable', 'boolean'],
            'events.result_publish' => ['nullable', 'boolean'],
        ];
    }
}

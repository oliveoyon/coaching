<?php

namespace App\Services\Gateways;

use App\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Log;

class LogWhatsAppGateway implements WhatsAppGateway
{
    public function send(string $to, string $message, array $context = []): array
    {
        Log::info('WhatsApp gateway stub called.', [
            'to' => $to,
            'message' => $message,
            'context' => $context,
        ]);

        return [
            'driver' => 'log_whatsapp',
            'to' => $to,
            'accepted' => true,
        ];
    }
}

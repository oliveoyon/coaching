<?php

namespace App\Services\Gateways;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message, array $context = []): array
    {
        Log::info('SMS gateway stub called.', [
            'to' => $to,
            'message' => $message,
            'context' => $context,
        ]);

        return [
            'driver' => 'log_sms',
            'to' => $to,
            'accepted' => true,
        ];
    }
}

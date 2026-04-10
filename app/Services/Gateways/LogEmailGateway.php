<?php

namespace App\Services\Gateways;

use App\Contracts\EmailGateway;
use Illuminate\Support\Facades\Log;

class LogEmailGateway implements EmailGateway
{
    public function send(string $to, string $subject, string $body, array $context = []): array
    {
        Log::info('Email gateway stub called.', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'context' => $context,
        ]);

        return [
            'driver' => 'log_email',
            'to' => $to,
            'accepted' => true,
        ];
    }
}

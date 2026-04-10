<?php

namespace App\Contracts;

interface WhatsAppGateway
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function send(string $to, string $message, array $context = []): array;
}

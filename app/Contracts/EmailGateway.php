<?php

namespace App\Contracts;

interface EmailGateway
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function send(string $to, string $subject, string $body, array $context = []): array;
}

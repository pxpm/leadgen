<?php

declare(strict_types=1);

namespace App\Contracts;

interface SmsProvider
{
    public function send(string $to, string $message): SmsResult;
}

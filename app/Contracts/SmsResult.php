<?php

declare(strict_types=1);

namespace App\Contracts;

readonly class SmsResult
{
    public function __construct(
        public bool $success,
        public ?string $messageId = null,
        public ?string $error = null,
    ) {}
}

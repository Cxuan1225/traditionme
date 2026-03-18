<?php

declare(strict_types=1);

namespace App\DTOs\Commerce;

readonly class PaymentSession
{
    public function __construct(
        public string $sessionId,
        public string $checkoutUrl,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\DTOs\Commerce;

readonly class PaymentVerification
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public int $amountInSen,
    ) {}
}

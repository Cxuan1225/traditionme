<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Contracts\PaymentGateway;
use Illuminate\Http\Request;

readonly class ProcessPaymentWebhookAction
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {}

    public function __invoke(Request $request): void
    {
        $this->gateway->handleWebhook($request);
    }
}

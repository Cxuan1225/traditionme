<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Contracts\PaymentGateway;
use App\Models\Order;

readonly class InitiatePaymentAction
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {}

    public function __invoke(Order $order): string
    {
        $session = $this->gateway->createCheckoutSession($order);

        $order->update([
            'payment_session_id' => $session->sessionId,
            'payment_method' => 'stripe',
        ]);

        return $session->checkoutUrl;
    }
}

<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Commerce\PaymentSession;
use App\DTOs\Commerce\PaymentVerification;
use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createCheckoutSession(Order $order): PaymentSession;

    public function handleWebhook(Request $request): void;

    public function verifyPayment(string $sessionId): PaymentVerification;
}

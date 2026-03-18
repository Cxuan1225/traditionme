<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\DTOs\Commerce\PaymentSession;
use App\DTOs\Commerce\PaymentVerification;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('payment.stripe.secret'));
    }

    public function createCheckoutSession(Order $order): PaymentSession
    {
        $order->load('items');

        /** @var array<int, array{price_data: array{currency: string, product_data: array{name: string}, unit_amount: int}, quantity: int}> $lineItems */
        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency' => config('payment.currency'),
                'product_data' => [
                    'name' => $item->product_name,
                ],
                'unit_amount' => $item->unit_price_in_sen,
            ],
            'quantity' => $item->quantity,
        ])->all();

        if ($order->discount_in_sen > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => config('payment.currency'),
                    'product_data' => [
                        'name' => 'Discount'.($order->coupon_code !== null ? ' ('.$order->coupon_code.')' : ''),
                    ],
                    'unit_amount' => -$order->discount_in_sen,
                ],
                'quantity' => 1,
            ];
        }

        if ($order->shipping_in_sen > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => config('payment.currency'),
                    'product_data' => [
                        'name' => 'Shipping',
                    ],
                    'unit_amount' => $order->shipping_in_sen,
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card', 'fpx'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url('/payment/success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/payment/cancel').'?order_id='.$order->id,
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        return new PaymentSession(
            sessionId: $session->id,
            checkoutUrl: $session->url ?? '',
        );
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $webhookSecret = config('payment.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException) {
            throw new RuntimeException('Invalid Stripe webhook signature.');
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var Session $session */
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            if ($orderId === null) {
                return;
            }

            $order = Order::find((int) $orderId);

            if ($order === null || $order->status !== 'pending') {
                return;
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
                'payment_method' => 'stripe',
                'payment_transaction_id' => $session->payment_intent,
            ]);

            $order->load('user');
            $order->user->notify(new OrderStatusChangedNotification($order, 'paid'));
        }
    }

    public function verifyPayment(string $sessionId): PaymentVerification
    {
        $session = Session::retrieve($sessionId);

        return new PaymentVerification(
            success: $session->payment_status === 'paid',
            transactionId: (string) ($session->payment_intent ?? ''),
            amountInSen: (int) ($session->amount_total ?? 0),
        );
    }
}

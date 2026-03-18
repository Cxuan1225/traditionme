<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\InitiatePaymentAction;
use App\Actions\Commerce\ProcessPaymentWebhookAction;
use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PaymentController extends Controller
{
    public function initiate(Request $request, Order $order, InitiatePaymentAction $action): RedirectResponse|SymfonyResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()
                ->route('account.orders.show', $order)
                ->with('status', 'This order has already been processed.');
        }

        $checkoutUrl = $action($order);

        return Inertia::location($checkoutUrl);
    }

    public function success(Request $request, PaymentGateway $gateway): InertiaResponse|RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return redirect()->route('account.orders.index');
        }

        $order = Order::where('payment_session_id', $sessionId)->first();

        if ($order === null) {
            return redirect()->route('account.orders.index');
        }

        $verification = $gateway->verifyPayment($sessionId);

        $order->load('items.product');

        return Inertia::render('PaymentSuccess', [
            'order' => new OrderResource($order),
            'verified' => $verification->success,
        ]);
    }

    public function cancel(Request $request): InertiaResponse|RedirectResponse
    {
        $orderId = $request->query('order_id');

        if (! is_string($orderId) || $orderId === '') {
            return redirect()->route('account.orders.index');
        }

        $order = Order::find((int) $orderId);

        if ($order === null) {
            return redirect()->route('account.orders.index');
        }

        $order->load('items.product');

        return Inertia::render('PaymentCancel', [
            'order' => new OrderResource($order),
        ]);
    }

    public function webhook(Request $request, ProcessPaymentWebhookAction $action): Response
    {
        $action($request);

        return response('OK', 200);
    }
}

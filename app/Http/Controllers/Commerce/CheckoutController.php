<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\GetCartAction;
use App\Actions\Commerce\PlaceOrderAction;
use App\DTOs\Commerce\PlaceOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CheckoutRequest;
use App\Http\Resources\CartLineResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(Request $request, GetCartAction $action): Response|RedirectResponse
    {
        $lines = $action($request->session());

        if ($lines === []) {
            return redirect()
                ->route('cart.show')
                ->with('status', 'Add items to your cart before checkout.');
        }

        $subtotalInSen = (int) collect($lines)->sum(
            fn (array $line): int => $line['product']->price_in_sen * $line['quantity'],
        );
        $shippingInSen = $subtotalInSen >= 20_000 ? 0 : 1_200;

        return Inertia::render('Checkout', [
            'lines' => CartLineResource::collection($lines)->resolve($request),
            'summary' => [
                'itemCount' => (int) collect($lines)->sum('quantity'),
                'subtotalInSen' => $subtotalInSen,
                'discountInSen' => 0,
                'shippingInSen' => $shippingInSen,
                'totalInSen' => $subtotalInSen + $shippingInSen,
            ],
        ]);
    }

    public function store(
        CheckoutRequest $request,
        PlaceOrderAction $action,
    ): RedirectResponse {
        try {
            $order = $action(
                $request->session(),
                $request->user() ?? abort(401),
                PlaceOrderData::fromRequest($request),
            );
        } catch (RuntimeException) {
            return redirect()
                ->route('cart.show')
                ->with('status', 'Add items to your cart before checkout.');
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Order placed successfully.');
    }
}

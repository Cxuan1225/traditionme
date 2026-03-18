<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\GetCartAction;
use App\Actions\Commerce\PlaceOrderAction;
use App\DTOs\Commerce\PlaceOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CheckoutRequest;
use App\Http\Resources\CartLineResource;
use App\Models\User;
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

        $subtotalInSen = array_reduce(
            $lines,
            static fn (int $sum, array $line): int => $sum + ($line['product']->price_in_sen * $line['quantity']),
            0,
        );
        $shippingInSen = $subtotalInSen >= 20_000 ? 0 : 1_200;
        $itemCount = array_reduce(
            $lines,
            static fn (int $sum, array $line): int => $sum + $line['quantity'],
            0,
        );

        return Inertia::render('Checkout', [
            'lines' => CartLineResource::collection($lines)->resolve($request),
            'summary' => [
                'itemCount' => $itemCount,
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
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        try {
            $order = $action(
                $request->session(),
                $user,
                PlaceOrderData::fromRequest($request),
            );
        } catch (RuntimeException) {
            return redirect()
                ->route('cart.show')
                ->with('status', 'Add items to your cart before checkout.');
        }

        return redirect()
            ->route('orders.pay', $order);
    }
}

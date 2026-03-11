<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        return Inertia::render('OrderConfirmation', [
            'order' => OrderResource::make($order->load(['items.product']))->resolve($request),
        ]);
    }
}

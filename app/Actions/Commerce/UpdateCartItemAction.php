<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;

class UpdateCartItemAction
{
    public function __invoke(Session $session, Product $product, int $quantity): void
    {
        /** @var array<string, int> $cart */
        $cart = Arr::wrap($session->get('cart.items', []));

        if ($quantity <= 0) {
            unset($cart[$product->slug]);
        } else {
            if (! $product->hasStockFor($quantity)) {
                $quantity = $product->track_stock ? $product->stock_quantity : $quantity;
            }

            $cart[$product->slug] = $quantity;
        }

        $session->put('cart.items', $cart);
    }
}

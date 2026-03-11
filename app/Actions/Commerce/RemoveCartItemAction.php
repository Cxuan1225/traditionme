<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;

class RemoveCartItemAction
{
    public function __invoke(Session $session, Product $product): void
    {
        /** @var array<string, int> $cart */
        $cart = Arr::wrap($session->get('cart.items', []));

        unset($cart[$product->slug]);

        $session->put('cart.items', $cart);
    }
}

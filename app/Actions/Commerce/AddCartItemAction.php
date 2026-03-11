<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\DTOs\Commerce\AddCartItemData;
use App\Models\Product;
use Illuminate\Contracts\Session\Session;

class AddCartItemAction
{
    public function __invoke(Session $session, AddCartItemData $data): bool
    {
        if (! Product::where('slug', $data->productSlug)->where('is_active', true)->exists()) {
            return false;
        }

        /** @var array<string, int> $cart */
        $cart = $session->get('cart.items', []);
        $currentQuantity = $cart[$data->productSlug] ?? 0;
        $cart[$data->productSlug] = $currentQuantity + 1;

        $session->put('cart.items', $cart);

        return true;
    }
}

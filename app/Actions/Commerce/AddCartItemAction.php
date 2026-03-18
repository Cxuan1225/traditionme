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
        $product = Product::where('slug', $data->productSlug)->where('is_active', true)->first();

        if ($product === null) {
            return false;
        }

        /** @var array<string, int> $cart */
        $cart = $session->get('cart.items', []);
        $currentQuantity = $cart[$data->productSlug] ?? 0;
        $newQuantity = $currentQuantity + 1;

        if (! $product->hasStockFor($newQuantity)) {
            return false;
        }

        $cart[$data->productSlug] = $newQuantity;

        $session->put('cart.items', $cart);

        return true;
    }
}

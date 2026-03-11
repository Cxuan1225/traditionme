<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;

class GetCartAction
{
    /**
     * @return list<array{product: Product, quantity: int}>
     */
    public function __invoke(Session $session): array
    {
        /** @var array<string, int> $cartItems */
        $cartItems = Arr::wrap($session->get('cart.items', []));

        if ($cartItems === []) {
            return [];
        }

        $products = Product::whereIn('slug', array_keys($cartItems))
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        $lines = [];

        foreach ($cartItems as $slug => $quantity) {
            $product = $products->get($slug);

            if ($product instanceof Product) {
                $lines[] = [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                ];
            }
        }

        return $lines;
    }
}

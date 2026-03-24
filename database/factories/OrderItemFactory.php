<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->numberBetween(1000, 30000);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product,
            'product_name' => fake()->words(3, true),
            'unit_price_in_sen' => $unitPrice,
            'quantity' => $quantity,
            'subtotal_in_sen' => $unitPrice * $quantity,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(2, 4), true);
        $price = fake()->numberBetween(500, 50000);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['traditional-wear', 'accessories', 'home-decor', 'food', 'crafts']),
            'description' => fake()->paragraph(),
            'price_in_sen' => $price,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => null,
            'image_url' => null,
            'is_active' => true,
            'stock_quantity' => fake()->numberBetween(10, 200),
            'track_stock' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $originalPrice = $attributes['price_in_sen'] * fake()->randomElement([1.2, 1.5, 2.0]);

            return [
                'original_price_in_sen' => (int) $originalPrice,
                'badge' => 'Sale',
            ];
        });
    }
}

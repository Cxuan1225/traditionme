<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 100000);
        $shipping = fake()->randomElement([0, 500, 1000, 1500]);

        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'subtotal_in_sen' => $subtotal,
            'discount_in_sen' => 0,
            'shipping_in_sen' => $shipping,
            'total_in_sen' => $subtotal + $shipping,
            'shipping_name' => fake()->name(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->randomElement(['Selangor', 'Kuala Lumpur', 'Penang', 'Johor', 'Sabah', 'Sarawak', 'Perak', 'Kedah', 'Kelantan', 'Terengganu', 'Pahang', 'Melaka', 'Negeri Sembilan', 'Perlis']),
            'shipping_postcode' => fake()->numerify('#####'),
            'shipping_phone' => fake()->numerify('01#-########'),
            'coupon_code' => null,
            'notes' => null,
            'payment_method' => null,
            'payment_session_id' => null,
            'payment_transaction_id' => null,
            'paid_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'payment_method' => 'stripe',
            'payment_transaction_id' => 'pi_'.fake()->unique()->regexify('[a-zA-Z0-9]{24}'),
            'paid_at' => now(),
        ]);
    }

    public function shipped(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->shipped()->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}

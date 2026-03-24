<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Ahmad',
                'email_verified_at' => now(),
                'password' => Hash::make('Customer123'),
            ],
        );

        $userRole = Role::findByName('user', 'web');
        $customer->syncRoles([$userRole]);

        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        $this->createOrder($customer, $products->random(3)->all(), 'paid');
        $this->createOrder($customer, $products->random(2)->all(), 'shipped');
        $this->createOrder($customer, $products->random(1)->all(), 'delivered');
        $this->createOrder($customer, $products->random(2)->all(), 'pending');
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function createOrder(User $user, array $products, string $status): void
    {
        $subtotal = 0;
        $items = [];

        foreach ($products as $product) {
            $quantity = rand(1, 3);
            $itemSubtotal = $product->price_in_sen * $quantity;
            $subtotal += $itemSubtotal;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_in_sen' => $product->price_in_sen,
                'quantity' => $quantity,
                'subtotal_in_sen' => $itemSubtotal,
            ];
        }

        $shipping = 1000;

        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => $status,
            'subtotal_in_sen' => $subtotal,
            'discount_in_sen' => 0,
            'shipping_in_sen' => $shipping,
            'total_in_sen' => $subtotal + $shipping,
            'shipping_name' => $user->name,
            'shipping_address' => '123 Jalan Bukit Bintang',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Kuala Lumpur',
            'shipping_postcode' => '55100',
            'shipping_phone' => '012-3456789',
            'payment_method' => $status !== 'pending' ? 'stripe' : null,
            'payment_transaction_id' => $status !== 'pending' ? 'pi_seed_'.fake()->unique()->regexify('[a-z0-9]{16}') : null,
            'paid_at' => in_array($status, ['paid', 'shipped', 'delivered']) ? now()->subDays(rand(1, 14)) : null,
            'shipped_at' => in_array($status, ['shipped', 'delivered']) ? now()->subDays(rand(1, 7)) : null,
            'delivered_at' => $status === 'delivered' ? now()->subDays(rand(1, 3)) : null,
        ]);

        foreach ($items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                ...$item,
            ]);
        }
    }
}

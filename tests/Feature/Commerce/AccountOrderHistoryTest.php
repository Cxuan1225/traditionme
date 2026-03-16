<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated user can filter their account orders by status', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $matchingOrder = createCustomerOrderFixture($user, 'paid');
    createCustomerOrderFixture($user, 'pending');
    createCustomerOrderFixture($otherUser, 'paid');

    $response = $this->actingAs($user)->get(route('account.orders.index', [
        'status' => 'paid',
    ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('account/Orders')
            ->where('filters.status', 'paid')
            ->where('orders.meta.total', 1)
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $matchingOrder->id)
            ->where('orders.data.0.number', sprintf('TM-%06d', $matchingOrder->id))
            ->where('orders.data.0.status', 'paid'));
});

test('authenticated user can view their account order detail', function (): void {
    $user = User::factory()->create();
    $order = createCustomerOrderFixture($user, 'shipped');

    $response = $this->actingAs($user)->get(route('account.orders.show', $order));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('account/OrderDetail')
            ->where('order.id', $order->id)
            ->where('order.number', sprintf('TM-%06d', $order->id))
            ->where('order.status', 'shipped')
            ->where('order.shippedAt', $order->shipped_at?->toIso8601String())
            ->has('order.items', 1));
});

test('user cannot view another users account order detail', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = createCustomerOrderFixture($owner, 'pending');

    $this->actingAs($otherUser)
        ->get(route('account.orders.show', $order))
        ->assertForbidden();
});

function createCustomerOrderFixture(User $user, string $status): Order
{
    $product = Product::query()->create([
        'name' => 'Artisan Kebaya '.$user->id.'-'.$status,
        'slug' => 'artisan-kebaya-'.$user->id.'-'.$status,
        'category' => 'Women',
        'description' => 'Tailored for celebratory evenings.',
        'price_in_sen' => 20900,
        'original_price_in_sen' => 23900,
        'badge' => 'Limited',
        'gradient' => 'from-amber-100 to-rose-100',
        'image_url' => null,
        'is_active' => true,
    ]);

    $timestamps = [
        'paid' => ['paid_at' => Carbon::parse('2026-03-15 10:00:00')],
        'shipped' => [
            'paid_at' => Carbon::parse('2026-03-15 10:00:00'),
            'shipped_at' => Carbon::parse('2026-03-16 09:30:00'),
        ],
        'delivered' => [
            'paid_at' => Carbon::parse('2026-03-14 12:00:00'),
            'shipped_at' => Carbon::parse('2026-03-15 08:30:00'),
            'delivered_at' => Carbon::parse('2026-03-16 16:45:00'),
        ],
    ];

    /** @var Order $order */
    $order = Order::query()->create(array_merge([
        'user_id' => $user->id,
        'status' => $status,
        'subtotal_in_sen' => 20900,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 0,
        'total_in_sen' => 20900,
        'shipping_name' => $user->name,
        'shipping_address' => '18 Jalan Warisan',
        'shipping_city' => 'George Town',
        'shipping_state' => 'Penang',
        'shipping_postcode' => '10200',
        'shipping_phone' => '0126677889',
        'coupon_code' => null,
        'notes' => 'Ring the bell on arrival.',
    ], $timestamps[$status] ?? []));

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price_in_sen' => $product->price_in_sen,
        'quantity' => 1,
        'subtotal_in_sen' => $product->price_in_sen,
    ]);

    return $order->load(['items.product']);
}

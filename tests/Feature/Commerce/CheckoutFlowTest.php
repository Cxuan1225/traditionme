<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('checkout page redirects to cart when cart is empty', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('checkout.show'));

    $response->assertRedirect(route('cart.show'));
    $response->assertSessionHas('status', 'Add items to your cart before checkout.');
});

test('authenticated user can place an order and cart is cleared', function (): void {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Batik Heritage',
        'slug' => 'batik-heritage',
        'category' => 'Women',
        'price_in_sen' => 15000,
        'original_price_in_sen' => 18000,
        'badge' => 'Featured',
        'gradient' => 'from-amber-100 to-orange-200',
        'image_url' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'cart.items' => [
                $product->slug => 2,
            ],
        ])
        ->post(route('checkout.store'), [
            'shipping_name' => 'Aisyah Ahmad',
            'shipping_address' => '12 Jalan Warisan',
            'shipping_city' => 'Shah Alam',
            'shipping_state' => 'Selangor',
            'shipping_postcode' => '40100',
            'shipping_phone' => '0123456789',
            'coupon_code' => 'HERITAGE10',
            'notes' => 'Please call on arrival.',
        ]);

    $order = Order::query()->with('items')->firstOrFail();

    $response->assertRedirect(route('orders.show', $order));
    $response->assertSessionHas('status', 'Order placed successfully.');
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'user_id' => $user->id,
        'status' => 'pending',
        'subtotal_in_sen' => 30000,
        'discount_in_sen' => 3000,
        'shipping_in_sen' => 0,
        'total_in_sen' => 27000,
        'coupon_code' => 'HERITAGE10',
    ]);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Batik Heritage',
        'unit_price_in_sen' => 15000,
        'quantity' => 2,
        'subtotal_in_sen' => 30000,
    ]);
    expect($order->items)->toHaveCount(1);
    expect(session('cart.items'))->toBeNull();
});

test('user cannot view another users order confirmation', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Songket Legacy',
        'slug' => 'songket-legacy',
        'category' => 'Men',
        'price_in_sen' => 12000,
        'original_price_in_sen' => null,
        'badge' => null,
        'gradient' => 'from-orange-100 to-amber-200',
        'image_url' => null,
        'is_active' => true,
    ]);
    $order = Order::query()->create([
        'user_id' => $owner->id,
        'status' => 'pending',
        'subtotal_in_sen' => 12000,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 1200,
        'total_in_sen' => 13200,
        'shipping_name' => 'Owner User',
        'shipping_address' => '8 Jalan Sentosa',
        'shipping_city' => 'Ipoh',
        'shipping_state' => 'Perak',
        'shipping_postcode' => '30000',
        'shipping_phone' => '01122334455',
        'coupon_code' => null,
        'notes' => null,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price_in_sen' => $product->price_in_sen,
        'quantity' => 1,
        'subtotal_in_sen' => $product->price_in_sen,
    ]);

    $response = $this->actingAs($otherUser)->get(route('orders.show', $order));

    $response->assertForbidden();

    $this->actingAs($owner)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('OrderConfirmation')
            ->where('order.id', $order->id)
            ->where('order.number', sprintf('TM-%06d', $order->id))
            ->where('order.summary.totalInSen', 13200)
            ->where('order.shipping.name', 'Owner User')
            ->has('order.items', 1));
});

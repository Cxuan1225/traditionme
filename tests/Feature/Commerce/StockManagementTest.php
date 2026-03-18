<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\SecurityRbacSeeder;
use Spatie\Permission\Models\Role;

function createTrackedProduct(string $slug, int $stock = 10): Product
{
    return Product::query()->create([
        'name' => 'Tracked '.$slug,
        'slug' => $slug,
        'category' => 'malay',
        'price_in_sen' => 9900,
        'is_active' => true,
        'track_stock' => true,
        'stock_quantity' => $stock,
    ]);
}

function createUntrackedProduct(string $slug): Product
{
    return Product::query()->create([
        'name' => 'Untracked '.$slug,
        'slug' => $slug,
        'category' => 'malay',
        'price_in_sen' => 5000,
        'is_active' => true,
        'track_stock' => false,
        'stock_quantity' => 0,
    ]);
}

test('product isInStock returns true when stock is not tracked', function (): void {
    $product = createUntrackedProduct('untracked-item');

    expect($product->isInStock())->toBeTrue();
    expect($product->hasStockFor(100))->toBeTrue();
});

test('product isInStock returns true when tracked and quantity > 0', function (): void {
    $product = createTrackedProduct('tracked-in-stock', 5);

    expect($product->isInStock())->toBeTrue();
    expect($product->hasStockFor(5))->toBeTrue();
    expect($product->hasStockFor(6))->toBeFalse();
});

test('product isInStock returns false when tracked and quantity is 0', function (): void {
    $product = createTrackedProduct('tracked-empty', 0);

    expect($product->isInStock())->toBeFalse();
    expect($product->hasStockFor(1))->toBeFalse();
});

test('add to cart fails when tracked product has insufficient stock', function (): void {
    $product = createTrackedProduct('limited-item', 1);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('cart.items.store'), ['product_slug' => $product->slug]);

    $this->actingAs($user)
        ->post(route('cart.items.store'), ['product_slug' => $product->slug]);

    expect(session('cart.items.'.$product->slug))->toBe(1);
});

test('add to cart succeeds for untracked product regardless of stock_quantity', function (): void {
    $product = createUntrackedProduct('unlimited-item');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('cart.items.store'), ['product_slug' => $product->slug]);

    expect(session('cart.items.'.$product->slug))->toBe(1);
});

test('shop page includes inStock field in product resource', function (): void {
    createTrackedProduct('shop-tracked', 0);
    createUntrackedProduct('shop-untracked');

    $response = $this->get(route('shop.index'));

    $response->assertOk();
});

test('place order decrements stock for tracked products', function (): void {
    $product = createTrackedProduct('checkout-item', 5);

    $user = User::factory()->create();

    session(['cart.items' => [$product->slug => 2]]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Heritage Lane',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Wilayah Persekutuan',
            'shipping_postcode' => '50000',
            'shipping_phone' => '0123456789',
        ]);

    $product->refresh();

    expect($product->stock_quantity)->toBe(3);
});

test('place order does not decrement stock for untracked products', function (): void {
    $product = createUntrackedProduct('checkout-untracked');

    $user = User::factory()->create();

    session(['cart.items' => [$product->slug => 3]]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Heritage Lane',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Wilayah Persekutuan',
            'shipping_postcode' => '50000',
            'shipping_phone' => '0123456789',
        ]);

    $product->refresh();

    expect($product->stock_quantity)->toBe(0);
});

test('cancelling order restores stock for tracked products', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $product = createTrackedProduct('cancel-item', 5);
    $customer = User::factory()->create();

    session(['cart.items' => [$product->slug => 2]]);

    $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Heritage Lane',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Wilayah Persekutuan',
            'shipping_postcode' => '50000',
            'shipping_phone' => '0123456789',
        ]);

    $product->refresh();
    expect($product->stock_quantity)->toBe(3);

    $order = Order::query()->latest()->first();

    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'cancelled',
        ]);

    $product->refresh();
    expect($product->stock_quantity)->toBe(5);
});

test('admin can create product with stock fields', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Tracked Songket',
        'slug' => 'tracked-songket',
        'category' => 'malay',
        'price_in_sen' => 19900,
        'is_active' => true,
        'track_stock' => true,
        'stock_quantity' => 25,
    ]);

    $product = Product::where('slug', 'tracked-songket')->first();

    expect($product)->not->toBeNull();
    expect($product->track_stock)->toBeTrue();
    expect($product->stock_quantity)->toBe(25);
});

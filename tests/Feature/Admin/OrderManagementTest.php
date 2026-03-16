<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\SecurityRbacSeeder;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin can filter and search orders from the admin orders index', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $targetCustomer = User::factory()->create(['name' => 'Nur Aina']);
    $otherCustomer = User::factory()->create(['name' => 'Farid']);

    $matchingOrder = createAdminOrderFixture($targetCustomer, 'paid');
    createAdminOrderFixture($otherCustomer, 'pending');
    $matchingOrderNumber = sprintf('TM-%06d', $matchingOrder->id);

    $response = $this->actingAs($admin)->get(route('admin.orders.index', [
        'status' => 'paid',
        'search' => $matchingOrderNumber,
    ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/Orders')
            ->where('filters.status', 'paid')
            ->where('filters.search', $matchingOrderNumber)
            ->where('orders.meta.total', 1)
            ->has('orders.data', 1)
            ->where('orders.data.0.id', $matchingOrder->id)
            ->where('orders.data.0.user.name', 'Nur Aina')
            ->where('orders.data.0.status', 'paid'));
});

test('user without order permissions cannot access admin orders index', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $user = User::factory()->create();
    $user->assignRole(Role::findByName('user', 'web'));

    $this->actingAs($user)
        ->get(route('admin.orders.index'))
        ->assertForbidden();
});

test('admin can view order detail in admin', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $customer = User::factory()->create([
        'name' => 'Adib Hassan',
        'email' => 'adib@example.test',
    ]);

    $order = createAdminOrderFixture($customer, 'pending');

    $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/OrderDetail')
            ->where('order.id', $order->id)
            ->where('order.number', sprintf('TM-%06d', $order->id))
            ->where('order.user.email', 'adib@example.test')
            ->has('order.items', 1));
});

test('admin can advance order status and timestamps are recorded', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $customer = User::factory()->create();
    $order = createAdminOrderFixture($customer, 'pending');

    Carbon::setTestNow('2026-03-12 10:00:00');
    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'paid',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('paid');
    expect($order->paid_at?->toDateTimeString())->toBe('2026-03-12 10:00:00');

    Carbon::setTestNow('2026-03-12 14:30:00');
    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'shipped',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('shipped');
    expect($order->shipped_at?->toDateTimeString())->toBe('2026-03-12 14:30:00');

    Carbon::setTestNow('2026-03-13 09:15:00');
    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'delivered',
            'notes' => 'Handed to customer.',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('delivered');
    expect($order->delivered_at?->toDateTimeString())->toBe('2026-03-13 09:15:00');
    expect($order->notes)->toBe('Handed to customer.');

    Carbon::setTestNow();
});

test('invalid order status transition is rejected', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $customer = User::factory()->create();
    $order = createAdminOrderFixture($customer, 'delivered');

    $this->from(route('admin.orders.show', $order))
        ->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'pending',
        ])
        ->assertSessionHasErrors('status')
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe('delivered');
});

function createAdminOrderFixture(User $customer, string $status): Order
{
    $product = Product::query()->create([
        'name' => 'Heritage Kurung',
        'slug' => 'heritage-kurung-'.$customer->id.'-'.$status,
        'category' => 'malay',
        'description' => 'Woven for ceremonial occasions.',
        'price_in_sen' => 18900,
        'original_price_in_sen' => 21900,
        'badge' => 'Signature',
        'gradient' => 'from-amber-100 to-orange-100',
        'image_url' => null,
        'is_active' => true,
    ]);

    $timestamps = [
        'paid' => ['paid_at' => now()],
        'shipped' => ['paid_at' => now()->subDay(), 'shipped_at' => now()],
        'delivered' => [
            'paid_at' => now()->subDays(2),
            'shipped_at' => now()->subDay(),
            'delivered_at' => now(),
        ],
    ];

    /** @var Order $order */
    $order = Order::query()->create(array_merge([
        'user_id' => $customer->id,
        'status' => $status,
        'subtotal_in_sen' => 18900,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 0,
        'total_in_sen' => 18900,
        'shipping_name' => $customer->name,
        'shipping_address' => '45 Jalan Warisan',
        'shipping_city' => 'Melaka',
        'shipping_state' => 'Melaka',
        'shipping_postcode' => '75000',
        'shipping_phone' => '0123456789',
        'coupon_code' => null,
        'notes' => 'Leave at the front desk.',
    ], $timestamps[$status] ?? []));

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price_in_sen' => $product->price_in_sen,
        'quantity' => 1,
        'subtotal_in_sen' => $product->price_in_sen,
    ]);

    return $order->load(['user', 'items.product']);
}

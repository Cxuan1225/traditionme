<?php

declare(strict_types=1);

use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Database\Seeders\SecurityRbacSeeder;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

test('order confirmation mail has correct subject and content', function (): void {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'subtotal_in_sen' => 19800,
        'discount_in_sen' => 1980,
        'shipping_in_sen' => 0,
        'total_in_sen' => 17820,
        'shipping_name' => 'Test User',
        'shipping_address' => '123 Heritage Lane',
        'shipping_city' => 'Kuala Lumpur',
        'shipping_state' => 'Wilayah Persekutuan',
        'shipping_postcode' => '50000',
        'shipping_phone' => '0123456789',
    ]);

    $order->items()->create([
        'product_id' => Product::query()->create([
            'name' => 'Batik Shirt',
            'slug' => 'batik-shirt',
            'category' => 'malay',
            'price_in_sen' => 9900,
            'is_active' => true,
        ])->id,
        'product_name' => 'Batik Shirt',
        'unit_price_in_sen' => 9900,
        'quantity' => 2,
        'subtotal_in_sen' => 19800,
    ]);

    $order->load('items');

    $mailable = new OrderConfirmationMail($order);

    $mailable->assertHasSubject(sprintf('Your Tradition Me Order TM-%06d is Confirmed', $order->id));
    $mailable->assertSeeInHtml('Batik Shirt');
    $mailable->assertSeeInHtml('RM 198.00');
    $mailable->assertSeeInHtml('View Your Order');
    $mailable->assertSeeInHtml('123 Heritage Lane');
});

test('order status updated mail has correct subject and status message', function (): void {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'shipped',
        'subtotal_in_sen' => 9900,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 1200,
        'total_in_sen' => 11100,
        'shipping_name' => 'Test User',
        'shipping_address' => '456 Culture Road',
        'shipping_city' => 'Penang',
        'shipping_state' => 'Pulau Pinang',
        'shipping_postcode' => '10000',
        'shipping_phone' => '0198765432',
    ]);

    $mailable = new OrderStatusUpdatedMail($order, 'shipped');

    $mailable->assertHasSubject(sprintf('Your Order TM-%06d is now Shipped', $order->id));
    $mailable->assertSeeInHtml('Your order is on its way!');
    $mailable->assertSeeInHtml('View Your Order');
});

test('order placed notification is sent after checkout', function (): void {
    Notification::fake();

    $product = Product::query()->create([
        'name' => 'Heritage Scarf',
        'slug' => 'heritage-scarf',
        'category' => 'malay',
        'price_in_sen' => 4900,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    session(['cart.items' => [$product->slug => 1]]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Heritage Lane',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Wilayah Persekutuan',
            'shipping_postcode' => '50000',
            'shipping_phone' => '0123456789',
        ]);

    Notification::assertSentTo($user, OrderPlacedNotification::class);
});

test('order status changed notification is sent on status update', function (): void {
    Notification::fake();

    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $customer = User::factory()->create();

    $product = Product::query()->create([
        'name' => 'Songket Cloth',
        'slug' => 'songket-cloth',
        'category' => 'malay',
        'price_in_sen' => 29900,
        'is_active' => true,
    ]);

    session(['cart.items' => [$product->slug => 1]]);

    $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Heritage Lane',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'Wilayah Persekutuan',
            'shipping_postcode' => '50000',
            'shipping_phone' => '0123456789',
        ]);

    $order = Order::query()->latest()->first();

    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'paid',
        ]);

    Notification::assertSentTo($customer, OrderStatusChangedNotification::class, function ($notification) {
        return $notification->newStatus === 'paid';
    });
});

test('order status updated mail shows correct message for each status', function (string $status, string $expectedMessage): void {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'status' => $status,
        'subtotal_in_sen' => 9900,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 0,
        'total_in_sen' => 9900,
        'shipping_name' => 'Test',
        'shipping_address' => '123 St',
        'shipping_city' => 'KL',
        'shipping_state' => 'WP',
        'shipping_postcode' => '50000',
        'shipping_phone' => '012345',
    ]);

    $mailable = new OrderStatusUpdatedMail($order, $status);

    $mailable->assertSeeInHtml($expectedMessage);
})->with([
    'paid' => ['paid', 'Payment confirmed!'],
    'shipped' => ['shipped', 'Your order is on its way!'],
    'delivered' => ['delivered', 'Your order has been delivered.'],
    'cancelled' => ['cancelled', 'Your order has been cancelled.'],
]);

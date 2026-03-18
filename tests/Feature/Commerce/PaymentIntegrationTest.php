<?php

declare(strict_types=1);

use App\Contracts\PaymentGateway;
use App\DTOs\Commerce\PaymentSession;
use App\DTOs\Commerce\PaymentVerification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

function createTestOrder(User $user, string $status = 'pending'): Order
{
    return Order::create([
        'user_id' => $user->id,
        'status' => $status,
        'subtotal_in_sen' => 9900,
        'discount_in_sen' => 0,
        'shipping_in_sen' => 1200,
        'total_in_sen' => 11100,
        'shipping_name' => 'Test User',
        'shipping_address' => '123 Heritage Lane',
        'shipping_city' => 'Kuala Lumpur',
        'shipping_state' => 'Wilayah Persekutuan',
        'shipping_postcode' => '50000',
        'shipping_phone' => '0123456789',
    ]);
}

test('initiate payment redirects to stripe checkout url', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user);

    $mockGateway = Mockery::mock(PaymentGateway::class);
    $mockGateway->shouldReceive('createCheckoutSession')
        ->once()
        ->andReturn(new PaymentSession(
            sessionId: 'cs_test_123',
            checkoutUrl: 'https://checkout.stripe.com/test',
        ));

    $this->app->instance(PaymentGateway::class, $mockGateway);

    $this->actingAs($user)
        ->post(route('orders.pay', $order))
        ->assertRedirect('https://checkout.stripe.com/test');

    $order->refresh();
    expect($order->payment_session_id)->toBe('cs_test_123');
    expect($order->payment_method)->toBe('stripe');
});

test('initiate payment rejects non-owner', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = createTestOrder($owner);

    $this->actingAs($other)
        ->post(route('orders.pay', $order))
        ->assertForbidden();
});

test('initiate payment rejects already paid order', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user, 'paid');

    $this->actingAs($user)
        ->post(route('orders.pay', $order))
        ->assertRedirect(route('account.orders.show', $order));
});

test('payment success page renders with verified order', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user);
    $order->update(['payment_session_id' => 'cs_test_456']);
    $order->items()->create([
        'product_id' => Product::query()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'category' => 'malay',
            'price_in_sen' => 9900,
            'is_active' => true,
        ])->id,
        'product_name' => 'Test Product',
        'unit_price_in_sen' => 9900,
        'quantity' => 1,
        'subtotal_in_sen' => 9900,
    ]);

    $mockGateway = Mockery::mock(PaymentGateway::class);
    $mockGateway->shouldReceive('verifyPayment')
        ->with('cs_test_456')
        ->once()
        ->andReturn(new PaymentVerification(
            success: true,
            transactionId: 'pi_test_789',
            amountInSen: 11100,
        ));

    $this->app->instance(PaymentGateway::class, $mockGateway);

    $this->actingAs($user)
        ->get(route('payment.success', ['session_id' => 'cs_test_456']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('PaymentSuccess')
            ->has('order')
            ->where('verified', true),
        );
});

test('payment cancel page renders with order', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user);
    $order->items()->create([
        'product_id' => Product::query()->create([
            'name' => 'Cancel Product',
            'slug' => 'cancel-product',
            'category' => 'malay',
            'price_in_sen' => 9900,
            'is_active' => true,
        ])->id,
        'product_name' => 'Cancel Product',
        'unit_price_in_sen' => 9900,
        'quantity' => 1,
        'subtotal_in_sen' => 9900,
    ]);

    $this->actingAs($user)
        ->get(route('payment.cancel', ['order_id' => $order->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('PaymentCancel')
            ->has('order'),
        );
});

test('webhook endpoint returns 200', function (): void {
    $mockGateway = Mockery::mock(PaymentGateway::class);
    $mockGateway->shouldReceive('handleWebhook')->once();

    $this->app->instance(PaymentGateway::class, $mockGateway);

    $this->post(route('webhooks.payment'), [], [
        'Stripe-Signature' => 'test_sig',
    ])->assertOk();
});

test('checkout redirects to payment initiation', function (): void {
    Notification::fake();

    $product = Product::query()->create([
        'name' => 'Checkout Product',
        'slug' => 'checkout-product',
        'category' => 'malay',
        'price_in_sen' => 9900,
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
        ])
        ->assertRedirect();

    $order = Order::query()->latest()->first();
    expect($order)->not->toBeNull();
    expect(route('orders.pay', $order))->toContain('/orders/'.$order->id.'/pay');
});

test('order model includes payment fields in fillable', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user);

    $order->update([
        'payment_method' => 'stripe',
        'payment_session_id' => 'cs_test_fillable',
        'payment_transaction_id' => 'pi_test_fillable',
    ]);

    $order->refresh();

    expect($order->payment_method)->toBe('stripe');
    expect($order->payment_session_id)->toBe('cs_test_fillable');
    expect($order->payment_transaction_id)->toBe('pi_test_fillable');
});

test('order resource includes payment fields', function (): void {
    $user = User::factory()->create();
    $order = createTestOrder($user);
    $order->update([
        'payment_method' => 'stripe',
        'payment_transaction_id' => 'pi_test_resource',
    ]);
    $order->items()->create([
        'product_id' => Product::query()->create([
            'name' => 'Resource Product',
            'slug' => 'resource-product',
            'category' => 'malay',
            'price_in_sen' => 9900,
            'is_active' => true,
        ])->id,
        'product_name' => 'Resource Product',
        'unit_price_in_sen' => 9900,
        'quantity' => 1,
        'subtotal_in_sen' => 9900,
    ]);

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('order.paymentMethod', 'stripe')
            ->where('order.paymentTransactionId', 'pi_test_resource'),
        );
});

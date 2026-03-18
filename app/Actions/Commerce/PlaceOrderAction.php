<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\DTOs\Commerce\PlaceOrderData;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use RuntimeException;

readonly class PlaceOrderAction
{
    public function __construct(
        private GetCartAction $getCartAction,
        private ValidateCouponAction $validateCouponAction,
    ) {}

    public function __invoke(Session $session, User $user, PlaceOrderData $data): Order
    {
        $lines = ($this->getCartAction)($session);

        if ($lines === []) {
            throw new RuntimeException('Your cart is empty.');
        }

        $subtotalInSen = array_reduce(
            $lines,
            static fn (int $sum, array $line): int => $sum + ($line['product']->price_in_sen * $line['quantity']),
            0,
        );

        $couponResult = ($this->validateCouponAction)($data->couponCode);
        $discountInSen = $couponResult['valid']
            ? (int) round($subtotalInSen * ($couponResult['discountPercent'] / 100))
            : 0;
        $shippingInSen = $subtotalInSen - $discountInSen >= 20000 ? 0 : 1200;
        $totalInSen = $subtotalInSen - $discountInSen + $shippingInSen;

        /** @var Order $order */
        $order = DB::transaction(function () use (
            $data,
            $discountInSen,
            $lines,
            $shippingInSen,
            $subtotalInSen,
            $totalInSen,
            $user,
            $couponResult,
        ): Order {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal_in_sen' => $subtotalInSen,
                'discount_in_sen' => $discountInSen,
                'shipping_in_sen' => $shippingInSen,
                'total_in_sen' => $totalInSen,
                'shipping_name' => $data->shippingName,
                'shipping_address' => $data->shippingAddress,
                'shipping_city' => $data->shippingCity,
                'shipping_state' => $data->shippingState,
                'shipping_postcode' => $data->shippingPostcode,
                'shipping_phone' => $data->shippingPhone,
                'coupon_code' => $couponResult['valid'] ? strtoupper((string) $data->couponCode) : null,
                'notes' => $data->notes,
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $quantity = $line['quantity'];

                if ($product->track_stock) {
                    $fresh = Product::query()->lockForUpdate()->find($product->id);

                    if ($fresh === null || ! $fresh->hasStockFor($quantity)) {
                        throw new RuntimeException(
                            sprintf('Insufficient stock for "%s". Only %d available.', $product->name, $fresh?->stock_quantity ?? 0),
                        );
                    }

                    $fresh->decrement('stock_quantity', $quantity);
                }
            }

            $order->items()->createMany(array_map(
                /**
                 * @param  array{product: Product, quantity: int}  $line
                 * @return array<string, int|string>
                 */
                static fn (array $line): array => [
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price_in_sen' => $line['product']->price_in_sen,
                    'quantity' => $line['quantity'],
                    'subtotal_in_sen' => $line['product']->price_in_sen * $line['quantity'],
                ],
                $lines,
            ));

            return $order;
        });

        $session->forget('cart.items');

        $order->load(['items.product']);

        $user->notify(new OrderPlacedNotification($order));

        return $order;
    }
}

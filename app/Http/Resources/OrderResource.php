<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemCount = (int) $this->items->sum('quantity');

        return [
            'id' => $this->id,
            'number' => sprintf('TM-%06d', $this->id),
            'status' => $this->status,
            'statusLabel' => ucfirst($this->status),
            'couponCode' => $this->coupon_code,
            'notes' => $this->notes,
            'placedAt' => $this->created_at?->toIso8601String(),
            'summary' => [
                'subtotalInSen' => $this->subtotal_in_sen,
                'discountInSen' => $this->discount_in_sen,
                'shippingInSen' => $this->shipping_in_sen,
                'totalInSen' => $this->total_in_sen,
                'itemCount' => $itemCount,
                'subtotal' => $this->formatMoney($this->subtotal_in_sen),
                'discount' => $this->formatMoney($this->discount_in_sen),
                'shipping' => $this->shipping_in_sen === 0
                    ? 'Free'
                    : $this->formatMoney($this->shipping_in_sen),
                'total' => $this->formatMoney($this->total_in_sen),
            ],
            'shipping' => [
                'name' => $this->shipping_name,
                'address' => $this->shipping_address,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'postcode' => $this->shipping_postcode,
                'phone' => $this->shipping_phone,
            ],
            'items' => OrderItemResource::collection($this->items)->resolve($request),
        ];
    }

    private function formatMoney(int $amountInSen): string
    {
        return 'RM '.number_format($amountInSen / 100, 2);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderItem */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'productSlug' => $this->product?->slug,
            'productName' => $this->product_name,
            'unitPriceInSen' => $this->unit_price_in_sen,
            'quantity' => $this->quantity,
            'subtotalInSen' => $this->subtotal_in_sen,
            'imageUrl' => $this->product?->image_url,
            'gradient' => $this->product?->gradient,
            'category' => $this->product?->category,
        ];
    }
}

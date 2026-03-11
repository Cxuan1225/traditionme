<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ShopProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'priceInSen' => $this->price_in_sen,
            'originalPriceInSen' => $this->original_price_in_sen,
            'badge' => $this->badge,
            'gradient' => $this->gradient,
            'imageUrl' => $this->image_url,
        ];
    }
}

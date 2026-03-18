<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
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
            'description' => $this->description,
            'priceInSen' => $this->price_in_sen,
            'originalPriceInSen' => $this->original_price_in_sen,
            'badge' => $this->badge,
            'gradient' => $this->gradient,
            'imageUrl' => $this->image_url,
            'inStock' => $this->isInStock(),
            'stockQuantity' => $this->track_stock ? $this->stock_quantity : null,
        ];
    }
}

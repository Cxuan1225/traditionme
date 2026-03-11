<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     category: string,
     *     price_in_sen: int,
     *     original_price_in_sen: int|null,
     *     badge: string|null,
     *     gradient: string|null,
     *     image_url: string|null,
     *     is_active: bool
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'price_in_sen' => $this->price_in_sen,
            'original_price_in_sen' => $this->original_price_in_sen,
            'badge' => $this->badge,
            'gradient' => $this->gradient,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
        ];
    }
}

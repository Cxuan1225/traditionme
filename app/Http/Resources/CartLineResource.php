<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{product: Product, quantity: int} $line */
        $line = $this->resource;

        return [
            'id' => $line['product']->id,
            'slug' => $line['product']->slug,
            'name' => $line['product']->name,
            'category' => $line['product']->category,
            'unitPriceInSen' => $line['product']->price_in_sen,
            'quantity' => $line['quantity'],
            'imageUrl' => $line['product']->image_url,
            'gradient' => $line['product']->gradient,
        ];
    }
}

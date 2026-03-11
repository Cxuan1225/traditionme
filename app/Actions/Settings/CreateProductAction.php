<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DTOs\Settings\ProductPayloadData;
use App\Models\Product;

class CreateProductAction
{
    public function __invoke(ProductPayloadData $data): Product
    {
        return Product::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'category' => $data->category,
            'price_in_sen' => $data->priceInSen,
            'original_price_in_sen' => $data->originalPriceInSen,
            'badge' => $data->badge,
            'gradient' => $data->gradient,
            'image_url' => $data->imageUrl,
            'is_active' => $data->isActive,
        ]);
    }
}

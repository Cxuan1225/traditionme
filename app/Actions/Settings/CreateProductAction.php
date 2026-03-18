<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Actions\Settings\Concerns\ManagesProductImages;
use App\DTOs\Settings\ProductPayloadData;
use App\Models\Product;
use Illuminate\Http\UploadedFile;

class CreateProductAction
{
    use ManagesProductImages;

    public function __invoke(ProductPayloadData $data): Product
    {
        $imageUrl = $data->imageFile instanceof UploadedFile
            ? $this->storeUploadedImage($data->imageFile)
            : $data->imageUrl;

        return Product::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'category' => $data->category->value,
            'description' => $data->description,
            'price_in_sen' => $data->priceInSen,
            'original_price_in_sen' => $data->originalPriceInSen,
            'badge' => $data->badge,
            'gradient' => $data->gradient,
            'image_url' => $imageUrl,
            'is_active' => $data->isActive,
            'stock_quantity' => $data->stockQuantity,
            'track_stock' => $data->trackStock,
        ]);
    }
}

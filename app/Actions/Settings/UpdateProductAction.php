<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Actions\Settings\Concerns\ManagesProductImages;
use App\DTOs\Settings\ProductPayloadData;
use App\Models\Product;

class UpdateProductAction
{
    use ManagesProductImages;

    public function __invoke(Product $product, ProductPayloadData $data): Product
    {
        $nextImageUrl = $data->imageFile instanceof \Illuminate\Http\UploadedFile
            ? $this->storeUploadedImage($data->imageFile)
            : ($data->hasImageUrlInput ? $data->imageUrl : $product->image_url);

        if ($product->image_url !== $nextImageUrl) {
            $this->deleteManagedImage($product->image_url);
        }

        $product->fill([
            'name' => $data->name,
            'slug' => $data->slug,
            'category' => $data->category,
            'price_in_sen' => $data->priceInSen,
            'original_price_in_sen' => $data->originalPriceInSen,
            'badge' => $data->badge,
            'gradient' => $data->gradient,
            'image_url' => $nextImageUrl,
            'is_active' => $data->isActive,
        ]);

        $product->save();

        return $product->refresh();
    }
}

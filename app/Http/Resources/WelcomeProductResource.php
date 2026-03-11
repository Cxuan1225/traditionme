<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Home\WelcomeProductData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WelcomeProductData */
class WelcomeProductResource extends JsonResource
{
    /**
     * @return array{
     *     name: string,
     *     slug: string,
     *     category: string,
     *     price: string,
     *     originalPrice: string,
     *     badge: string,
     *     gradient: string,
     *     imageUrl: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        /** @var WelcomeProductData $product */
        $product = $this->resource;

        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category,
            'price' => $this->formatRinggit($product->priceInSen),
            'originalPrice' => $this->formatRinggit($product->originalPriceInSen),
            'badge' => $product->badge,
            'gradient' => $product->gradient,
            'imageUrl' => $product->imageUrl,
        ];
    }

    private function formatRinggit(int $amountInSen): string
    {
        return sprintf('RM %.0f', $amountInSen / 100);
    }
}

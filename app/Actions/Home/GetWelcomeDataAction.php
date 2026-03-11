<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\DTOs\Home\WelcomeCategoryData;
use App\DTOs\Home\WelcomeOccasionData;
use App\DTOs\Home\WelcomePageData;
use App\DTOs\Home\WelcomeProductData;
use App\DTOs\Home\WelcomeReviewData;
use App\Models\Product;
use App\Support\WelcomeCatalog;

class GetWelcomeDataAction
{
    public function __invoke(): WelcomePageData
    {
        $dbProducts = Product::where('is_active', true)->latest()->take(4)->get();
        /** @var list<WelcomeProductData> $products */
        $products = array_values($dbProducts->map(
            static fn (Product $product): WelcomeProductData => new WelcomeProductData(
                name: $product->name,
                slug: $product->slug,
                category: $product->category,
                priceInSen: $product->price_in_sen,
                originalPriceInSen: $product->original_price_in_sen ?? $product->price_in_sen,
                badge: $product->badge ?? '',
                gradient: $product->gradient ?? '',
                imageUrl: $product->image_url,
            ),
        )->all());

        return new WelcomePageData(
            categories: array_map(
                static fn (array $category): WelcomeCategoryData => new WelcomeCategoryData(
                    name: $category['name'],
                    slug: $category['slug'],
                ),
                WelcomeCatalog::categories(),
            ),
            products: $products,
            occasions: array_map(
                static fn (array $occasion): WelcomeOccasionData => new WelcomeOccasionData(
                    name: $occasion['name'],
                    slug: $occasion['slug'],
                    description: $occasion['description'],
                    badge: $occasion['badge'],
                ),
                WelcomeCatalog::occasions(),
            ),
            reviews: array_map(
                static fn (array $review): WelcomeReviewData => new WelcomeReviewData(
                    name: $review['name'],
                    location: $review['location'],
                    comment: $review['comment'],
                ),
                WelcomeCatalog::reviews(),
            ),
            totalProducts: Product::where('is_active', true)->count(),
        );
    }
}

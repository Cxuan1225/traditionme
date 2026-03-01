<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Home\WelcomePageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WelcomePageData */
class WelcomePageResource extends JsonResource
{
    /**
     * @return array{
     *     categories: array<int, array{name: string, slug: string}>,
     *     products: array<int, array{
     *         name: string,
     *         slug: string,
     *         category: string,
     *         price: string,
     *         originalPrice: string,
     *         badge: string,
     *         gradient: string
     *     }>,
     *     occasions: array<int, array{name: string, slug: string, description: string, badge: string}>,
     *     reviews: array<int, array{name: string, location: string, comment: string}>,
     *     totalProducts: int
     * }
     */
    public function toArray(Request $request): array
    {
        /** @var WelcomePageData $welcome */
        $welcome = $this->resource;

        return [
            'categories' => WelcomeCategoryResource::collection($welcome->categories)->resolve($request),
            'products' => WelcomeProductResource::collection($welcome->products)->resolve($request),
            'occasions' => WelcomeOccasionResource::collection($welcome->occasions)->resolve($request),
            'reviews' => WelcomeReviewResource::collection($welcome->reviews)->resolve($request),
            'totalProducts' => $welcome->totalProducts,
        ];
    }
}

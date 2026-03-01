<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Home\WelcomeReviewData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WelcomeReviewData */
class WelcomeReviewResource extends JsonResource
{
    /**
     * @return array{name: string, location: string, comment: string}
     */
    public function toArray(Request $request): array
    {
        /** @var WelcomeReviewData $review */
        $review = $this->resource;

        return [
            'name' => $review->name,
            'location' => $review->location,
            'comment' => $review->comment,
        ];
    }
}

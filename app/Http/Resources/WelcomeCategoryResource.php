<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Home\WelcomeCategoryData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WelcomeCategoryData */
class WelcomeCategoryResource extends JsonResource
{
    /**
     * @return array{name: string, slug: string}
     */
    public function toArray(Request $request): array
    {
        /** @var WelcomeCategoryData $category */
        $category = $this->resource;

        return [
            'name' => $category->name,
            'slug' => $category->slug,
        ];
    }
}

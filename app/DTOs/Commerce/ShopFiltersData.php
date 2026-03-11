<?php

declare(strict_types=1);

namespace App\DTOs\Commerce;

use Illuminate\Http\Request;

readonly class ShopFiltersData
{
    public function __construct(
        public ?string $category,
        public ?string $search,
        public string $sort = 'featured',
        public int $perPage = 12,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            category: $request->string('category')->toString() ?: null,
            search: $request->string('search')->toString() ?: null,
            sort: $request->string('sort')->toString() ?: 'featured',
            perPage: 12,
        );
    }
}

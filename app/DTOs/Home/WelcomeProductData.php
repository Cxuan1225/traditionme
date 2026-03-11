<?php

declare(strict_types=1);

namespace App\DTOs\Home;

readonly class WelcomeProductData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $category,
        public int $priceInSen,
        public int $originalPriceInSen,
        public string $badge,
        public string $gradient,
        public ?string $imageUrl,
    ) {}
}

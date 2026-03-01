<?php

declare(strict_types=1);

namespace App\DTOs\Home;

readonly class WelcomeOccasionData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
        public string $badge,
    ) {}
}

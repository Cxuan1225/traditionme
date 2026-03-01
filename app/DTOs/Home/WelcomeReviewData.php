<?php

declare(strict_types=1);

namespace App\DTOs\Home;

readonly class WelcomeReviewData
{
    public function __construct(
        public string $name,
        public string $location,
        public string $comment,
    ) {}
}

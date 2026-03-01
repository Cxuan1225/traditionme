<?php

declare(strict_types=1);

namespace App\DTOs\Home;

readonly class WelcomePageData
{
    /**
     * @param  list<WelcomeCategoryData>  $categories
     * @param  list<WelcomeProductData>  $products
     * @param  list<WelcomeOccasionData>  $occasions
     * @param  list<WelcomeReviewData>  $reviews
     */
    public function __construct(
        public array $categories,
        public array $products,
        public array $occasions,
        public array $reviews,
        public int $totalProducts,
    ) {}
}

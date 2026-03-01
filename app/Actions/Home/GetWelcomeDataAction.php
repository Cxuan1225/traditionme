<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\DTOs\Home\WelcomeCategoryData;
use App\DTOs\Home\WelcomeOccasionData;
use App\DTOs\Home\WelcomePageData;
use App\DTOs\Home\WelcomeProductData;
use App\DTOs\Home\WelcomeReviewData;

class GetWelcomeDataAction
{
    public function __invoke(): WelcomePageData
    {
        return new WelcomePageData(
            categories: [
                new WelcomeCategoryData(name: 'Women', slug: 'women'),
                new WelcomeCategoryData(name: 'Men', slug: 'men'),
                new WelcomeCategoryData(name: 'Kids', slug: 'kids'),
                new WelcomeCategoryData(name: 'Accessories', slug: 'accessories'),
                new WelcomeCategoryData(name: 'Festive Sets', slug: 'festive-sets'),
                new WelcomeCategoryData(name: 'New Arrivals', slug: 'new-arrivals'),
            ],
            products: [
                new WelcomeProductData(
                    name: 'Songket Luxe Kurung Set',
                    slug: 'songket-luxe-kurung-set',
                    category: 'Women',
                    priceInSen: 28900,
                    originalPriceInSen: 35900,
                    badge: 'Best Seller',
                    gradient: 'from-rose-100 via-orange-50 to-amber-100',
                ),
                new WelcomeProductData(
                    name: 'Cekak Musang Premium',
                    slug: 'cekak-musang-premium',
                    category: 'Men',
                    priceInSen: 22900,
                    originalPriceInSen: 29900,
                    badge: 'Limited',
                    gradient: 'from-emerald-100 via-teal-50 to-cyan-100',
                ),
                new WelcomeProductData(
                    name: 'Nyonya Kebaya Bloom',
                    slug: 'nyonya-kebaya-bloom',
                    category: 'Women',
                    priceInSen: 25900,
                    originalPriceInSen: 31900,
                    badge: 'New',
                    gradient: 'from-fuchsia-100 via-pink-50 to-rose-100',
                ),
                new WelcomeProductData(
                    name: 'Kids Raya Twin Set',
                    slug: 'kids-raya-twin-set',
                    category: 'Kids',
                    priceInSen: 14900,
                    originalPriceInSen: 18900,
                    badge: 'Hot',
                    gradient: 'from-sky-100 via-blue-50 to-indigo-100',
                ),
            ],
            occasions: [
                new WelcomeOccasionData(
                    name: 'Weddings',
                    description: 'Elegant coordinated looks for bride, groom, and family members.',
                    badge: 'Most Booked',
                ),
                new WelcomeOccasionData(
                    name: 'Festive Gatherings',
                    description: 'Comfortable statement sets for open house and reunion events.',
                    badge: 'Seasonal',
                ),
                new WelcomeOccasionData(
                    name: 'Corporate Events',
                    description: 'Modern heritage styling suitable for formal cultural occasions.',
                    badge: 'Editor Pick',
                ),
            ],
            reviews: [
                new WelcomeReviewData(
                    name: 'Aina',
                    location: 'Shah Alam',
                    comment: 'Quality exceeded expectations and delivery arrived two days early.',
                ),
                new WelcomeReviewData(
                    name: 'Pravin',
                    location: 'Penang',
                    comment: 'Sizing guide was accurate and tailoring service saved us before a wedding.',
                ),
                new WelcomeReviewData(
                    name: 'Mei Lin',
                    location: 'Johor Bahru',
                    comment: 'The designs feel premium and the checkout process was very smooth.',
                ),
            ],
        );
    }
}

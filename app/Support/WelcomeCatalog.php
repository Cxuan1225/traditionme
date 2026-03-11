<?php

declare(strict_types=1);

namespace App\Support;

final class WelcomeCatalog
{
    /**
     * @return list<array{name: string, slug: string}>
     */
    public static function categories(): array
    {
        return [
            ['name' => 'Women', 'slug' => 'women'],
            ['name' => 'Men', 'slug' => 'men'],
            ['name' => 'Kids', 'slug' => 'kids'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
            ['name' => 'Festive Sets', 'slug' => 'festive-sets'],
            ['name' => 'New Arrivals', 'slug' => 'new-arrivals'],
        ];
    }

    /**
     * @return list<array{
     *     name: string,
     *     slug: string,
     *     category: string,
     *     priceInSen: int,
     *     originalPriceInSen: int,
     *     badge: string,
     *     gradient: string,
     *     imageUrl: string
     * }>
     */
    public static function products(): array
    {
        return [
            [
                'name' => 'Songket Luxe Kurung Set',
                'slug' => 'songket-luxe-kurung-set',
                'category' => 'Women',
                'priceInSen' => 28900,
                'originalPriceInSen' => 35900,
                'badge' => 'Best Seller',
                'gradient' => 'from-rose-100 via-orange-50 to-amber-100',
                'imageUrl' => 'https://images.unsplash.com/photo-1551232864-3f0890e580d9?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'name' => 'Cekak Musang Premium',
                'slug' => 'cekak-musang-premium',
                'category' => 'Men',
                'priceInSen' => 22900,
                'originalPriceInSen' => 29900,
                'badge' => 'Limited',
                'gradient' => 'from-emerald-100 via-teal-50 to-cyan-100',
                'imageUrl' => 'https://images.unsplash.com/photo-1618886614638-80e3c103d31a?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'name' => 'Nyonya Kebaya Bloom',
                'slug' => 'nyonya-kebaya-bloom',
                'category' => 'Women',
                'priceInSen' => 25900,
                'originalPriceInSen' => 31900,
                'badge' => 'New',
                'gradient' => 'from-fuchsia-100 via-pink-50 to-rose-100',
                'imageUrl' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'name' => 'Kids Raya Twin Set',
                'slug' => 'kids-raya-twin-set',
                'category' => 'Kids',
                'priceInSen' => 14900,
                'originalPriceInSen' => 18900,
                'badge' => 'Hot',
                'gradient' => 'from-sky-100 via-blue-50 to-indigo-100',
                'imageUrl' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=900&q=80',
            ],
        ];
    }

    /**
     * @return list<array{name: string, slug: string, description: string, badge: string}>
     */
    public static function occasions(): array
    {
        return [
            [
                'name' => 'Weddings',
                'slug' => 'weddings',
                'description' => 'Elegant coordinated looks for bride, groom, and family members.',
                'badge' => 'Most Booked',
            ],
            [
                'name' => 'Festive Gatherings',
                'slug' => 'festive-gatherings',
                'description' => 'Comfortable statement sets for open house and reunion events.',
                'badge' => 'Seasonal',
            ],
            [
                'name' => 'Corporate Events',
                'slug' => 'corporate-events',
                'description' => 'Modern heritage styling suitable for formal cultural occasions.',
                'badge' => 'Editor Pick',
            ],
        ];
    }

    /**
     * @return list<array{name: string, location: string, comment: string}>
     */
    public static function reviews(): array
    {
        return [
            [
                'name' => 'Aina',
                'location' => 'Shah Alam',
                'comment' => 'Quality exceeded expectations and delivery arrived two days early.',
            ],
            [
                'name' => 'Pravin',
                'location' => 'Penang',
                'comment' => 'Sizing guide was accurate and tailoring service saved us before a wedding.',
            ],
            [
                'name' => 'Mei Lin',
                'location' => 'Johor Bahru',
                'comment' => 'The designs feel premium and the checkout process was very smooth.',
            ],
        ];
    }

    public static function totalProducts(): int
    {
        return 120;
    }

    /**
     * @return list<string>
     */
    public static function productSlugs(): array
    {
        return array_map(
            static fn (array $product): string => $product['slug'],
            self::products(),
        );
    }
}

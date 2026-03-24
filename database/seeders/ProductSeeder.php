<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * @var list<array<string, mixed>>
     */
    private const PRODUCTS = [
        [
            'name' => 'Batik Sarawak Shirt',
            'slug' => 'batik-sarawak-shirt',
            'category' => 'traditional-wear',
            'description' => 'Handcrafted Sarawak batik shirt featuring traditional Pua Kumbu motifs. Made from premium cotton for everyday comfort.',
            'price_in_sen' => 12900,
            'original_price_in_sen' => null,
            'badge' => 'New',
            'gradient' => 'from-amber-500 to-orange-600',
            'is_active' => true,
            'stock_quantity' => 50,
            'track_stock' => true,
        ],
        [
            'name' => 'Songket Tenun Shawl',
            'slug' => 'songket-tenun-shawl',
            'category' => 'traditional-wear',
            'description' => 'Elegant hand-woven songket shawl from Terengganu. Gold thread detailing with intricate floral patterns.',
            'price_in_sen' => 25900,
            'original_price_in_sen' => 32900,
            'badge' => 'Sale',
            'gradient' => 'from-yellow-500 to-amber-600',
            'is_active' => true,
            'stock_quantity' => 25,
            'track_stock' => true,
        ],
        [
            'name' => 'Pewter Hibiscus Brooch',
            'slug' => 'pewter-hibiscus-brooch',
            'category' => 'accessories',
            'description' => 'Royal Selangor inspired pewter brooch featuring the national flower, Bunga Raya. Perfect for formal occasions.',
            'price_in_sen' => 8900,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => 'from-slate-400 to-zinc-600',
            'is_active' => true,
            'stock_quantity' => 100,
            'track_stock' => true,
        ],
        [
            'name' => 'Wau Bulan Kite',
            'slug' => 'wau-bulan-kite',
            'category' => 'home-decor',
            'description' => 'Decorative moon kite (Wau Bulan) from Kelantan. Hand-painted with traditional motifs, perfect as wall art.',
            'price_in_sen' => 15900,
            'original_price_in_sen' => null,
            'badge' => 'Popular',
            'gradient' => 'from-blue-500 to-indigo-600',
            'is_active' => true,
            'stock_quantity' => 30,
            'track_stock' => true,
        ],
        [
            'name' => 'Sambal Belacan Set',
            'slug' => 'sambal-belacan-set',
            'category' => 'food',
            'description' => 'Premium artisan sambal belacan gift set. Includes three varieties: original, extra spicy, and sweet chili.',
            'price_in_sen' => 4500,
            'original_price_in_sen' => null,
            'badge' => 'Best Seller',
            'gradient' => 'from-red-500 to-rose-600',
            'is_active' => true,
            'stock_quantity' => 200,
            'track_stock' => true,
        ],
        [
            'name' => 'Rattan Basket Bag',
            'slug' => 'rattan-basket-bag',
            'category' => 'crafts',
            'description' => 'Handwoven rattan basket bag by Orang Asli artisans. Sturdy construction with leather strap closure.',
            'price_in_sen' => 18900,
            'original_price_in_sen' => 23900,
            'badge' => 'Sale',
            'gradient' => 'from-emerald-500 to-teal-600',
            'is_active' => true,
            'stock_quantity' => 15,
            'track_stock' => true,
        ],
        [
            'name' => 'Keris Letter Opener',
            'slug' => 'keris-letter-opener',
            'category' => 'crafts',
            'description' => 'Miniature keris-inspired letter opener with carved wooden handle. A functional piece of Malaysian heritage.',
            'price_in_sen' => 6900,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => 'from-stone-500 to-stone-700',
            'is_active' => true,
            'stock_quantity' => 75,
            'track_stock' => true,
        ],
        [
            'name' => 'Nyonya Kebaya Blouse',
            'slug' => 'nyonya-kebaya-blouse',
            'category' => 'traditional-wear',
            'description' => 'Peranakan-style kebaya blouse with delicate embroidery. Available in pastel colours inspired by Melaka heritage.',
            'price_in_sen' => 21900,
            'original_price_in_sen' => null,
            'badge' => 'New',
            'gradient' => 'from-pink-400 to-rose-500',
            'is_active' => true,
            'stock_quantity' => 40,
            'track_stock' => true,
        ],
        [
            'name' => 'Dodol Gift Box',
            'slug' => 'dodol-gift-box',
            'category' => 'food',
            'description' => 'Assorted dodol gift box with durian, coconut, and pandan flavours. Traditional recipe from Negeri Sembilan.',
            'price_in_sen' => 3500,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => 'from-lime-500 to-green-600',
            'is_active' => true,
            'stock_quantity' => 150,
            'track_stock' => true,
        ],
        [
            'name' => 'Labu Sayong Vase',
            'slug' => 'labu-sayong-vase',
            'category' => 'home-decor',
            'description' => 'Black clay water vessel from Kuala Kangsar, Perak. Naturally cools water and serves as elegant home decor.',
            'price_in_sen' => 9900,
            'original_price_in_sen' => 12900,
            'badge' => 'Sale',
            'gradient' => 'from-gray-600 to-gray-800',
            'is_active' => true,
            'stock_quantity' => 20,
            'track_stock' => true,
        ],
        [
            'name' => 'Pandan Weave Clutch',
            'slug' => 'pandan-weave-clutch',
            'category' => 'accessories',
            'description' => 'Hand-woven pandan leaf clutch bag with modern geometric pattern. Eco-friendly and uniquely Malaysian.',
            'price_in_sen' => 7900,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => 'from-green-400 to-emerald-500',
            'is_active' => true,
            'stock_quantity' => 60,
            'track_stock' => true,
        ],
        [
            'name' => 'Congkak Board Game',
            'slug' => 'congkak-board-game',
            'category' => 'crafts',
            'description' => 'Traditional Malay congkak board game carved from solid wood. Includes marble playing pieces and carrying pouch.',
            'price_in_sen' => 11900,
            'original_price_in_sen' => null,
            'badge' => null,
            'gradient' => 'from-amber-600 to-yellow-700',
            'is_active' => true,
            'stock_quantity' => 35,
            'track_stock' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTS as $product) {
            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                $product,
            );
        }
    }
}

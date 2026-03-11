<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

use App\DTOs\Commerce\ShopFiltersData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetShopProductsAction
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function __invoke(ShopFiltersData $filters): LengthAwarePaginator
    {
        $query = Product::where('is_active', true);

        if ($filters->category !== null) {
            $query->where('category', $filters->category);
        }

        if ($filters->search !== null) {
            $search = $filters->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        match ($filters->sort) {
            'price-asc' => $query->orderBy('price_in_sen', 'asc'),
            'price-desc' => $query->orderBy('price_in_sen', 'desc'),
            'newest' => $query->latest(),
            default => $query->latest(),
        };

        return $query->paginate($filters->perPage)->withQueryString();
    }
}

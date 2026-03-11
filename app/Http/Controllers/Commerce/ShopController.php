<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\GetShopProductsAction;
use App\DTOs\Commerce\ShopFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request, GetShopProductsAction $action): Response
    {
        $filters = ShopFiltersData::fromRequest($request);
        $products = $action($filters);

        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->all();

        return Inertia::render('Shop', [
            'products' => ShopProductResource::collection($products),
            'filters' => [
                'category' => $filters->category ?? '',
                'search' => $filters->search ?? '',
                'sort' => $filters->sort,
            ],
            'categories' => $categories,
        ]);
    }
}

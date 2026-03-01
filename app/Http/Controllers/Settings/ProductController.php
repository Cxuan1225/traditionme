<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\CreateProductAction;
use App\Actions\Settings\DeleteProductAction;
use App\Actions\Settings\UpdateProductAction;
use App\DTOs\Settings\ProductPayloadData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProductStoreRequest;
use App\Http\Requests\Settings\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $products = Product::query()->latest()->get();

        if ($request->expectsJson()) {
            return ProductResource::collection($products);
        }

        return Inertia::render('admin/settings/Products', [
            'initialProducts' => ProductResource::collection($products)->resolve($request),
            'capabilities' => [
                'canViewProducts' => $request->user()?->can('products.view') ?? false,
                'canCreateProducts' => $request->user()?->can('products.create') ?? false,
                'canUpdateProducts' => $request->user()?->can('products.update') ?? false,
                'canDeleteProducts' => $request->user()?->can('products.delete') ?? false,
            ],
        ]);
    }

    public function store(ProductStoreRequest $request, CreateProductAction $action): ProductResource
    {
        $product = $action(ProductPayloadData::fromStoreRequest($request));

        return new ProductResource($product);
    }

    public function update(
        ProductUpdateRequest $request,
        Product $product,
        UpdateProductAction $action,
    ): ProductResource {
        $updatedProduct = $action($product, ProductPayloadData::fromUpdateRequest($request));

        return new ProductResource($updatedProduct);
    }

    public function destroy(Product $product, DeleteProductAction $action): \Illuminate\Http\JsonResponse
    {
        $action($product);

        return response()->json(['deleted' => true]);
    }
}

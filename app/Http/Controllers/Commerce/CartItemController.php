<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\AddCartItemAction;
use App\Actions\Commerce\RemoveCartItemAction;
use App\Actions\Commerce\UpdateCartItemAction;
use App\DTOs\Commerce\AddCartItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\AddCartItemRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function store(
        AddCartItemRequest $request,
        AddCartItemAction $action,
    ): RedirectResponse {
        $added = $action($request->session(), AddCartItemData::fromRequest($request));

        if (! $added) {
            return back()->with('status', 'Unable to add this product to cart.');
        }

        return back()->with('status', 'Product added to cart.');
    }

    public function update(
        Request $request,
        Product $product,
        UpdateCartItemAction $action,
    ): RedirectResponse {
        $quantity = (int) $request->input('quantity', 1);

        $action($request->session(), $product, $quantity);

        return back();
    }

    public function destroy(
        Request $request,
        Product $product,
        RemoveCartItemAction $action,
    ): RedirectResponse {
        $action($request->session(), $product);

        return back();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\AddCartItemAction;
use App\DTOs\Commerce\AddCartItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\AddCartItemRequest;
use Illuminate\Http\RedirectResponse;

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
}

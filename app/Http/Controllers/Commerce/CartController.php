<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Actions\Commerce\GetCartAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartLineResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(Request $request, GetCartAction $action): Response
    {
        $lines = $action($request->session());

        return Inertia::render('Cart', [
            'lines' => CartLineResource::collection($lines)->resolve($request),
        ]);
    }
}

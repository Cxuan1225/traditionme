<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Shop', [
            'category' => $request->string('category')->toString(),
            'occasion' => $request->string('occasion')->toString(),
        ]);
    }
}

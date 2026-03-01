<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class CollectionController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        return redirect()->route('shop.index', ['occasion' => $slug]);
    }
}

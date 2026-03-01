<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminViewMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ViewModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:admin,storefront'],
        ]);

        AdminViewMode::set($request, (string) $validated['mode']);

        return back();
    }
}

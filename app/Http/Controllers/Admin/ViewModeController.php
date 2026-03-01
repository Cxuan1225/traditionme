<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdminViewMode as AdminViewModeEnum;
use App\Http\Controllers\Controller;
use App\Support\AdminViewMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViewModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::enum(AdminViewModeEnum::class)],
        ]);

        AdminViewMode::set($request, (string) $validated['mode']);

        return back();
    }
}

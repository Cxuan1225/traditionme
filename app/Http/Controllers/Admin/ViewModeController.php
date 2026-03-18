<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdminViewMode as AdminViewModeEnum;
use App\Http\Controllers\Controller;
use App\Support\AdminViewMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Validation\Rule;

#[Middleware('role:admin')]
class ViewModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', Rule::enum(AdminViewModeEnum::class)],
        ]);

        AdminViewMode::set($request, $request->string('mode')->toString());

        return back();
    }
}

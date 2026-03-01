<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\AdminViewMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        if (AdminViewMode::isAdminMode($request)) {
            return Inertia::render('admin/settings/Appearance');
        }

        return to_route('profile.edit');
    }
}

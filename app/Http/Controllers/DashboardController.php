<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AdminViewMode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $shouldEnterAdmin = $user instanceof User
            && AdminViewMode::isAdmin($user)
            && $request->boolean('admin');

        if ($shouldEnterAdmin) {
            AdminViewMode::set($request, AdminViewMode::ADMIN);
        }

        if (AdminViewMode::isAdminMode($request)) {
            return Inertia::render('admin/Dashboard');
        }

        return Inertia::render('account/Dashboard');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\AdminViewMode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if (AdminViewMode::isAdminMode($request)) {
            return Inertia::render('admin/Dashboard');
        }

        return Inertia::render('account/Dashboard');
    }
}

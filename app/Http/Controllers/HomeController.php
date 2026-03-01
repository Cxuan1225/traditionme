<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetWelcomeDataAction;
use App\Http\Resources\WelcomePageResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __invoke(GetWelcomeDataAction $action, Request $request): Response
    {
        $user = $request->user();
        $welcomeData = WelcomePageResource::make($action())->resolve($request);

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'canAccessAdministration' => $user !== null && ($user->hasRole('admin') || $user->can('roles.view')),
            ...$welcomeData,
        ]);
    }
}

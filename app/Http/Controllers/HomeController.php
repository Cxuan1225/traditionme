<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Home\GetWelcomeDataAction;
use App\Http\Resources\WelcomePageResource;
use App\Support\AdminViewMode;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __invoke(GetWelcomeDataAction $action, Request $request): Response
    {
        $user = $request->user();
        $welcomeData = WelcomePageResource::make($action())->resolve($request);
        $cartItems = $request->session()->get('cart.items', []);
        $cartCount = (int) array_sum(Arr::wrap($cartItems));

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'canAccessAdministration' => $user !== null && AdminViewMode::isAdmin($user),
            'cartCount' => $cartCount,
            ...$welcomeData,
        ]);
    }
}

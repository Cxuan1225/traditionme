<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AdminViewMode;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    #[\Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function share(Request $request): array
    {
        $user = $request->user();
        $isAdmin = AdminViewMode::isAdmin($user);
        $status = $request->session()->get('status');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'permissions' => fn (): array => $request->user()?->getPermissionNames()->values()->all() ?? [],
                'roles' => fn (): array => $request->user()?->getRoleNames()->values()->all() ?? [],
                'isAdmin' => $isAdmin,
                'adminViewMode' => $isAdmin ? AdminViewMode::current($request) : AdminViewMode::STOREFRONT,
            ],
            'flash' => [
                'status' => fn (): ?string => is_string($status) ? $status : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

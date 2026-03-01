<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

final class AdminViewMode
{
    public const ADMIN = 'admin';

    public const STOREFRONT = 'storefront';

    private const SESSION_KEY = 'admin_view_mode';

    public static function isAdmin(User|null $user): bool
    {
        return $user?->hasRole('admin') ?? false;
    }

    public static function current(Request $request): string
    {
        $user = $request->user();

        if (! $user instanceof User || ! self::isAdmin($user)) {
            return self::STOREFRONT;
        }

        $mode = (string) $request->session()->get(self::SESSION_KEY, self::ADMIN);

        return in_array($mode, [self::ADMIN, self::STOREFRONT], true)
            ? $mode
            : self::ADMIN;
    }

    public static function isAdminMode(Request $request): bool
    {
        return self::current($request) === self::ADMIN;
    }

    public static function set(Request $request, string $mode): void
    {
        if (! in_array($mode, [self::ADMIN, self::STOREFRONT], true)) {
            return;
        }

        $user = $request->user();

        if (! $user instanceof User || ! self::isAdmin($user)) {
            $request->session()->forget(self::SESSION_KEY);

            return;
        }

        $request->session()->put(self::SESSION_KEY, $mode);
    }
}

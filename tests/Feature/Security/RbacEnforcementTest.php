<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Permission;

test('roles index is forbidden without permission', function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('security.roles.index'))
        ->assertForbidden();
});

test('roles index is accessible with roles.view permission', function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    Permission::findOrCreate('roles.view', 'web');
    $user->givePermissionTo('roles.view');

    $this->actingAs($user)
        ->getJson(route('security.roles.index'))
        ->assertOk()
        ->assertJsonStructure(['data']);
});

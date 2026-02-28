<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Spatie\Permission\Models\Permission;

test('roles index is forbidden without permission', function (): void {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('security.roles.index'))
        ->assertForbidden();
});

test('roles index is accessible with roles.view permission', function (): void {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    Permission::findOrCreate('roles.view', 'web');
    $user->givePermissionTo('roles.view');

    $this->actingAs($user)
        ->get(route('security.roles.index'))
        ->assertOk()
        ->assertJsonStructure(['data']);
});

<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('sync role permissions is idempotent for duplicate calls', function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $actor = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    Permission::findOrCreate('roles.manage_permissions', 'web');
    $actor->givePermissionTo('roles.manage_permissions');

    $role = Role::findOrCreate('manager', 'web');
    Permission::findOrCreate('orders.view', 'web');

    $payload = ['permissions' => ['orders.view']];

    $this->actingAs($actor)
        ->putJson(route('security.roles.permissions.update', $role), $payload)
        ->assertOk();

    $this->actingAs($actor)
        ->putJson(route('security.roles.permissions.update', $role), $payload)
        ->assertOk();

    $role->refresh()->load('permissions');

    expect($role->permissions->pluck('name')->all())->toBe(['orders.view']);
});

test('assign user roles uses sync behavior', function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $actor = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    Permission::findOrCreate('users.assign_roles', 'web');
    $actor->givePermissionTo('users.assign_roles');

    $target = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Role::findOrCreate('editor', 'web');
    Role::findOrCreate('viewer', 'web');

    $this->actingAs($actor)
        ->putJson(route('security.users.roles.update', $target), ['roles' => ['editor']])
        ->assertOk();

    $this->actingAs($actor)
        ->putJson(route('security.users.roles.update', $target), ['roles' => ['viewer']])
        ->assertOk();

    $target->refresh();

    expect($target->hasRole('viewer'))->toBeTrue();
    expect($target->hasRole('editor'))->toBeFalse();
});

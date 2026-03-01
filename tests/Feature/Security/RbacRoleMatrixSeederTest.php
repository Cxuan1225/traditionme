<?php

declare(strict_types=1);

use Database\Seeders\SecurityRbacSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('rbac seeder defines canonical admin user seller matrix', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = Role::findByName('admin', 'web');
    $seller = Role::findByName('seller', 'web');
    $user = Role::findByName('user', 'web');

    $adminPermissions = $admin->permissions->pluck('name')->sort()->values()->all();
    $sellerPermissions = $seller->permissions->pluck('name')->sort()->values()->all();
    $userPermissions = $user->permissions->pluck('name')->sort()->values()->all();

    expect($adminPermissions)->toBe([
        'admin.view',
        'admin.view.switch',
        'permissions.create',
        'permissions.view',
        'products.create',
        'products.delete',
        'products.update',
        'products.view',
        'roles.create',
        'roles.manage_permissions',
        'roles.view',
        'security.audit.view',
        'security.mfa.manage',
        'security.sessions.revoke',
        'users.assign_roles',
    ]);
    expect($sellerPermissions)->toBe(['security.mfa.manage']);
    expect($userPermissions)->toBe(['security.mfa.manage']);
});

test('rbac seeder is idempotent when rerun', function (): void {
    $this->seed(SecurityRbacSeeder::class);
    $this->seed(SecurityRbacSeeder::class);

    expect(Role::query()->whereIn('name', ['admin', 'seller', 'user'])->count())->toBe(3);
    expect(Permission::query()->count())->toBe(15);
    expect(Role::findByName('admin', 'web')->permissions()->count())->toBe(15);
    expect(Role::findByName('seller', 'web')->permissions()->count())->toBe(1);
    expect(Role::findByName('user', 'web')->permissions()->count())->toBe(1);
});

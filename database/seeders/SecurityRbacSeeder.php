<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SecurityRbacSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSION_MATRIX = [
        'admin' => [
            'roles.view',
            'roles.create',
            'roles.manage_permissions',
            'permissions.view',
            'permissions.create',
            'users.assign_roles',
            'security.audit.view',
            'security.sessions.revoke',
            'security.mfa.manage',
        ],
        'seller' => [
            'security.mfa.manage',
        ],
        'user' => [
            'security.mfa.manage',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::ROLE_PERMISSION_MATRIX)
            ->flatten()
            ->unique()
            ->values()
            ->all();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSION_MATRIX as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

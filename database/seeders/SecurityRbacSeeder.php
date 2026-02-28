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
     * @var list<string>
     */
    private const PERMISSIONS = [
        'roles.view',
        'roles.create',
        'roles.manage_permissions',
        'permissions.view',
        'permissions.create',
        'users.assign_roles',
        'security.audit.view',
        'security.sessions.revoke',
        'security.mfa.manage',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $superAdmin->syncPermissions(self::PERMISSIONS);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Security;

use App\DTOs\Security\SyncRolePermissionsData;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsAction
{
    public function __invoke(Role $role, SyncRolePermissionsData $data): Role
    {
        $role->syncPermissions($data->permissions);

        return $role->load('permissions');
    }
}

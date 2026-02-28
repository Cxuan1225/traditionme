<?php

declare(strict_types=1);

namespace App\Actions\Security;

use App\DTOs\Security\CreateRoleData;
use Spatie\Permission\Models\Role;

class CreateRoleAction
{
    public function __invoke(CreateRoleData $data): Role
    {
        $role = Role::create([
            'name' => $data->name,
            'guard_name' => $data->guardName,
        ]);

        if ($data->permissions !== []) {
            $role->syncPermissions($data->permissions);
        }

        return $role->load('permissions');
    }
}

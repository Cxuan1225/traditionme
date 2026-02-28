<?php

declare(strict_types=1);

namespace App\Actions\Security;

use App\DTOs\Security\AssignUserRolesData;
use App\Models\User;

class AssignUserRolesAction
{
    public function __invoke(User $user, AssignUserRolesData $data): User
    {
        $user->syncRoles($data->roles);

        return $user->load('roles.permissions');
    }
}

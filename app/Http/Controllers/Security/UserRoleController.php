<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Actions\Security\AssignUserRolesAction;
use App\DTOs\Security\AssignUserRolesData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\AssignUserRolesRequest;
use App\Http\Resources\UserSecurityResource;
use App\Models\User;

class UserRoleController extends Controller
{
    public function update(
        AssignUserRolesRequest $request,
        User $user,
        AssignUserRolesAction $action,
    ): UserSecurityResource {
        return new UserSecurityResource($action($user, AssignUserRolesData::fromRequest($request)));
    }
}

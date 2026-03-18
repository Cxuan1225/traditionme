<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Actions\Security\SyncRolePermissionsAction;
use App\DTOs\Security\SyncRolePermissionsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\SyncRolePermissionsRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Spatie\Permission\Models\Role;

#[Middleware('permission:roles.manage_permissions')]
class RolePermissionController extends Controller
{
    public function update(
        SyncRolePermissionsRequest $request,
        Role $role,
        SyncRolePermissionsAction $action,
    ): RoleResource {
        return new RoleResource($action($role, SyncRolePermissionsData::fromRequest($request)));
    }
}

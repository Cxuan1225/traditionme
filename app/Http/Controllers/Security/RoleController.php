<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Actions\Security\CreateRoleAction;
use App\DTOs\Security\CreateRoleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\CreateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Middleware('role_or_permission:admin|roles.view', only: ['index'])]
#[Middleware('permission:roles.create', only: ['store'])]
class RoleController extends Controller
{
    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        if (! $request->expectsJson()) {
            return Inertia::render('settings/security/Roles', [
                'initialRoles' => RoleResource::collection($roles)->resolve($request),
                'initialPermissions' => PermissionResource::collection($permissions)->resolve($request),
                'capabilities' => [
                    'canViewRoles' => $request->user()?->can('roles.view') ?? false,
                    'canCreateRoles' => $request->user()?->can('roles.create') ?? false,
                    'canManageRolePermissions' => $request->user()?->can('roles.manage_permissions') ?? false,
                    'canAssignUserRoles' => $request->user()?->can('users.assign_roles') ?? false,
                ],
            ]);
        }

        return RoleResource::collection($roles)
            ->additional([
                'permissions' => PermissionResource::collection($permissions),
            ]);
    }

    public function store(CreateRoleRequest $request, CreateRoleAction $action): RoleResource
    {
        $role = $action(CreateRoleData::fromRequest($request));

        return new RoleResource($role);
    }
}

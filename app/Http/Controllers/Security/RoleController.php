<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Actions\Security\CreateRoleAction;
use App\DTOs\Security\CreateRoleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\CreateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return RoleResource::collection($roles)
            ->additional([
                'permissions' => PermissionResource::collection(
                    Permission::query()
                        ->where('guard_name', 'web')
                        ->orderBy('name')
                        ->get(),
                ),
            ]);
    }

    public function store(CreateRoleRequest $request, CreateRoleAction $action): RoleResource
    {
        $role = $action(CreateRoleData::fromRequest($request));

        return new RoleResource($role);
    }
}

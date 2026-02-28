<?php

declare(strict_types=1);

namespace App\DTOs\Security;

use App\Http\Requests\Security\SyncRolePermissionsRequest;

readonly class SyncRolePermissionsData
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public array $permissions,
    ) {}

    public static function fromRequest(SyncRolePermissionsRequest $request): self
    {
        $rawPermissions = $request->validated('permissions');

        if (! is_array($rawPermissions)) {
            $rawPermissions = [];
        }

        $permissions = [];
        foreach ($rawPermissions as $permission) {
            if (is_string($permission)) {
                $permissions[] = $permission;
            }
        }

        return new self(permissions: $permissions);
    }
}

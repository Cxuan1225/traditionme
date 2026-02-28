<?php

declare(strict_types=1);

namespace App\DTOs\Security;

use App\Http\Requests\Security\AssignUserRolesRequest;

readonly class AssignUserRolesData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public array $roles,
    ) {}

    public static function fromRequest(AssignUserRolesRequest $request): self
    {
        $rawRoles = $request->validated('roles');

        if (! is_array($rawRoles)) {
            $rawRoles = [];
        }

        $roles = [];
        foreach ($rawRoles as $role) {
            if (is_string($role)) {
                $roles[] = $role;
            }
        }

        return new self(roles: $roles);
    }
}

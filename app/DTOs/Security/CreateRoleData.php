<?php

declare(strict_types=1);

namespace App\DTOs\Security;

use App\Http\Requests\Security\CreateRoleRequest;
use UnexpectedValueException;

readonly class CreateRoleData
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public string $name,
        public string $guardName,
        public array $permissions = [],
    ) {}

    public static function fromRequest(CreateRoleRequest $request): self
    {
        $rawPermissions = $request->validated('permissions', []);

        if (! is_array($rawPermissions)) {
            $rawPermissions = [];
        }

        $permissions = [];
        foreach ($rawPermissions as $permission) {
            if (is_string($permission)) {
                $permissions[] = $permission;
            }
        }
        $name = $request->validated('name');

        if (! is_string($name)) {
            throw new UnexpectedValueException('Validated role name must be a string.');
        }

        return new self(
            name: $name,
            guardName: 'web',
            permissions: $permissions,
        );
    }
}

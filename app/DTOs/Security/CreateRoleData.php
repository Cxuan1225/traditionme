<?php

declare(strict_types=1);

namespace App\DTOs\Security;

use App\Http\Requests\Security\CreateRoleRequest;

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
        /** @var list<string> $permissions */
        $permissions = array_values($request->validated('permissions', []));

        return new self(
            name: $request->validated('name'),
            guardName: 'web',
            permissions: $permissions,
        );
    }
}

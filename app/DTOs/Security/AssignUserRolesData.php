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
        /** @var list<string> $roles */
        $roles = array_values($request->validated('roles'));

        return new self(roles: $roles);
    }
}

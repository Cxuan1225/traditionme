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
        /** @var list<string> $permissions */
        $permissions = array_values($request->validated('permissions'));

        return new self(permissions: $permissions);
    }
}

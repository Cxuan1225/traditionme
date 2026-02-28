<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(
                    static fn (QueryBuilder $query): QueryBuilder => $query->where('guard_name', 'web')
                ),
                'distinct',
            ],
        ];
    }
}

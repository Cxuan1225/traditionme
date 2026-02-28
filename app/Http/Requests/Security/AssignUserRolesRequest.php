<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where(
                    static fn (QueryBuilder $query): QueryBuilder => $query->where('guard_name', 'web')
                ),
                'distinct',
            ],
        ];
    }
}

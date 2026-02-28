<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
                'distinct',
            ],
        ];
    }
}

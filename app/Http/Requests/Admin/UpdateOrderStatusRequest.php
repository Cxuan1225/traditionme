<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Admin\UpdateOrderStatusData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(UpdateOrderStatusData::allowedStatuses())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', 'alpha_dash', Rule::unique('products', 'slug')],
            'category' => ['required', 'string', 'max:120'],
            'price_in_sen' => ['required', 'integer', 'min:1', 'max:99999999'],
            'original_price_in_sen' => ['nullable', 'integer', 'min:1', 'max:99999999'],
            'badge' => ['nullable', 'string', 'max:80'],
            'gradient' => ['nullable', 'string', 'max:120'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

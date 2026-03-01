<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : null;

        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'category' => ['required', 'string', 'max:120'],
            'price_in_sen' => ['required', 'integer', 'min:1', 'max:99999999'],
            'original_price_in_sen' => ['nullable', 'integer', 'min:1', 'max:99999999'],
            'badge' => ['nullable', 'string', 'max:80'],
            'gradient' => ['nullable', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

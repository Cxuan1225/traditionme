<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $category
 * @property string|null $description
 * @property int $price_in_sen
 * @property int|null $original_price_in_sen
 * @property string|null $badge
 * @property string|null $gradient
 * @property string|null $image_url
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['name', 'slug', 'category', 'description', 'price_in_sen', 'original_price_in_sen', 'badge', 'gradient', 'image_url', 'is_active'])]
class Product extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }
}

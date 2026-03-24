<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property int $stock_quantity
 * @property bool $track_stock
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['name', 'slug', 'category', 'description', 'price_in_sen', 'original_price_in_sen', 'badge', 'gradient', 'image_url', 'is_active', 'stock_quantity', 'track_stock'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
            'track_stock' => 'bool',
        ];
    }

    public function isInStock(): bool
    {
        return ! $this->track_stock || $this->stock_quantity > 0;
    }

    public function hasStockFor(int $quantity): bool
    {
        return ! $this->track_stock || $this->stock_quantity >= $quantity;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property int $unit_price_in_sen
 * @property int $quantity
 * @property int $subtotal_in_sen
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['order_id', 'product_id', 'product_name', 'unit_price_in_sen', 'quantity', 'subtotal_in_sen'])]
class OrderItem extends Model
{
    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

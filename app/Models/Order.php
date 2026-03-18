<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property int $subtotal_in_sen
 * @property int $discount_in_sen
 * @property int $shipping_in_sen
 * @property int $total_in_sen
 * @property string $shipping_name
 * @property string $shipping_address
 * @property string $shipping_city
 * @property string $shipping_state
 * @property string $shipping_postcode
 * @property string $shipping_phone
 * @property string|null $coupon_code
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['user_id', 'status', 'subtotal_in_sen', 'discount_in_sen', 'shipping_in_sen', 'total_in_sen', 'shipping_name', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_phone', 'coupon_code', 'notes', 'paid_at', 'shipped_at', 'delivered_at'])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

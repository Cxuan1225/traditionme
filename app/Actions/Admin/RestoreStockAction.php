<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Order;
use App\Models\Product;

class RestoreStockAction
{
    public function __invoke(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            Product::query()
                ->where('id', $item->product_id)
                ->where('track_stock', true)
                ->increment('stock_quantity', $item->quantity);
        }
    }
}

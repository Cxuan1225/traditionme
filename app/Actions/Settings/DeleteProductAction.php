<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\Product;

class DeleteProductAction
{
    public function __invoke(Product $product): void
    {
        $product->delete();
    }
}

<?php

declare(strict_types=1);

use App\Enums\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->select(['id', 'category'])
            ->orderBy('id')
            ->lazy()
            ->each(static function (object $product): void {
                if (! is_string($product->category)) {
                    return;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'category' => ProductCategory::normalize($product->category)->value,
                    ]);
            });
    }

    public function down(): void
    {
        // Historical free-text categories cannot be restored safely.
    }
};

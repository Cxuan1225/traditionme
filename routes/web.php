<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CartItemController;
use App\Http\Controllers\Commerce\CollectionController;
use App\Http\Controllers\Commerce\ShopController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ViewModeController;
use App\Http\Controllers\Marketing\NewsletterSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
Route::post('/newsletter/subscriptions', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscriptions.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('admin/view-mode', [ViewModeController::class, 'update'])
        ->middleware('role:admin')
        ->name('admin.view-mode.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/security.php';

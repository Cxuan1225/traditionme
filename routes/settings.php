<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProductController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('settings/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('settings/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('settings/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('settings/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('settings/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('settings/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});

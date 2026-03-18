<?php

declare(strict_types=1);

use App\Http\Controllers\Security\AuditLogController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\RolePermissionController;
use App\Http\Controllers\Security\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('settings/security')->group(function (): void {
    Route::get('roles', [RoleController::class, 'index'])->name('security.roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('security.roles.store');
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('security.roles.permissions.update');
    Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('security.users.roles.update');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('security.audit-logs.index');
});

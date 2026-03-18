<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('role:admin')]
class AuditLogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AuditLogResource::collection(
            AuditLog::query()
                ->latest('occurred_at')
                ->limit(200)
                ->get(),
        );
    }
}

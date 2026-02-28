<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AuditLog */
class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\AuditLog $auditLog */
        $auditLog = $this->resource;

        return [
            'id' => $auditLog->id,
            'actor_id' => $auditLog->actor_id,
            'event' => $auditLog->event,
            'subject_type' => $auditLog->subject_type,
            'subject_id' => $auditLog->subject_id,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'metadata' => $auditLog->metadata,
            'occurred_at' => $auditLog->occurred_at,
        ];
    }
}

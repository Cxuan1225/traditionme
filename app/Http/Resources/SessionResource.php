<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\UserSession */
class SessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\UserSession $session */
        $session = $this->resource;

        return [
            'id' => $session->id,
            'session_id' => $session->session_id,
            'user_id' => $session->user_id,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
            'last_activity_at' => $session->last_activity_at,
            'revoked_at' => $session->revoked_at,
        ];
    }
}

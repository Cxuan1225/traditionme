<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Home\WelcomeOccasionData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WelcomeOccasionData */
class WelcomeOccasionResource extends JsonResource
{
    /**
     * @return array{name: string, description: string, badge: string}
     */
    public function toArray(Request $request): array
    {
        /** @var WelcomeOccasionData $occasion */
        $occasion = $this->resource;

        return [
            'name' => $occasion->name,
            'description' => $occasion->description,
            'badge' => $occasion->badge,
        ];
    }
}

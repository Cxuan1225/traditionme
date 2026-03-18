<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $session_id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property CarbonImmutable|Carbon|string|null $last_activity_at
 * @property CarbonImmutable|Carbon|string|null $revoked_at
 */
#[Fillable(['session_id', 'user_id', 'ip_address', 'user_agent', 'last_activity_at', 'revoked_at'])]
class UserSession extends Model
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

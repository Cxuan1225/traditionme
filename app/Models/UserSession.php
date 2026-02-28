<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $session_id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonImmutable|\Illuminate\Support\Carbon|string|null $last_activity_at
 * @property \Carbon\CarbonImmutable|\Illuminate\Support\Carbon|string|null $revoked_at
 */
class UserSession extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'revoked_at',
    ];

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

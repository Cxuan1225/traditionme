<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $source
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class NewsletterSubscriber extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'source',
    ];
}

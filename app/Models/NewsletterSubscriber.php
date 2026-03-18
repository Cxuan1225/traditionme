<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string $source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['email', 'source'])]
class NewsletterSubscriber extends Model {}

<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\DTOs\Marketing\SubscribeNewsletterData;
use App\Models\NewsletterSubscriber;

class SubscribeNewsletterAction
{
    public function __invoke(SubscribeNewsletterData $data): NewsletterSubscriber
    {
        /** @var NewsletterSubscriber $subscriber */
        $subscriber = NewsletterSubscriber::query()->firstOrCreate(
            ['email' => $data->email],
            ['source' => $data->source],
        );

        return $subscriber;
    }
}

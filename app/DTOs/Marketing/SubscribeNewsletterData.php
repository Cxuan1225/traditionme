<?php

declare(strict_types=1);

namespace App\DTOs\Marketing;

use App\Http\Requests\Marketing\SubscribeNewsletterRequest;
use UnexpectedValueException;

readonly class SubscribeNewsletterData
{
    public function __construct(
        public string $email,
        public string $source,
    ) {}

    public static function fromRequest(SubscribeNewsletterRequest $request): self
    {
        $email = $request->validated('email');
        $source = $request->validated('source', 'welcome-page');

        if (! is_string($email)) {
            throw new UnexpectedValueException('Validated email must be a string.');
        }

        if (! is_string($source)) {
            throw new UnexpectedValueException('Validated source must be a string.');
        }

        return new self(
            email: $email,
            source: $source,
        );
    }
}

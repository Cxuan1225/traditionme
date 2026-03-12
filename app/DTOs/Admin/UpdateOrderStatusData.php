<?php

declare(strict_types=1);

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use UnexpectedValueException;

readonly class UpdateOrderStatusData
{
    /**
     * @var list<string>
     */
    private const ALLOWED_STATUSES = [
        'pending',
        'paid',
        'shipped',
        'delivered',
        'cancelled',
    ];

    public function __construct(
        public string $status,
        public ?string $notes,
    ) {}

    /**
     * @return list<string>
     */
    public static function allowedStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    public static function fromRequest(UpdateOrderStatusRequest $request): self
    {
        $status = $request->validated('status');
        $notes = $request->validated('notes');

        if (! is_string($status) || ! in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new UnexpectedValueException('Validated order status is invalid.');
        }

        if (! is_string($notes) && $notes !== null) {
            throw new UnexpectedValueException('Validated order notes must be a string or null.');
        }

        return new self(
            status: $status,
            notes: self::normalizeOptionalString($notes),
        );
    }

    private static function normalizeOptionalString(?string $value): ?string
    {
        $normalizedValue = $value !== null ? trim($value) : null;

        return $normalizedValue !== null && $normalizedValue !== '' ? $normalizedValue : null;
    }
}

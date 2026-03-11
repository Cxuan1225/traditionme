<?php

declare(strict_types=1);

namespace App\DTOs\Commerce;

use App\Http\Requests\Commerce\CheckoutRequest;
use UnexpectedValueException;

readonly class PlaceOrderData
{
    public function __construct(
        public string $shippingName,
        public string $shippingAddress,
        public string $shippingCity,
        public string $shippingState,
        public string $shippingPostcode,
        public string $shippingPhone,
        public ?string $couponCode,
        public ?string $notes,
    ) {}

    public static function fromRequest(CheckoutRequest $request): self
    {
        $shippingName = $request->validated('shipping_name');
        $shippingAddress = $request->validated('shipping_address');
        $shippingCity = $request->validated('shipping_city');
        $shippingState = $request->validated('shipping_state');
        $shippingPostcode = $request->validated('shipping_postcode');
        $shippingPhone = $request->validated('shipping_phone');
        $couponCode = $request->validated('coupon_code');
        $notes = $request->validated('notes');

        if (! is_string($shippingName)) {
            throw new UnexpectedValueException('Validated shipping_name must be a string.');
        }

        if (! is_string($shippingAddress)) {
            throw new UnexpectedValueException('Validated shipping_address must be a string.');
        }

        if (! is_string($shippingCity)) {
            throw new UnexpectedValueException('Validated shipping_city must be a string.');
        }

        if (! is_string($shippingState)) {
            throw new UnexpectedValueException('Validated shipping_state must be a string.');
        }

        if (! is_string($shippingPostcode)) {
            throw new UnexpectedValueException('Validated shipping_postcode must be a string.');
        }

        if (! is_string($shippingPhone)) {
            throw new UnexpectedValueException('Validated shipping_phone must be a string.');
        }

        if (! is_string($couponCode) && $couponCode !== null) {
            throw new UnexpectedValueException('Validated coupon_code must be a string or null.');
        }

        if (! is_string($notes) && $notes !== null) {
            throw new UnexpectedValueException('Validated notes must be a string or null.');
        }

        return new self(
            shippingName: $shippingName,
            shippingAddress: $shippingAddress,
            shippingCity: $shippingCity,
            shippingState: $shippingState,
            shippingPostcode: $shippingPostcode,
            shippingPhone: $shippingPhone,
            couponCode: self::normalizeOptionalString($couponCode),
            notes: self::normalizeOptionalString($notes),
        );
    }

    private static function normalizeOptionalString(?string $value): ?string
    {
        $normalizedValue = $value !== null ? trim($value) : null;

        return $normalizedValue !== null && $normalizedValue !== '' ? $normalizedValue : null;
    }
}

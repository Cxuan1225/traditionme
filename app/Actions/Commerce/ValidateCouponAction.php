<?php

declare(strict_types=1);

namespace App\Actions\Commerce;

readonly class ValidateCouponAction
{
    /**
     * @return array{valid: bool, discountPercent: int}
     */
    public function __invoke(?string $couponCode): array
    {
        $normalizedCoupon = $couponCode !== null ? strtoupper(trim($couponCode)) : null;

        if ($normalizedCoupon === 'HERITAGE10') {
            return [
                'valid' => true,
                'discountPercent' => 10,
            ];
        }

        return [
            'valid' => false,
            'discountPercent' => 0,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DTOs\Settings;

use App\Http\Requests\Settings\ProductStoreRequest;
use App\Http\Requests\Settings\ProductUpdateRequest;
use UnexpectedValueException;

readonly class ProductPayloadData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $category,
        public int $priceInSen,
        public ?int $originalPriceInSen,
        public ?string $badge,
        public ?string $gradient,
        public bool $isActive,
    ) {}

    public static function fromStoreRequest(ProductStoreRequest $request): self
    {
        return self::fromValidated($request->validated());
    }

    public static function fromUpdateRequest(ProductUpdateRequest $request): self
    {
        return self::fromValidated($request->validated());
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private static function fromValidated(array $validated): self
    {
        $name = $validated['name'] ?? null;
        $slug = $validated['slug'] ?? null;
        $category = $validated['category'] ?? null;
        $priceInSen = $validated['price_in_sen'] ?? null;
        $originalPriceInSen = $validated['original_price_in_sen'] ?? null;
        $badge = $validated['badge'] ?? null;
        $gradient = $validated['gradient'] ?? null;
        $isActive = $validated['is_active'] ?? null;

        if (! is_string($name) || ! is_string($slug) || ! is_string($category) || ! is_int($priceInSen) || ! is_bool($isActive)) {
            throw new UnexpectedValueException('Validated product payload is invalid.');
        }

        return new self(
            name: $name,
            slug: $slug,
            category: $category,
            priceInSen: $priceInSen,
            originalPriceInSen: is_int($originalPriceInSen) ? $originalPriceInSen : null,
            badge: is_string($badge) ? $badge : null,
            gradient: is_string($gradient) ? $gradient : null,
            isActive: $isActive,
        );
    }
}

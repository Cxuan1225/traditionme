<?php

declare(strict_types=1);

namespace App\DTOs\Commerce;

use App\Http\Requests\Commerce\AddCartItemRequest;
use UnexpectedValueException;

readonly class AddCartItemData
{
    public function __construct(
        public string $productSlug,
    ) {}

    public static function fromRequest(AddCartItemRequest $request): self
    {
        $productSlug = $request->validated('product_slug');

        if (! is_string($productSlug)) {
            throw new UnexpectedValueException('Validated product slug must be a string.');
        }

        return new self(productSlug: $productSlug);
    }
}

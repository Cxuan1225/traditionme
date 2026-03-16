<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings\Concerns;

trait NormalizesProductInput
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('category') && is_string($this->input('category'))) {
            $normalized['category'] = strtolower(trim($this->input('category')));
        }

        if ($this->has('price_in_sen')) {
            $normalized['price_in_sen'] = $this->normalizeInteger($this->input('price_in_sen'));
        }

        if ($this->has('original_price_in_sen')) {
            $normalized['original_price_in_sen'] = $this->normalizeNullableInteger($this->input('original_price_in_sen'));
        }

        if ($this->has('is_active')) {
            $normalized['is_active'] = $this->normalizeBoolean($this->input('is_active'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    private function normalizeInteger(mixed $value): mixed
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return $value;
        }

        $integer = filter_var($normalized, FILTER_VALIDATE_INT);

        return $integer !== false ? $integer : $value;
    }

    private function normalizeNullableInteger(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $this->normalizeInteger($value);
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $boolean ?? $value;
    }
}

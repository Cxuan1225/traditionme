<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductCategory: string
{
    case MALAY = 'malay';
    case CHINESE = 'chinese';
    case INDIAN = 'indian';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MALAY => 'Malay',
            self::CHINESE => 'Chinese',
            self::INDIAN => 'Indian',
            self::OTHER => 'Other',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $category): string => $category->value,
            self::cases(),
        );
    }

    public static function normalize(string $value): self
    {
        $normalized = strtolower(trim($value));

        return match (true) {
            $normalized === self::MALAY->value,
            $normalized === 'melayu',
            str_contains($normalized, 'kurung'),
            str_contains($normalized, 'songket'),
            str_contains($normalized, 'baju melayu') => self::MALAY,

            $normalized === self::CHINESE->value,
            str_contains($normalized, 'cheongsam'),
            str_contains($normalized, 'qipao'),
            str_contains($normalized, 'kebaya') => self::CHINESE,

            $normalized === self::INDIAN->value,
            str_contains($normalized, 'saree'),
            str_contains($normalized, 'sari'),
            str_contains($normalized, 'kurta'),
            str_contains($normalized, 'lehenga') => self::INDIAN,

            default => self::OTHER,
        };
    }
}

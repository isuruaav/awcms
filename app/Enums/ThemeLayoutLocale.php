<?php

namespace App\Enums;

enum ThemeLayoutLocale: string
{
    case English = 'en';

    case Sinhala = 'si';

    case Tamil = 'ta';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Sinhala => 'Sinhala',
            self::Tamil => 'Tamil',
        };
    }

    public function nativeLabel(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Sinhala => 'සිංහල',
            self::Tamil => 'தமிழ்',
        };
    }

    public static function fromApplicationLocale(
        ?string $locale,
    ): self {
        return self::tryFrom(
            is_string($locale)
                ? strtolower(trim($locale))
                : '',
        ) ?? self::English;
    }
}

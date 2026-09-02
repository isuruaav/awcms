<?php

namespace App\Enums;

enum PageLocale: string
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

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $locale): string => $locale->value,
            self::cases(),
        );
    }
}

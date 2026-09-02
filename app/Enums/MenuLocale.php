<?php

namespace App\Enums;

enum MenuLocale: string
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

    public static function fromApplicationLocale(?string $locale = null): self
    {
        return self::tryFrom($locale ?? app()->getLocale()) ?? self::English;
    }
}

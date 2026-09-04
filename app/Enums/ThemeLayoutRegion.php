<?php

namespace App\Enums;

enum ThemeLayoutRegion: string
{
    case Header = 'header';
    case Footer = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header',
            self::Footer => 'Footer',
        };
    }
}

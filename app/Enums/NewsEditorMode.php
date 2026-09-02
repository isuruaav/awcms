<?php

namespace App\Enums;

enum NewsEditorMode: string
{
    case Visual = 'visual';
    case Html = 'html';

    public function label(): string
    {
        return match ($this) {
            self::Visual => 'Visual Editor',
            self::Html => 'HTML + Tailwind',
        };
    }
}

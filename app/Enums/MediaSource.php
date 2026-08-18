<?php

namespace App\Enums;

enum MediaSource: string
{
    case Upload = 'upload';

    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Upload => 'Uploaded File',
            self::External => 'External Link',
        };
    }
}

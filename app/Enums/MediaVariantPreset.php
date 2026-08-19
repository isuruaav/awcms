<?php

namespace App\Enums;

enum MediaVariantPreset: string
{
    case Thumbnail = 'thumbnail';

    case Medium = 'medium';

    public function label(): string
    {
        return match ($this) {
            self::Thumbnail => 'Thumbnail',
            self::Medium => 'Medium',
        };
    }

    /**
     * @return int<1, max>
     */
    public function maximumWidth(): int
    {
        return match ($this) {
            self::Thumbnail => 480,
            self::Medium => 1280,
        };
    }

    /**
     * @return int<1, max>
     */
    public function maximumHeight(): int
    {
        return match ($this) {
            self::Thumbnail => 480,
            self::Medium => 1280,
        };
    }

    /**
     * @return int<1, 100>
     */
    public function quality(): int
    {
        return match ($this) {
            self::Thumbnail => 78,
            self::Medium => 82,
        };
    }
}

<?php

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';

    case Document = 'document';

    case Video = 'video';

    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Document => 'Document',
            self::Video => 'Video',
            self::Other => 'Other',
        };
    }
}

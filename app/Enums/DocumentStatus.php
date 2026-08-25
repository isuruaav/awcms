<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';

    case Published = 'published';

    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',

            self::Published => 'Published',

            self::Archived => 'Archived',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}

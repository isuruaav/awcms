<?php

namespace App\Enums;

enum NewsStatus: string
{
    case Draft = 'draft';

    case Submitted = 'submitted';

    case Approved = 'approved';

    case Published = 'published';

    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',

            self::Submitted => 'Submitted for Review',

            self::Approved => 'Approved',

            self::Published => 'Published',

            self::Archived => 'Archived',
        };
    }

    public function isEditable(): bool
    {
        return in_array(
            $this,
            [
                self::Draft,
                self::Submitted,
            ],
            true,
        );
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}

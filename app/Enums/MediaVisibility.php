<?php

namespace App\Enums;

enum MediaVisibility: string
{
    case Public = 'public';

    case Internal = 'internal';

    case Restricted = 'restricted';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Internal => 'Internal',
            self::Restricted => 'Restricted',
        };
    }
}

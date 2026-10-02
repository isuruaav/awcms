<?php

namespace App\Enums;

enum SchoolLeadershipRankGroup: string
{
    case Commissioned = 'commissioned';
    case OtherRanks = 'other_ranks';

    public function label(): string
    {
        return match ($this) {
            self::Commissioned => 'Commissioned Officers',
            self::OtherRanks => 'Other Ranks',
        };
    }
}

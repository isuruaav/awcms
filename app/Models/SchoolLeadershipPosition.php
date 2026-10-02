<?php

namespace App\Models;

use App\Enums\SchoolLeadershipRankGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SchoolLeadershipPosition extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'name_en',
        'name_si',
        'rank_group',
        'sort_order',
        'show_on_home',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rank_group' => SchoolLeadershipRankGroup::class,
            'sort_order' => 'integer',
            'show_on_home' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<SchoolLeader, $this>
     */
    public function leaders(): HasMany
    {
        return $this->hasMany(
            SchoolLeader::class,
            'position_id',
        );
    }

    public function nameForLocale(string $locale): string
    {
        if (
            $locale === 'si'
            && trim($this->name_si) !== ''
        ) {
            return $this->name_si;
        }

        return $this->name_en;
    }

    public function requiresCommissionedRank(): bool
    {
        return (string) $this->getRawOriginal('rank_group')
            === SchoolLeadershipRankGroup::Commissioned->value;
    }
}

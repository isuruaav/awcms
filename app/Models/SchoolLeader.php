<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $role_key
 * @property string $title_en
 * @property string|null $title_si
 * @property string|null $name_en
 * @property string|null $name_si
 * @property int|null $image_media_id
 * @property bool $is_active
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 */
final class SchoolLeader extends Model
{
    public const ROLE_COMMANDANT = 'commandant';

    public const ROLE_CHIEF_INSTRUCTOR = 'chief_instructor';

    public const ROLE_WARRANT_OFFICER = 'warrant_officer';

    /** @var list<string> */
    protected $fillable = [
        'role_key',
        'title_en',
        'title_si',
        'name_en',
        'name_si',
        'image_media_id',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image_media_id' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class,
            'image_media_id',
        );
    }

    /**
     * @param  Builder<SchoolLeader>  $query
     * @return Builder<SchoolLeader>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function titleForLocale(string $locale): string
    {
        if (
            $locale === 'si'
            && is_string($this->title_si)
            && trim($this->title_si) !== ''
        ) {
            return $this->title_si;
        }

        return $this->title_en;
    }

    public function nameForLocale(string $locale): string
    {
        if (
            $locale === 'si'
            && is_string($this->name_si)
            && trim($this->name_si) !== ''
        ) {
            return $this->name_si;
        }

        return is_string($this->name_en)
            ? $this->name_en
            : '';
    }

    /**
     * @return list<string>
     */
    public static function allowedRoles(): array
    {
        return [
            self::ROLE_COMMANDANT,
            self::ROLE_CHIEF_INSTRUCTOR,
            self::ROLE_WARRANT_OFFICER,
        ];
    }
}

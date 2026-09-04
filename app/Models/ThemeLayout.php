<?php

namespace App\Models;

use App\Enums\ThemeLayoutLocale;
use App\Enums\ThemeLayoutRegion;
use App\Enums\ThemeLayoutStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $theme_slug
 * @property ThemeLayoutRegion $region
 * @property ThemeLayoutLocale $locale
 * @property string|null $draft_html
 * @property string|null $draft_css
 * @property string|null $published_html
 * @property string|null $published_css
 * @property ThemeLayoutStatus $status
 * @property int $revision_number
 * @property CarbonImmutable|null $published_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $published_by
 */
final class ThemeLayout extends Model
{
    /** @var list<string> */
    protected $guarded = [
        'id',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'locale' => 'en',
        'status' => 'draft',
        'revision_number' => 0,
    ];

    protected static function booted(): void
    {
        self::creating(
            static function (ThemeLayout $layout): void {
                $uuid = $layout->getAttribute(
                    'uuid',
                );

                if (
                    ! is_string($uuid)
                    || trim($uuid) === ''
                ) {
                    $layout->setAttribute(
                        'uuid',
                        (string) Str::uuid(),
                    );
                }
            },
        );
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'region' => ThemeLayoutRegion::class,
            'locale' => ThemeLayoutLocale::class,
            'status' => ThemeLayoutStatus::class,
            'revision_number' => 'integer',
            'published_at' => 'immutable_datetime',
        ];
    }

    /** @return HasMany<ThemeLayoutRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(
            ThemeLayoutRevision::class,
        )->orderByDesc(
            'revision_number',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'published_by',
        );
    }
}

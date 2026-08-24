<?php

namespace App\Models;

use App\Enums\GalleryStatus;
use Carbon\CarbonInterface;
use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class Gallery extends Model
{
    /** @use HasFactory<GalleryFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'event_date',
        'description',
        'cover_media_id',
        'status',
        'published_at',
        'archived_at',
        'seo_title',
        'seo_description',
        'created_by',
        'updated_by',
        'published_by',
        'archived_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',

            'status' => GalleryStatus::class,

            'published_at' => 'datetime',

            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(
            static function (Gallery $gallery): void {
                $uuid =
                    $gallery->getAttribute(
                        'uuid',
                    );

                if (
                    ! is_string(
                        $uuid,
                    )
                    || trim(
                        $uuid,
                    ) === ''
                ) {
                    $gallery->setAttribute(
                        'uuid',
                        Str::uuid()->toString(),
                    );
                }
            },
        );
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class,
            'cover_media_id',
        );
    }

    /**
     * @return HasMany<GalleryImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(
            GalleryImage::class,
        )
            ->orderBy(
                'sort_order',
            )
            ->orderBy(
                'id',
            );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'published_by',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function archiver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'archived_by',
        );
    }

    /**
     * @param  Builder<Gallery>  $query
     * @return Builder<Gallery>
     */
    public function scopePublished(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'status',
                GalleryStatus::Published->value,
            )
            ->whereNotNull(
                'published_at',
            )
            ->where(
                'published_at',
                '<=',
                now(),
            );
    }

    /**
     * @param  Builder<Gallery>  $query
     * @return Builder<Gallery>
     */
    public function scopeRecent(
        Builder $query,
    ): Builder {
        return $query
            ->orderByDesc(
                'event_date',
            )
            ->orderByDesc(
                'published_at',
            )
            ->orderByDesc(
                'id',
            );
    }

    public function isPublished(): bool
    {
        $status =
            $this->getAttribute(
                'status',
            );

        if (
            ! $status instanceof GalleryStatus
            || ! $status->isPublic()
        ) {
            return false;
        }

        $publishedAt =
            $this->getAttribute(
                'published_at',
            );

        if (! $publishedAt instanceof CarbonInterface) {
            return false;
        }

        return $publishedAt->lessThanOrEqualTo(
            now(),
        );
    }

    public function isEditable(): bool
    {
        $status =
            $this->getAttribute(
                'status',
            );

        return $status instanceof GalleryStatus
            && $status->isEditable();
    }
}

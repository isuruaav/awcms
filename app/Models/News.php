<?php

namespace App\Models;

use App\Enums\NewsStatus;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        self::creating(
            function (News $news): void {
                $uuid = $news->getAttribute(
                    'uuid',
                );

                if (
                    ! is_string($uuid)
                    || trim($uuid) === ''
                ) {
                    $news->setAttribute(
                        'uuid',
                        Str::uuid()->toString(),
                    );
                }
            },
        );
    }

    /**
     * @return HasMany<NewsRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(
            NewsRevision::class,
        )->orderByDesc(
            'revision_number',
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,

            'is_featured' => 'boolean',

            'published_at' => 'datetime',

            'submitted_at' => 'datetime',

            'approved_at' => 'datetime',

            'archived_at' => 'datetime',

            'changes_requested_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<NewsCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
            'category_id',
        );
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(
            MediaAsset::class,
            'featured_image_id',
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
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by',
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
     * @param  Builder<News>  $query
     * @return Builder<News>
     */
    public function scopePublished(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'status',
                NewsStatus::Published->value,
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
     * @return BelongsTo<User, $this>
     */
    public function changesRequestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'changes_requested_by',
        );
    }

    /**
     * @param  Builder<News>  $query
     * @return Builder<News>
     */
    public function scopeFeatured(
        Builder $query,
    ): Builder {
        return $query->where(
            'is_featured',
            true,
        );
    }

    public function isPublished(): bool
    {
        $status = $this->getAttribute(
            'status',
        );

        return $status instanceof NewsStatus
            && $status === NewsStatus::Published;
    }
}

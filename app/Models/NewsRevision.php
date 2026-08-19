<?php

namespace App\Models;

use App\Enums\NewsStatus;
use Database\Factories\NewsRevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $news_id
 * @property int $revision_number
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property int|null $featured_image_id
 * @property bool $is_featured
 * @property NewsStatus $status
 * @property Carbon|null $published_at
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property int|null $created_by
 * @property string|null $reason
 */
final class NewsRevision extends Model
{
    /** @use HasFactory<NewsRevisionFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'is_featured' => 'boolean',
            'status' => NewsStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<News, $this>
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(
            News::class,
        );
    }

    /**
     * @return BelongsTo<NewsCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
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
}

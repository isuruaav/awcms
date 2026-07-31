<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Services\ContentSanitizer;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'status' => PageStatus::class,
            'submitted_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
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
    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by',
        );
    }

    public function setExcerptAttribute(mixed $value): void
    {
        $rawExcerpt = is_string($value)
            ? $value
            : null;

        $excerpt = app(
            ContentSanitizer::class,
        )->plainText(
            $rawExcerpt,
            500,
        );

        $this->attributes['excerpt'] = $excerpt !== ''
            ? $excerpt
            : null;
    }

    public function setContentAttribute(mixed $value): void
    {
        $rawContent = is_string($value)
            ? $value
            : null;

        $content = app(
            ContentSanitizer::class,
        )->sanitize(
            $rawContent,
        );

        $this->attributes['content'] = $content !== ''
            ? $content
            : null;
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where(
                'status',
                PageStatus::Published->value,
            )
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '<=',
                now(),
            );
    }
}

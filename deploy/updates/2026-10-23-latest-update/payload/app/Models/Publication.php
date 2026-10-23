<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon|null $published_at
 */
final class Publication extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        self::creating(function (Publication $publication): void {
            $publication->uuid ??= (string) Str::uuid();
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'size_bytes' => 'integer',
            'cover_width' => 'integer',
            'cover_height' => 'integer',
        ];
    }

    /** @param Builder<Publication> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function titleForLocale(string $locale): string
    {
        return $locale === 'si' && is_string($this->title_si) && trim($this->title_si) !== ''
            ? $this->title_si : (string) $this->title;
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && $this->is_published && $this->published_at !== null && $this->published_at->lte(now());
    }

    public static function indexUrl(string $locale = 'en'): string
    {
        return $locale === 'si'
            ? route('publications.index.localized', ['locale' => 'si'])
            : route('publications.index');
    }
}

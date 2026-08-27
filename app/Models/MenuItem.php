<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string $type
 * @property string|null $url
 * @property string|null $route_name
 * @property int|null $reference_id
 * @property bool $open_in_new_tab
 * @property bool $is_active
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 */
final class MenuItem extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'url',
        'route_name',
        'reference_id',
        'open_in_new_tab',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        self::creating(static function (MenuItem $item): void {
            $uuid = $item->getAttribute('uuid');

            if (! is_string($uuid) || trim($uuid) === '') {
                $item->setAttribute(
                    'uuid',
                    (string) Str::uuid(),
                );
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Menu, $this> */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** @return BelongsTo<MenuItem, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<MenuItem, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function resolvedUrl(): string
    {
        if ($this->type === 'route' && is_string($this->route_name) && $this->route_name !== '') {
            return route($this->route_name);
        }

        if ($this->type === 'page' && is_int($this->reference_id)) {
            $page = Page::query()->published()->find($this->reference_id);

            return $page instanceof Page ? route('pages.show', ['slug' => $page->slug]) : '#';
        }

        if ($this->type === 'news' && is_int($this->reference_id)) {
            $news = News::query()->published()->find($this->reference_id);

            return $news instanceof News ? route('news.show', ['slug' => $news->slug]) : '#';
        }

        if ($this->type === 'gallery' && is_int($this->reference_id)) {
            $gallery = Gallery::query()->published()->find($this->reference_id);

            return $gallery instanceof Gallery ? route('galleries.show', ['slug' => $gallery->slug]) : '#';
        }

        if ($this->type === 'document' && is_int($this->reference_id)) {
            $document = Document::query()->published()->find($this->reference_id);

            return $document instanceof Document ? route('documents.show', ['slug' => $document->slug]) : '#';
        }

        return is_string($this->url) && $this->url !== '' ? $this->url : '#';
    }
}

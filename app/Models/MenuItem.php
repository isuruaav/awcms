<?php

namespace App\Models;

use App\Enums\MenuLocale;
use App\Enums\NewsLocale;
use App\Enums\PageLocale;
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
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** @return HasMany<MenuItemTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(MenuItemTranslation::class);
    }

    public function labelForLocale(?string $locale = null): string
    {
        $menuLocale = MenuLocale::fromApplicationLocale($locale);

        if ($menuLocale === MenuLocale::English) {
            return $this->label;
        }

        $translation = $this->translations
            ->first(static fn (MenuItemTranslation $candidate): bool => $candidate->locale === $menuLocale);

        return $translation instanceof MenuItemTranslation && trim($translation->label) !== ''
            ? $translation->label
            : $this->label;
    }

    public function resolvedUrl(?string $locale = null): string
    {
        $menuLocale = MenuLocale::fromApplicationLocale($locale);

        if ($this->type === 'route' && is_string($this->route_name) && $this->route_name !== '') {
            if ($this->route_name === 'news.index' && $menuLocale !== MenuLocale::English) {
                return route('news.index.localized', ['locale' => $menuLocale->value]);
            }

            return route($this->route_name);
        }

        if ($this->type === 'page' && is_int($this->reference_id)) {
            $page = Page::query()->published()->find($this->reference_id);

            if (! $page instanceof Page) {
                return '#';
            }

            $target = $this->translatedPage($page, $menuLocale);
            $rawTargetLocale = $target->getRawOriginal('locale');
            $targetLocale = is_string($rawTargetLocale) ? PageLocale::tryFrom($rawTargetLocale) : null;
            $targetLocale ??= PageLocale::English;

            return $targetLocale === PageLocale::English
                ? route('pages.show', ['slug' => $target->slug])
                : route(
                    'pages.show.localized',
                    [
                        'locale' => $targetLocale->value,
                        'slug' => $target->slug,
                    ],
                );
        }

        if ($this->type === 'news' && is_int($this->reference_id)) {
            $news = News::query()->published()->find($this->reference_id);

            if (! $news instanceof News) {
                return '#';
            }

            $target = $this->translatedNews($news, $menuLocale);
            $rawTargetLocale = $target->getRawOriginal('locale');
            $targetLocale = is_string($rawTargetLocale) ? NewsLocale::tryFrom($rawTargetLocale) : null;
            $targetLocale ??= NewsLocale::English;

            return $targetLocale === NewsLocale::English
                ? route('news.show', ['slug' => $target->slug])
                : route(
                    'news.show.localized',
                    [
                        'locale' => $targetLocale->value,
                        'slug' => $target->slug,
                    ],
                );
        }

        if ($this->type === 'gallery' && is_int($this->reference_id)) {
            $gallery = Gallery::query()->published()->find($this->reference_id);

            return $gallery instanceof Gallery
                ? route('galleries.show', ['slug' => $gallery->slug])
                : '#';
        }

        if ($this->type === 'document' && is_int($this->reference_id)) {
            $document = Document::query()->published()->find($this->reference_id);

            return $document instanceof Document
                ? route('documents.show', ['slug' => $document->slug])
                : '#';
        }

        $translatedUrl = $this->translatedCustomUrl($menuLocale);

        return $translatedUrl !== null
            ? $translatedUrl
            : '#';
    }

    private function translatedCustomUrl(MenuLocale $locale): ?string
    {
        if ($locale !== MenuLocale::English) {
            $translation = $this->translations
                ->first(static fn (MenuItemTranslation $candidate): bool => $candidate->locale === $locale);

            if ($translation instanceof MenuItemTranslation && is_string($translation->url) && trim($translation->url) !== '') {
                return $translation->url;
            }
        }

        return is_string($this->url) && trim($this->url) !== '' ? $this->url : null;
    }

    private function translatedPage(Page $page, MenuLocale $locale): Page
    {
        $group = $page->getAttribute('translation_group');

        if ($locale === MenuLocale::English || ! is_string($group) || trim($group) === '') {
            return $page;
        }

        return Page::query()->published()
            ->where('translation_group', $group)
            ->where('locale', $locale->value)
            ->first() ?? $page;
    }

    private function translatedNews(News $news, MenuLocale $locale): News
    {
        $group = $news->getAttribute('translation_group');

        if ($locale === MenuLocale::English || ! is_string($group) || trim($group) === '') {
            return $news;
        }

        return News::query()->published()
            ->where('translation_group', $group)
            ->where('locale', $locale->value)
            ->first() ?? $news;
    }
}

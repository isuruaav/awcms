<?php

namespace App\Http\Controllers;

use App\Enums\MediaVariantPreset;
use App\Enums\NewsEditorMode;
use App\Enums\NewsLocale;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\News;
use App\Services\PageHtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class PublicNewsController extends Controller
{
    public function index(Request $request): View
    {
        $locale = $this->routeLocale($request);

        app()->setLocale($locale->value);

        $news = News::query()
            ->with([
                'category',
                'featuredImage.variants',
            ])
            ->published()
            ->where('locale', $locale->value)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view(
            'public.news.index',
            [
                'news' => $news,
                'locale' => $locale,
                'locales' => NewsLocale::cases(),
            ],
        );
    }

    public function show(Request $request): View
    {
        $locale = $this->routeLocale($request);
        $slug = $request->route('slug');

        abort_unless(
            is_string($slug) && trim($slug) !== '',
            404,
        );

        app()->setLocale($locale->value);

        $news = News::query()
            ->with([
                'category',
                'featuredImage.variants',
                'images.media.variants',
            ])
            ->published()
            ->where('locale', $locale->value)
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedNews = News::query()
            ->with([
                'category',
                'featuredImage.variants',
            ])
            ->published()
            ->where('locale', $locale->value)
            ->whereKeyNot($news->id)
            ->when(
                $news->category_id !== null,
                fn ($query) => $query->where(
                    'category_id',
                    $news->category_id,
                ),
            )
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $translationGroup = $news->getAttribute('translation_group');

        $publishedTranslations = News::query()
            ->whereRaw('1 = 0')
            ->get();

        if (is_string($translationGroup) && trim($translationGroup) !== '') {
            $publishedTranslations = News::query()
                ->published()
                ->where('translation_group', $translationGroup)
                ->get();
        }

        $languageVersions = array_map(
            static function (NewsLocale $localeOption) use (
                $news,
                $publishedTranslations,
            ): array {
                $translation = $publishedTranslations->first(
                    static fn (News $candidate): bool => $candidate->getRawOriginal('locale') === $localeOption->value,
                );

                $activeLocale = $news->getRawOriginal('locale');

                return [
                    'code' => $localeOption->value,
                    'label' => $localeOption->label(),
                    'native_label' => $localeOption->nativeLabel(),
                    'active' => $activeLocale === $localeOption->value,
                    'available' => $translation instanceof News,
                    'url' => $translation instanceof News
    ? route(
        'news.show.localized',
        [
            'locale' => $localeOption->value,
            'slug' => $translation->slug,
        ],
    )
    : null,
                ];
            },
            NewsLocale::cases(),
        );

        $rawEditorMode = $news->getRawOriginal('editor_mode');

        $editorMode = is_string($rawEditorMode)
            ? NewsEditorMode::tryFrom($rawEditorMode)
            : null;

        $editorMode ??= NewsEditorMode::Visual;

        $content = $news->getAttribute('content');

        $rawContent = is_string($content)
            ? $content
            : null;

        $sanitizer = app(PageHtmlSanitizer::class);

        $safeContent = $editorMode === NewsEditorMode::Visual
            ? $sanitizer->sanitizeVisual($rawContent)
            : $sanitizer->sanitize($rawContent);

        return view(
            'public.news.show',
            [
                'news' => $news,
                'locale' => $locale,
                'languageVersions' => $languageVersions,
                'relatedNews' => $relatedNews,
                'safeContent' => $safeContent,
                'featuredImageUrl' => self::imageUrl(
                    $news->featuredImage,
                ),
            ],
        );
    }

    public static function imageUrl(?MediaAsset $media): ?string
    {
        if (
            ! $media instanceof MediaAsset
            || $media->trashed()
            || ! $media->isImage()
            || ! $media->isPublic()
        ) {
            return null;
        }

        $variant = $media->variants->first(
            static fn (MediaVariant $variant): bool => $variant->name === MediaVariantPreset::Medium->value,
        );

        if ($variant instanceof MediaVariant) {
            $disk = $variant->getAttribute('disk');
            $path = $variant->getAttribute('path');

            if (
                is_string($disk)
                && trim($disk) !== ''
                && is_string($path)
                && trim($path) !== ''
            ) {
                return Storage::disk($disk)->url($path);
            }
        }

        $disk = $media->getAttribute('disk');
        $path = $media->getAttribute('path');

        if (
            ! is_string($disk)
            || trim($disk) === ''
            || ! is_string($path)
            || trim($path) === ''
        ) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    private function routeLocale(Request $request): NewsLocale
    {
        $routeLocale = $request->route('locale');

        if (! is_string($routeLocale) || trim($routeLocale) === '') {
            return NewsLocale::English;
        }

        $locale = NewsLocale::tryFrom($routeLocale);

        abort_unless(
            $locale instanceof NewsLocale,
            404,
        );

        return $locale;
    }
}

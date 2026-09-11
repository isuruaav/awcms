@extends('theme-school-of-signals::layout')

@php
    $currentNewsLocale = $news->locale instanceof \BackedEnum
        ? $news->locale->value
        : (string) $news->locale;

    $publicNewsUrl = $currentNewsLocale === 'en'
        ? route('news.show', ['slug' => $news->slug])
        : route('news.show.localized', [
            'locale' => $currentNewsLocale,
            'slug' => $news->slug,
        ]);

    $publicNewsIndexUrl = $currentNewsLocale === 'en'
        ? route('news.index')
        : route('news.index.localized', ['locale' => $currentNewsLocale]);

    $relatedNewsUrl = static function (\App\Models\News $article) use ($currentNewsLocale): string {
        return $currentNewsLocale === 'en'
            ? route('news.show', ['slug' => $article->slug])
            : route('news.show.localized', [
                'locale' => $currentNewsLocale,
                'slug' => $article->slug,
            ]);
    };
@endphp

@section('title', ($news->seo_title ?: $news->title).' | '.config('app.name'))
@section('description', $news->seo_description ?: ($news->summary ?: $news->title))
@section('meta_description', $news->seo_description ?: ($news->summary ?: $news->title))
@section('canonical', $publicNewsUrl)

@section('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $news->seo_title ?: $news->title }}">
    <meta property="og:description" content="{{ $news->seo_description ?: ($news->summary ?: $news->title) }}">
    <meta property="og:url" content="{{ $publicNewsUrl }}">

    @if (is_string($featuredImageUrl) && $featuredImageUrl !== '')
        <meta property="og:image" content="{{ $featuredImageUrl }}">
    @endif
@endsection

@section('content')
    <main class="school-news-page">
        <article class="school-news-article">
            <header class="school-news-header">
                <div class="school-news-container">
                    <div class="school-news-toolbar">
                        <a href="{{ $publicNewsIndexUrl }}" class="school-news-back">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            <span>All News</span>
                        </a>

                        <nav class="school-news-languages" aria-label="Article language">
                            @foreach ($languageVersions as $version)
                                @if (($version['available'] ?? false) === true && is_string($version['url'] ?? null))
                                    <a
                                        href="{{ $version['url'] }}"
                                        hreflang="{{ $version['code'] }}"
                                        @class([
                                            'school-news-language',
                                            'active' => ($version['active'] ?? false) === true,
                                        ])
                                    >
                                        {{ $version['native_label'] }}
                                    </a>
                                @else
                                    <span
                                        class="school-news-language disabled"
                                        title="This language version has not been published yet."
                                    >
                                        {{ $version['native_label'] }}
                                    </span>
                                @endif
                            @endforeach
                        </nav>
                    </div>

                    <div class="school-news-meta">
                        @if ($news->category)
                            <span class="school-news-category">{{ $news->category->name }}</span>
                        @endif

                        @if ($news->is_featured)
                            <span class="school-news-featured">
                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                                Featured
                            </span>
                        @endif

                        @if ($news->published_at)
                            <time
                                class="school-news-date"
                                datetime="{{ $news->published_at->toIso8601String() }}"
                            >
                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                {{ $news->published_at->format('d M Y H:i') }}
                            </time>
                        @endif
                    </div>

                    <h1 class="school-news-title">{{ $news->title }}</h1>

                    @if ($news->summary)
                        <p class="school-news-summary">{{ $news->summary }}</p>
                    @endif
                </div>
            </header>

            @if ($safeContent !== '')
                <section class="school-news-body">
                    <div class="school-news-container">
                        @if ($news->editor_mode === \App\Enums\NewsEditorMode::Visual)
                            <div class="school-news-content awcms-content">
                                {!! $safeContent !!}
                            </div>
                        @else
                            <div class="school-news-content page-html-content">
                                {!! $safeContent !!}
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if ($news->images->isNotEmpty())
                <section class="school-news-gallery-section" aria-labelledby="news-gallery-title">
                    <div class="school-news-container">
                        <div class="school-news-section-heading">
                            <div>
                                <span class="school-news-section-kicker">Photo Gallery</span>
                                <h2 id="news-gallery-title">Event Photographs</h2>
                            </div>

                            <span class="school-news-photo-count">
                                {{ $news->images->count() }}
                                {{ $news->images->count() === 1 ? 'Photo' : 'Photos' }}
                            </span>
                        </div>

                        <div class="school-news-gallery">
                            @foreach ($news->images as $newsImage)
                                @php
                                    $galleryImageUrl = \App\Http\Controllers\PublicNewsController::imageUrl(
                                        $newsImage->media,
                                    );

                                    $galleryAlt = $newsImage->media?->alt_text ?: $news->title;
                                @endphp

                                @if (is_string($galleryImageUrl) && $galleryImageUrl !== '')
                                    <figure class="school-news-gallery-item">
                                        <a
                                            href="{{ $galleryImageUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="Open photograph in a new tab"
                                        >
                                            <img
                                                src="{{ $galleryImageUrl }}"
                                                alt="{{ $galleryAlt }}"
                                                loading="lazy"
                                            >

                                            <span class="school-news-image-overlay" aria-hidden="true">
                                                <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                                            </span>
                                        </a>

                                        @if ($newsImage->media?->caption)
                                            <figcaption>{{ $newsImage->media->caption }}</figcaption>
                                        @endif
                                    </figure>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </article>

        @if ($relatedNews->isNotEmpty())
            <section class="school-related-news" aria-labelledby="related-news-title">
                <div class="school-news-container">
                    <div class="school-news-section-heading">
                        <div>
                            <span class="school-news-section-kicker">Continue Reading</span>
                            <h2 id="related-news-title">Related News</h2>
                        </div>

                        <a href="{{ $publicNewsIndexUrl }}" class="school-news-view-all">
                            View All News
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="school-related-news-grid">
                        @foreach ($relatedNews as $related)
                            <article class="school-related-news-card">
                                @if ($related->published_at)
                                    <time datetime="{{ $related->published_at->toIso8601String() }}">
                                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                        {{ $related->published_at->format('d M Y') }}
                                    </time>
                                @endif

                                <h3>
                                    <a href="{{ $relatedNewsUrl($related) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>

                                <a
                                    href="{{ $relatedNewsUrl($related) }}"
                                    class="school-related-news-link"
                                >
                                    Read Article
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection

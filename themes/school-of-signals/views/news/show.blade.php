@extends('theme-school-of-signals::layout')

@php
    $currentNewsLocale = $news->locale instanceof \BackedEnum
        ? $news->locale->value
        : (string) $news->locale;

    $articleUrl = static function (\App\Models\News $article) use ($currentNewsLocale): string {
        return $currentNewsLocale === 'en'
            ? route('news.show', ['slug' => $article->slug])
            : route('news.show.localized', [
                'locale' => $currentNewsLocale,
                'slug' => $article->slug,
            ]);
    };

    $indexUrl = $currentNewsLocale === 'en'
        ? route('news.index')
        : route('news.index.localized', ['locale' => $currentNewsLocale]);

    $allowedLocales = config(
        'awcms.theme_locales.school-of-signals',
        ['en', 'si'],
    );

    $allowedLocales = is_array($allowedLocales)
        ? $allowedLocales
        : ['en', 'si'];

    $description = $news->seo_description ?: ($news->summary ?: $news->title);
@endphp

@section('title', ($news->seo_title ?: $news->title).' | '.config('app.name'))
@section('description', $description)
@section('meta_description', $description)
@section('canonical', $articleUrl($news))

@section('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $news->seo_title ?: $news->title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $articleUrl($news) }}">

    @if ($featuredImageUrl)
        <meta property="og:image" content="{{ $featuredImageUrl }}">
    @endif
@endsection

@section('content')
    <div class="sos-article-page">
        <article>
            <header class="sos-article-header">
                <div class="sos-article-container">
                    <div class="sos-article-toolbar">
                        <a class="sos-article-back" href="{{ $indexUrl }}">
                            ← All News
                        </a>
                    </div>

                    <div class="sos-article-meta">
                        @if ($news->category)
                            <span class="sos-article-category">
                                {{ $news->category->name }}
                            </span>
                        @endif

                        @if ($news->is_featured)
                            <span class="sos-article-featured">Featured</span>
                        @endif

                        @if ($news->published_at)
                            <time datetime="{{ $news->published_at->toIso8601String() }}">
                                {{ $news->published_at->format('d M Y H:i') }}
                            </time>
                        @endif
                    </div>

                    <h1 class="sos-article-title">{{ $news->title }}</h1>

                    @if ($news->summary)
                        <p class="sos-article-summary">{{ $news->summary }}</p>
                    @endif
                </div>
            </header>

            @if ($safeContent !== '')
                <div class="sos-article-body">
                    <div class="sos-article-container">
                        <div @class([
                            'sos-article-content',
                            'awcms-content' => $news->editor_mode === \App\Enums\NewsEditorMode::Visual,
                            'page-html-content' => $news->editor_mode !== \App\Enums\NewsEditorMode::Visual,
                        ])>
                            {!! $safeContent !!}
                        </div>
                    </div>
                </div>
            @endif

            @if ($news->images->isNotEmpty())
                <section class="sos-article-gallery-section" aria-labelledby="article-gallery-title">
                    <div class="sos-article-container">
                        <h2 id="article-gallery-title">Event Photographs</h2>

                        <div class="sos-article-gallery">
                            @foreach ($news->images as $newsImage)
                                @php
                                    $galleryImageUrl = \App\Http\Controllers\PublicNewsController::imageUrl(
                                        $newsImage->media,
                                    );
                                @endphp

                                @if ($galleryImageUrl)
                                    <figure class="sos-article-photo">
                                        <a
                                            href="{{ $galleryImageUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <img
                                                src="{{ $galleryImageUrl }}"
                                                alt="{{ $newsImage->media?->alt_text ?: $news->title }}"
                                                loading="lazy"
                                            >
                                        </a>
                                    </figure>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </article>

        @if ($relatedNews->isNotEmpty())
            <section class="sos-article-related" aria-labelledby="related-articles-title">
                <div class="sos-article-container">
                    <h2 id="related-articles-title">Related News</h2>

                    <div class="sos-article-related-grid">
                        @foreach ($relatedNews as $related)
                            <article class="sos-article-related-card">
                                @if ($related->published_at)
                                    <time datetime="{{ $related->published_at->toIso8601String() }}">
                                        {{ $related->published_at->format('d M Y') }}
                                    </time>
                                @endif

                                <h3>
                                    <a href="{{ $articleUrl($related) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>

                                <a class="sos-article-back" href="{{ $articleUrl($related) }}">
                                    Read Article →
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
@extends('theme-school-of-signals::layout')

@php
    $currentNewsLocale = $locale->value;

    $publicNewsIndexUrl =
        $currentNewsLocale === 'en'
            ? route('news.index')
            : route('news.index.localized', ['locale' => $currentNewsLocale]);

    $publicNewsUrl = static function (\App\Models\News $article) use ($currentNewsLocale): string {
        return $currentNewsLocale === 'en'
            ? route('news.show', ['slug' => $article->slug])
            : route('news.show.localized', [
                'locale' => $currentNewsLocale,
                'slug' => $article->slug,
            ]);
    };
@endphp

@section('title', __('public.news') . ' | ' . config('app.name'))
@section('description', __('public.news_page.description'))
@section('meta_description', __('public.news_page.description'))
@section('canonical', $publicNewsIndexUrl)

@section('content')
    <div class="school-news-index-page">
        <header class="school-news-index-header">
            <div class="school-news-container school-news-index-header-inner">
                <div>
                    <span class="school-news-section-kicker">{{ __('theme-school-of-signals::home.news.kicker') }}</span>
                    <h1>{{ __('public.news') }}</h1>
                    <p>{{ __('public.news_page.intro') }}</p>
                </div>
            </div>
        </header>

        <section class="school-news-listing">
            <div class="school-news-container">
                @if ($news->isEmpty())
                    <div class="school-news-empty">
                        <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                        <h2>{{ __('public.news_page.empty_title') }}</h2>
                        <p>{{ __('public.news_page.empty', ['language' => $locale->nativeLabel()]) }}</p>
                    </div>
                @else
                    <div class="school-news-card-grid">
                        @foreach ($news as $article)
                            @php
                                $imageUrl = \App\Http\Controllers\PublicNewsController::imageUrl(
                                    $article->featuredImage,
                                );
                            @endphp

                            <article class="school-news-card">
                                @if (is_string($imageUrl) && $imageUrl !== '')
                                    <a href="{{ $publicNewsUrl($article) }}" class="school-news-card-image"
                                        aria-label="{{ __('public.news_page.read', ['title' => $article->title]) }}">
                                        <img src="{{ $imageUrl }}"
                                            alt="{{ $article->featuredImage?->alt_text ?: $article->title }}"
                                            loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $publicNewsUrl($article) }}"
                                        class="school-news-card-image school-news-card-placeholder"
                                        aria-label="{{ __('public.news_page.read', ['title' => $article->title]) }}">
                                        <i class="fa-regular fa-image" aria-hidden="true"></i>
                                    </a>
                                @endif

                                <div class="school-news-card-content">
                                    <div class="school-news-card-badges">
                                        @if ($article->category)
                                            <span class="school-news-category">
                                                {{ $article->category->name }}
                                            </span>
                                        @endif

                                        @if ($article->is_featured)
                                            <span class="school-news-featured">
                                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                                                {{ __('public.news_page.featured') }}
                                            </span>
                                        @endif
                                    </div>

                                    <h2>
                                        <a href="{{ $publicNewsUrl($article) }}">
                                            {{ $article->title }}
                                        </a>
                                    </h2>

                                    @if ($article->summary)
                                        <p>{{ $article->summary }}</p>
                                    @endif

                                    <div class="school-news-card-footer">
                                        @if ($article->published_at)
                                            <time datetime="{{ $article->published_at->toIso8601String() }}">
                                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                {{ $article->published_at->format('d M Y') }}
                                            </time>
                                        @endif

                                        <a href="{{ $publicNewsUrl($article) }}">
                                            {{ __('theme-school-of-signals::home.news.read_more') }}
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($news->hasPages())
                        <div class="school-news-pagination">
                            {{ $news->onEachSide(1)->links('public.news.pagination') }}
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </div>
@endsection

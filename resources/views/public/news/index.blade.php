@extends('layouts.public')

@php
    $currentNewsLocale = $locale->value;

    $publicNewsIndexUrl = $currentNewsLocale === 'en'
        ? route('news.index')
        : route('news.index.localized', [
            'locale' => $currentNewsLocale,
        ]);

    $publicNewsUrl = static function (\App\Models\News $article) use ($currentNewsLocale): string {
        return $currentNewsLocale === 'en'
            ? route('news.show', ['slug' => $article->slug])
            : route('news.show.localized', [
                'locale' => $currentNewsLocale,
                'slug' => $article->slug,
            ]);
    };
@endphp

@section('title', 'News | '.config('app.name'))
@section('description', 'Latest published news and official updates.')
@section('canonical', $publicNewsIndexUrl)

@section('content')
    <section class="border-b border-zinc-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">Latest Updates</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-zinc-950 sm:text-4xl">News</h1>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-zinc-600">Latest published news, announcements and official updates.</p>
                </div>

                <nav class="flex flex-wrap gap-2" aria-label="News language">
                    @foreach ($locales as $localeOption)
                        <a
                            href="{{ route('news.index.localized', ['locale' => $localeOption->value]) }}"
                            @class([
                                'rounded-full px-4 py-2 text-sm font-semibold transition',
                                'bg-emerald-700 text-white' => $localeOption === $locale,
                                'border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50' => $localeOption !== $locale,
                            ])
                        >
                            {{ $localeOption->nativeLabel() }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($news->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-16 text-center">
                <p class="font-semibold text-zinc-700">No published news is available in {{ $locale->nativeLabel() }}.</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($news as $article)
                    @php
                        $imageUrl = \App\Http\Controllers\PublicNewsController::imageUrl($article->featuredImage);
                    @endphp

                    <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                        @if ($imageUrl)
                            <a href="{{ $publicNewsUrl($article) }}" class="block aspect-[16/9] overflow-hidden bg-zinc-100">
                                <img src="{{ $imageUrl }}" alt="{{ $article->featuredImage?->alt_text ?: $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 hover:scale-[1.02]">
                            </a>
                        @endif

                        <div class="p-6">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @if ($article->category)
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 font-semibold text-sky-700">{{ $article->category->name }}</span>
                                @endif
                                @if ($article->is_featured)
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-700">Featured</span>
                                @endif
                            </div>

                            <h2 class="mt-4 text-xl font-bold leading-7 text-zinc-950">
                                <a href="{{ $publicNewsUrl($article) }}" class="hover:text-emerald-700">{{ $article->title }}</a>
                            </h2>

                            @if ($article->summary)
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-600">{{ $article->summary }}</p>
                            @endif

                            <div class="mt-5 flex items-center justify-between gap-3 border-t border-zinc-100 pt-4">
                                <span class="text-xs text-zinc-500">{{ $article->published_at?->format('d M Y') }}</span>
                                <a href="{{ $publicNewsUrl($article) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Read more →</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($news->hasPages())
                <div class="mt-10">{{ $news->links() }}</div>
            @endif
        @endif
    </section>
@endsection

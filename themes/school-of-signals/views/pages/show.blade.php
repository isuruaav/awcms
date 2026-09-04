@extends('theme-school-of-signals::layout')

@section('title', $pageTitle)
@section('meta_description', $metaDescription)

@if (is_string($canonicalUrl) && $canonicalUrl !== '')
    @section('canonical', $canonicalUrl)
@endif

@section('meta')
    <meta name="robots" content="{{ $robots }}">

    @foreach ($languageVersions as $languageVersion)
        @if ($languageVersion['available'] && is_string($languageVersion['url']))
            <link rel="alternate" hreflang="{{ $languageVersion['code'] }}" href="{{ $languageVersion['url'] }}">
        @endif
    @endforeach

    @if ($socialMetadata)
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ $ogUrl }}">

        @if (is_string($ogImage) && $ogImage !== '')
            <meta property="og:image" content="{{ $ogImage }}">
        @endif

        <meta name="twitter:card" content="{{ $twitterCard }}">
        <meta name="twitter:title" content="{{ $twitterTitle }}">
        <meta name="twitter:description" content="{{ $twitterDescription }}">

        @if (is_string($twitterImage) && $twitterImage !== '')
            <meta name="twitter:image" content="{{ $twitterImage }}">
        @endif
    @endif
@endsection

@section('content')
    <div class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Page language</p>

            <nav class="flex flex-wrap items-center gap-2" aria-label="Page languages">
                @foreach ($languageVersions as $languageVersion)
                    @if ($languageVersion['available'] && is_string($languageVersion['url']))
                        <a
                            href="{{ $languageVersion['url'] }}"
                            hreflang="{{ $languageVersion['code'] }}"
                            @class([
                                'rounded-lg border px-3 py-1.5 text-xs font-bold transition',
                                'border-emerald-700 bg-emerald-700 text-white' => $languageVersion['active'],
                                'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50' => ! $languageVersion['active'],
                            ])
                        >
                            {{ $languageVersion['native_label'] }}
                        </a>
                    @else
                        <span
                            class="cursor-not-allowed rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-1.5 text-xs font-bold text-zinc-400"
                            title="This language version has not been published yet."
                        >
                            {{ $languageVersion['native_label'] }}
                        </span>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>

    <article class="theme-page">
        @if ($page->show_title || $page->excerpt)
            <header class="section section-soft">
                <div class="container">
                    <p class="kicker">{{ $page->locale->value === 'en' ? 'School Information' : $page->locale->label() }}</p>

                    @if ($page->show_title)
                        <h1 class="title-lg">{{ $page->title }}</h1>
                    @endif

                    @if ($page->excerpt)
                        <p class="lead">{{ $page->excerpt }}</p>
                    @endif
                </div>
            </header>
        @endif

        @if ($safeContent !== '')
            @if ($page->getRawOriginal('editor_mode') === \App\Enums\PageEditorMode::Visual->value)
                <section class="section white">
                    <div class="container">
                        <div class="awcms-content">
                            {!! $safeContent !!}
                        </div>
                    </div>
                </section>
            @else
                <div class="page-html-content">
                    {!! $safeContent !!}
                </div>
            @endif
        @endif

        {{-- Existing page blocks remain public and read-only; the builder UI stays retired. --}}
        @if ($pageBlocks !== [])
            <section class="section white">
                <div class="container">
                    <x-page.blocks :blocks="$pageBlocks" />
                </div>
            </section>
        @endif
    </article>
@endsection

@extends('layouts.public')

@section('title', $pageTitle)
@section('description', $metaDescription)

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
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                Page language
            </p>

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

    <article>
        @if ($page->show_title || $page->excerpt)
            <header class="mx-auto max-w-5xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="border-b border-zinc-200 pb-8">
                    @if ($page->show_title)
                        <h1 class="text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl lg:text-5xl">
                            {{ $page->title }}
                        </h1>
                    @endif

                    @if ($page->excerpt)
                        <p class="{{ $page->show_title ? 'mt-5 ' : '' }}max-w-3xl text-lg leading-8 text-zinc-600">
                            {{ $page->excerpt }}
                        </p>
                    @endif
                </div>
            </header>
        @endif

        @if ($safeContent !== '')
            @if ($page->getRawOriginal('editor_mode') === \App\Enums\PageEditorMode::Visual->value)
                <div
                    @class([
                        'mx-auto max-w-5xl px-5 sm:px-6 lg:px-8',
                        'pb-12 pt-4 lg:pb-16 lg:pt-6' => $page->show_title || $page->excerpt,
                        'py-12 lg:py-16' => ! ($page->show_title || $page->excerpt),
                    ])
                >
                    <div class="awcms-content">
                        {!! $safeContent !!}
                    </div>
                </div>
            @else
                <div class="page-html-content">
                    {!! $safeContent !!}
                </div>
            @endif
        @endif

        {{-- Legacy page blocks remain renderable for existing content, but the builder UI is retired. --}}
        @if ($pageBlocks !== [])
            <div class="mx-auto max-w-5xl px-5 pb-12 sm:px-6 lg:px-8 lg:pb-16">
                <x-page.blocks :blocks="$pageBlocks" />
            </div>
        @endif
    </article>
@endsection

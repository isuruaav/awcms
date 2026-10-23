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

@push('styles')
    <x-page.custom-styles :page="$page" />
@endpush

@section('content')
    <article>
        @if ($page->show_title || $page->excerpt)
            <header class="mx-auto max-w-5xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
                @if ($page->show_title)
                    <h1 class="text-3xl font-bold text-zinc-950 sm:text-4xl">{{ $page->title }}</h1>
                @endif
                @if ($page->excerpt)
                    <p class="mt-5 text-lg leading-8 text-zinc-600">{{ $page->excerpt }}</p>
                @endif
            </header>
        @endif

        @if ($safeContent !== '')
            @if ($page->getRawOriginal('editor_mode') === \App\Enums\PageEditorMode::Visual->value)
                <div id="cms-page-{{ $page->getKey() }}" class="mx-auto max-w-5xl px-5 py-12 sm:px-6 lg:px-8">
                    <div class="awcms-content">{!! $safeContent !!}</div>
                </div>
            @else
                <div id="cms-page-{{ $page->getKey() }}" class="page-html-content">{!! $safeContent !!}</div>
            @endif
        @endif

        @if ($pageBlocks !== [])
            <div class="mx-auto max-w-5xl px-5 pb-12 sm:px-6 lg:px-8">
                <x-page.blocks :blocks="$pageBlocks" />
            </div>
        @endif
    </article>
@endsection

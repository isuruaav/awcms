@extends('layouts.public')

@section('title', $pageTitle)
@section('description', $metaDescription)

@if (is_string($canonicalUrl) && $canonicalUrl !== '')
    @section('canonical', $canonicalUrl)
@endif

@section('meta')
    <meta name="robots" content="{{ $robots }}">

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
    <article
        class="mx-auto max-w-5xl
               px-5 py-12
               sm:px-6
               lg:px-8 lg:py-16"
    >
        {{-- Page Header --}}
        <header
            class="border-b
                   border-zinc-200
                   pb-8"
        >
            <h1
                class="text-3xl font-bold
                       tracking-tight
                       text-zinc-950
                       sm:text-4xl
                       lg:text-5xl"
            >
                {{ $page->title }}
            </h1>

            @if ($page->excerpt)
                <p
                    class="mt-5 max-w-3xl
                           text-lg leading-8
                           text-zinc-600"
                >
                    {{ $page->excerpt }}
                </p>
            @endif
        </header>

        {{-- Legacy / Main Rich Text --}}
        @if ($safeContent !== '')
            <div
                class="trix-content
                       awcms-content
                       mt-10"
            >
                {!! $safeContent !!}
            </div>
        @endif

        {{-- Structured Page Builder --}}
        <x-page.blocks
            :blocks="$pageBlocks"
        />
    </article>
@endsection
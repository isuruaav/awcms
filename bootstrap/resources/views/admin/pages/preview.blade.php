@extends('layouts.public')

@section('title', $pageTitle)
@section('description', $metaDescription)

@section('meta')
    <meta name="robots" content="{{ $robots }}">
@endsection

@section('content')
    <div class="border-b border-amber-200 bg-amber-50">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-bold text-amber-900">
                    Administrator Preview
                </p>

                <p class="mt-1 text-xs text-amber-700">
                    This is a preview of the current page content.
                </p>
            </div>

            <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-800">
                {{ $page->status->label() }}
            </span>
        </div>
    </div>

    <article>
        <header class="mx-auto max-w-5xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="border-b border-zinc-200 pb-8">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl lg:text-5xl">
                    {{ $page->title }}
                </h1>

                @if ($page->excerpt)
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-zinc-600">
                        {{ $page->excerpt }}
                    </p>
                @endif
            </div>
        </header>

        @if ($safeContent !== '')
            <div class="page-html-content">
                {!! $safeContent !!}
            </div>
        @endif

        {{-- Legacy page blocks remain visible in previews for existing pages. --}}
        @if ($pageBlocks !== [])
            <div class="mx-auto max-w-5xl px-5 pb-12 sm:px-6 lg:px-8 lg:pb-16">
                <x-page.blocks :blocks="$pageBlocks" />
            </div>
        @endif
    </article>
@endsection

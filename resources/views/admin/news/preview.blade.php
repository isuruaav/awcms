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
                <p class="text-sm font-bold text-amber-900">Administrator Preview</p>
                <p class="mt-1 text-xs text-amber-700">This is a preview of the current news article.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-800">
                    {{ $news->locale->nativeLabel() }}
                </span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-800">
                    {{ $news->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <article>
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto max-w-4xl px-5 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-3 text-sm text-zinc-500">
                    @if ($news->category)
                        <span class="rounded-full bg-sky-50 px-3 py-1 font-semibold text-sky-700">{{ $news->category->name }}</span>
                    @endif

                    @if ($news->is_featured)
                        <span class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-700">Featured</span>
                    @endif
                </div>

                <h1 class="mt-5 text-3xl font-black tracking-tight text-zinc-950 sm:text-4xl lg:text-5xl">
                    {{ $news->title }}
                </h1>

                @if ($news->summary)
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-zinc-600">{{ $news->summary }}</p>
                @endif
            </div>
        </header>

        @if ($featuredImageUrl)
            <div class="mx-auto max-w-5xl px-5 pt-10 sm:px-6 lg:px-8">
                <img src="{{ $featuredImageUrl }}" alt="{{ $news->featuredImage?->alt_text ?: $news->title }}" class="max-h-[620px] w-full rounded-2xl object-cover shadow-sm">
            </div>
        @endif

        @if ($safeContent !== '')
            @if ($news->editor_mode === \App\Enums\NewsEditorMode::Visual)
                <div class="mx-auto max-w-4xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">
                    <div class="awcms-content">{!! $safeContent !!}</div>
                </div>
            @else
                <div class="page-html-content">{!! $safeContent !!}</div>
            @endif
        @endif

        @if ($news->images->isNotEmpty())
            <section class="mx-auto max-w-6xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-16">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($news->images as $newsImage)
                        @php
                            $galleryImageUrl = \App\Http\Controllers\PublicNewsController::imageUrl(
                                $newsImage->media,
                            );

                            $galleryAlt = $newsImage->media?->alt_text ?: $news->title;
                        @endphp

                        @if ($galleryImageUrl)
                            <figure class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                                <a
                                    href="{{ $galleryImageUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="block aspect-[4/3] overflow-hidden bg-zinc-100"
                                >
                                    <img
                                        src="{{ $galleryImageUrl }}"
                                        alt="{{ $galleryAlt }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition duration-300 hover:scale-105"
                                    >
                                </a>
                            </figure>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection

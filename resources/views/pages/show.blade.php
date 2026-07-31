@extends('layouts.public')

@section('content')
    <article class="mx-auto max-w-4xl px-5 py-12
               sm:px-6 sm:py-16 lg:px-8">
        <nav aria-label="Breadcrumb" class="mb-7 text-sm text-zinc-500">
            <a href="{{ route('home') }}" class="font-medium hover:text-emerald-700">
                Home
            </a>

            <span class="mx-2" aria-hidden="true">
                /
            </span>

            <span aria-current="page">
                {{ $page->title }}
            </span>
        </nav>

        <header class="border-b border-zinc-200 pb-8">
            <h1 class="text-3xl font-bold tracking-tight
                       text-zinc-950 sm:text-4xl">
                {{ $page->title }}
            </h1>

            @if ($page->excerpt)
                <p class="mt-5 text-lg leading-8
                           text-zinc-600">
                    {{ $page->excerpt }}
                </p>
            @endif

            @if ($page->published_at)
                <p class="mt-5 text-sm text-zinc-500">
                    Published
                    <time datetime="{{ $page->published_at->toAtomString() }}">
                        {{ $page->published_at->format('F j, Y') }}
                    </time>
                </p>
            @endif
        </header>

        @if ($safeContent !== '')
            <div class="trix-content awcms-content mt-8">
                {!! $safeContent !!}
            </div>
        @else
            <p class="mt-8 text-zinc-500">
                No content is available for this page.
            </p>
        @endif
    </article>
@endsection

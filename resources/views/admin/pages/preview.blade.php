@extends('layouts.public')

@section('content')
    <div class="border-b border-amber-200
               bg-amber-50">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-3
                   px-5 py-4 sm:flex-row sm:items-center
                   sm:justify-between sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-bold
                           text-amber-950">
                    Administrative preview
                </p>

                <p class="mt-1 text-sm
                           text-amber-800">
                    This preview is not publicly indexed.
                    Current status:
                    {{ $page->status->label() }}
                </p>
            </div>

            <a href="{{ route('admin.pages.index') }}"
                class="inline-flex items-center justify-center
                       rounded-lg border border-amber-300
                       bg-white px-4 py-2 text-sm
                       font-semibold text-amber-900
                       hover:bg-amber-100">
                Back to Pages
            </a>
        </div>
    </div>

    <article class="mx-auto max-w-4xl px-5 py-12
               sm:px-6 sm:py-16 lg:px-8">
        <header class="border-b border-zinc-200 pb-8">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight
                           text-zinc-950 sm:text-4xl">
                    {{ $page->title }}
                </h1>

                <span
                    class="inline-flex rounded-full
                           bg-zinc-100 px-3 py-1
                           text-xs font-semibold text-zinc-700">
                    {{ $page->status->label() }}
                </span>
            </div>

            <p class="mt-3 text-sm text-zinc-500">
                URL:
                <span class="font-mono">
                    /pages/{{ $page->slug }}
                </span>
            </p>

            @if ($page->excerpt)
                <p class="mt-5 text-lg leading-8
                           text-zinc-600">
                    {{ $page->excerpt }}
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

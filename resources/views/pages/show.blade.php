@extends('layouts.public')

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
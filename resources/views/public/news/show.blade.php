@extends('layouts.public')

@section(
    'title',
    ($news->seo_title ?: $news->title)
    .' | '
    .config('app.name')
)

@section(
    'description',
    $news->seo_description
        ?: ($news->summary ?: $news->title)
)

@section(
    'canonical',
    route(
        'news.show',
        [
            'slug' => $news->slug,
        ]
    )
)

@section('meta')

    <meta
        property="og:type"
        content="article"
    >

    <meta
        property="og:title"
        content="{{ $news->seo_title ?: $news->title }}"
    >

    <meta
        property="og:description"
        content="{{ $news->seo_description
            ?: ($news->summary ?: $news->title) }}"
    >

    <meta
        property="og:url"
        content="{{ route(
            'news.show',
            [
                'slug' => $news->slug,
            ]
        ) }}"
    >

    @if ($featuredImageUrl)
        <meta
            property="og:image"
            content="{{ $featuredImageUrl }}"
        >
    @endif

@endsection


@section('content')

    <article>
        <header
            class="border-b
                   border-zinc-200
                   bg-white"
        >
            <div
                class="mx-auto
                       max-w-4xl
                       px-4
                       py-12
                       sm:px-6
                       lg:px-8"
            >
                <a
                    href="{{ route('news.index') }}"
                    class="text-sm
                           font-semibold
                           text-emerald-700
                           hover:text-emerald-800"
                >
                    ← All News
                </a>

                <div
                    class="mt-6
                           flex
                           flex-wrap
                           items-center
                           gap-3
                           text-sm
                           text-zinc-500"
                >
                    @if ($news->category)
                        <span
                            class="rounded-full
                                   bg-emerald-50
                                   px-3
                                   py-1
                                   text-xs
                                   font-bold
                                   text-emerald-700"
                        >
                            {{ $news->category->name }}
                        </span>
                    @endif

                    @if ($news->published_at)
                        <time
                            datetime="{{ $news
                                ->published_at
                                ->toAtomString() }}"
                        >
                            {{ $news
                                ->published_at
                                ->format(
                                    'd F Y, H:i'
                                ) }}
                        </time>
                    @endif

                    @if ($news->is_featured)
                        <span
                            class="rounded-full
                                   bg-amber-50
                                   px-3
                                   py-1
                                   text-xs
                                   font-bold
                                   text-amber-700"
                        >
                            Featured
                        </span>
                    @endif
                </div>

                <h1
                    class="mt-5
                           text-3xl
                           font-black
                           leading-tight
                           tracking-tight
                           text-zinc-950
                           sm:text-5xl"
                >
                    {{ $news->title }}
                </h1>

                @if ($news->summary)
                    <p
                        class="mt-5
                               text-lg
                               leading-8
                               text-zinc-600"
                    >
                        {{ $news->summary }}
                    </p>
                @endif
            </div>
        </header>


        <div
            class="mx-auto
                   max-w-4xl
                   px-4
                   py-10
                   sm:px-6
                   lg:px-8"
        >
            @if ($featuredImageUrl)
                <figure
                    class="mb-10
                           overflow-hidden
                           rounded-2xl
                           bg-zinc-100
                           shadow-sm"
                >
                    <img
                        src="{{ $featuredImageUrl }}"
                        alt="{{ $news->title }}"
                        class="h-auto
                               w-full
                               object-cover"
                    >

                    @if ($news->featuredImage?->caption)
                        <figcaption
                            class="border-t
                                   border-zinc-200
                                   bg-white
                                   px-4 py-3
                                   text-sm
                                   text-zinc-500"
                        >
                            {{ $news->featuredImage->caption }}
                        </figcaption>
                    @endif
                </figure>
            @endif

            <div
                class="awcms-content
                       text-base
                       leading-8
                       text-zinc-800"
            >
                {!! $news->content !!}
            </div>
        </div>
    </article>


    @if ($relatedNews->isNotEmpty())
        <section
            class="border-t
                   border-zinc-200
                   bg-white"
        >
            <div
                class="mx-auto
                       max-w-7xl
                       px-4
                       py-12
                       sm:px-6
                       lg:px-8"
            >
                <h2
                    class="text-2xl
                           font-black
                           text-zinc-950"
                >
                    Related News
                </h2>

                <div
                    class="mt-6
                           grid gap-5
                           md:grid-cols-3"
                >
                    @foreach ($relatedNews as $related)

                        @php
                            $relatedImageUrl =
                                \App\Http\Controllers\PublicNewsController::imageUrl(
                                    $related->featuredImage
                                );
                        @endphp

                        <article
                            class="overflow-hidden
                                   rounded-2xl
                                   border
                                   border-zinc-200
                                   bg-zinc-50"
                        >
                            @if ($relatedImageUrl)
                                <a
                                    href="{{ route(
                                        'news.show',
                                        [
                                            'slug' =>
                                                $related->slug,
                                        ]
                                    ) }}"
                                    class="block
                                           aspect-[16/9]
                                           overflow-hidden"
                                >
                                    <img
                                        src="{{ $relatedImageUrl }}"
                                        alt="{{ $related->title }}"
                                        loading="lazy"
                                        class="h-full
                                               w-full
                                               object-cover"
                                    >
                                </a>
                            @endif

                            <div class="p-5">
                                <h3
                                    class="font-bold
                                           leading-6
                                           text-zinc-900"
                                >
                                    <a
                                        href="{{ route(
                                            'news.show',
                                            [
                                                'slug' =>
                                                    $related->slug,
                                            ]
                                        ) }}"
                                        class="hover:text-emerald-700"
                                    >
                                        {{ $related->title }}
                                    </a>
                                </h3>

                                @if ($related->published_at)
                                    <p
                                        class="mt-2
                                               text-xs
                                               text-zinc-500"
                                    >
                                        {{ $related
                                            ->published_at
                                            ->format(
                                                'd M Y'
                                            ) }}
                                    </p>
                                @endif
                            </div>
                        </article>

                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
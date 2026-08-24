@extends('layouts.public')

@section(
    'title',
    'News | '.config('app.name')
)

@section(
    'description',
    'Latest published news and official updates.'
)

@section(
    'canonical',
    route('news.index')
)

@section('content')

    <section
        class="border-b
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
            <p
                class="text-sm
                       font-bold
                       uppercase
                       tracking-[0.18em]
                       text-emerald-700"
            >
                Latest Updates
            </p>

            <h1
                class="mt-2
                       text-3xl
                       font-black
                       tracking-tight
                       text-zinc-950
                       sm:text-4xl"
            >
                News
            </h1>

            <p
                class="mt-3
                       max-w-2xl
                       text-base
                       leading-7
                       text-zinc-600"
            >
                Latest published news,
                announcements and official updates.
            </p>
        </div>
    </section>


    <section
        class="mx-auto
               max-w-7xl
               px-4
               py-10
               sm:px-6
               lg:px-8"
    >
        @if ($news->isEmpty())

            <div
                class="rounded-2xl
                       border
                       border-zinc-200
                       bg-white
                       px-6
                       py-16
                       text-center
                       shadow-sm"
            >
                <h2
                    class="text-xl
                           font-bold
                           text-zinc-900"
                >
                    No published news
                </h2>

                <p
                    class="mt-2
                           text-sm
                           text-zinc-500"
                >
                    Published articles will appear here.
                </p>
            </div>

        @else

            <div
                class="grid gap-6
                       md:grid-cols-2
                       xl:grid-cols-3"
            >
                @foreach ($news as $article)

                    @php
                        $imageUrl =
                            \App\Http\Controllers\PublicNewsController::imageUrl(
                                $article->featuredImage
                            );
                    @endphp

                    <article
                        class="group
                               overflow-hidden
                               rounded-2xl
                               border
                               border-zinc-200
                               bg-white
                               shadow-sm
                               transition
                               hover:-translate-y-0.5
                               hover:shadow-md"
                    >
                        <a
                            href="{{ route(
                                'news.show',
                                [
                                    'slug' =>
                                        $article->slug,
                                ]
                            ) }}"
                            class="block"
                        >
                            @if ($imageUrl)
                                <div
                                    class="aspect-[16/9]
                                           overflow-hidden
                                           bg-zinc-100"
                                >
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $article->title }}"
                                        loading="lazy"
                                        class="h-full
                                               w-full
                                               object-cover
                                               transition
                                               duration-300
                                               group-hover:scale-[1.02]"
                                    >
                                </div>
                            @else
                                <div
                                    class="flex
                                           aspect-[16/9]
                                           items-center
                                           justify-center
                                           bg-zinc-100
                                           px-6
                                           text-center"
                                >
                                    <span
                                        class="text-sm
                                               font-semibold
                                               text-zinc-400"
                                    >
                                        {{ config('app.name') }}
                                    </span>
                                </div>
                            @endif
                        </a>

                        <div class="p-5">

                            <div
                                class="flex
                                       flex-wrap
                                       items-center
                                       gap-2
                                       text-xs
                                       font-semibold
                                       text-zinc-500"
                            >
                                @if ($article->category)
                                    <span
                                        class="rounded-full
                                               bg-emerald-50
                                               px-2.5
                                               py-1
                                               text-emerald-700"
                                    >
                                        {{ $article->category->name }}
                                    </span>
                                @endif

                                @if ($article->published_at)
                                    <time
                                        datetime="{{ $article
                                            ->published_at
                                            ->toAtomString() }}"
                                    >
                                        {{ $article
                                            ->published_at
                                            ->format(
                                                'd M Y'
                                            ) }}
                                    </time>
                                @endif
                            </div>

                            <h2
                                class="mt-4
                                       text-xl
                                       font-bold
                                       leading-7
                                       text-zinc-950"
                            >
                                <a
                                    href="{{ route(
                                        'news.show',
                                        [
                                            'slug' =>
                                                $article->slug,
                                        ]
                                    ) }}"
                                    class="transition
                                           hover:text-emerald-700"
                                >
                                    {{ $article->title }}
                                </a>
                            </h2>

                            @if ($article->summary)
                                <p
                                    class="mt-3
                                           line-clamp-3
                                           text-sm
                                           leading-6
                                           text-zinc-600"
                                >
                                    {{ $article->summary }}
                                </p>
                            @endif

                            <a
                                href="{{ route(
                                    'news.show',
                                    [
                                        'slug' =>
                                            $article->slug,
                                    ]
                                ) }}"
                                class="mt-5
                                       inline-flex
                                       text-sm
                                       font-bold
                                       text-emerald-700
                                       hover:text-emerald-800"
                            >
                                Read article →
                            </a>
                        </div>
                    </article>

                @endforeach
            </div>

            <div class="mt-10">
                {{ $news->links() }}
            </div>

        @endif
    </section>

@endsection
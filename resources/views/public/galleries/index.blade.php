@php
    $galleryLayout = config('awcms.active_theme') === 'school-of-signals'
        ? 'theme-school-of-signals::layout' : 'layouts.public';
    $feed = app(\App\Services\PublicGalleryFeed::class);
    $isSinhala = app()->getLocale() === 'si';
    $galleryTitle = $isSinhala ? 'ඡායාරූප ගැලරිය' : 'Photo Gallery';
@endphp

@extends($galleryLayout)

@section('title', $galleryTitle.' | '.config('app.name'))
@section('description', $isSinhala ? 'පුවත්, උත්සව සහ ක්‍රියාකාරකම්වල ඡායාරූප එකතුව.' : 'Photographs from our news, events and activities.')
@section('meta_description', $isSinhala ? 'පුවත්, උත්සව සහ ක්‍රියාකාරකම්වල ඡායාරූප එකතුව.' : 'Photographs from our news, events and activities.')
@section('canonical', \App\Services\GalleryLocale::indexUrl(app()->getLocale()))

@section('content')
    @include('public.galleries.styles')

    <div class="min-h-screen bg-zinc-50/50 py-8 md:py-12" lang="{{ app()->getLocale() }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Hero / Header Section -->
            <header class="mb-10 text-center md:mb-14">
                <span class="inline-block rounded-full bg-emerald-50 px-3.5 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                    {{ $isSinhala ? 'අපගේ මතක සටහන්' : 'Our moments' }}
                </span>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl md:text-5xl">
                    {{ $galleryTitle }}
                </h1>
                <p class="mx-auto mt-3 max-w-2xl text-base text-zinc-600 sm:text-lg">
                    {{ $isSinhala ? 'පුවත්, උත්සව සහ ක්‍රියාකාරකම්වල ඡායාරූප එකතුව.' : 'Explore photographs from our news, events and activities.' }}
                </p>
            </header>

            <!-- Main Content Section -->
            <section aria-label="{{ $galleryTitle }}">
                @if ($albums->isNotEmpty())
                    <!-- Gallery Grid -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:gap-8">
                        @foreach ($albums as $album)
                            @php
                                $isNews = $album instanceof \App\Models\News;
                                $albumUrl = $isNews
                                    ? \App\Services\GalleryLocale::newsUrl(app()->getLocale(), $album->id)
                                    : \App\Services\GalleryLocale::showUrl(app()->getLocale(), $album->slug);
                                $albumTitle = $isNews ? $album->title : $album->titleForLocale(app()->getLocale());
                                $cover = $isNews ? $album->images->first()?->media : $album->coverMedia;
                                $coverUrl = $feed->imageUrl($cover);
                            @endphp

                            <article class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                <!-- Cover Image / Placeholder -->
                                <a href="{{ $albumUrl }}" aria-label="{{ $albumTitle }}" class="relative aspect-[4/3] w-full overflow-hidden bg-zinc-100">
                                    @if ($coverUrl)
                                        <img src="{{ $coverUrl }}" 
                                             alt="{{ $cover?->alt_text ?: $albumTitle }}" 
                                             loading="lazy" 
                                             decoding="async" 
                                             width="600" 
                                             height="450"
                                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-zinc-100 p-6 text-center">
                                            <span class="text-sm font-semibold text-zinc-400">
                                                {{ $isSinhala ? 'ඡායාරූප එකතුව' : 'View photographs' }}
                                            </span>
                                        </div>
                                    @endif
                                </a>

                                <!-- Card Body -->
                                <div class="flex flex-1 flex-col justify-between p-5">
                                    <div class="space-y-2.5">
                                        <!-- Meta Information -->
                                        <div class="flex items-center justify-between text-xs font-medium text-zinc-500">
                                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-0.5 font-semibold text-zinc-700">
                                                {{ $isNews ? ($isSinhala ? 'පුවත්' : 'News') : ($isSinhala ? 'ගැලරිය' : 'Gallery') }}
                                            </span>
                                            @if ($album->published_at)
                                                <time datetime="{{ $album->published_at->toIso8601String() }}" class="font-mono text-zinc-400">
                                                    {{ $album->published_at->format('d M Y') }}
                                                </time>
                                            @endif
                                        </div>

                                        <!-- Album Title -->
                                        <h2 class="text-lg font-bold text-zinc-900 line-clamp-2 leading-snug group-hover:text-emerald-700 transition-colors">
                                            <a href="{{ $albumUrl }}">
                                                {{ $albumTitle }}
                                            </a>
                                        </h2>
                                    </div>

                                    <!-- Read / View Link -->
                                    <div class="mt-4 pt-3 border-t border-zinc-100">
                                        <a href="{{ $albumUrl }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition-colors">
                                            <span>{{ $isSinhala ? 'ඡායාරූප බලන්න' : 'View album' }}</span>
                                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="mx-auto max-w-md rounded-2xl border border-zinc-200/80 bg-white p-8 text-center shadow-sm my-12">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100">
                            <svg class="h-7 w-7 text-zinc-400" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="5" y="8" width="38" height="32" rx="5"/>
                                <circle cx="16" cy="18" r="4"/>
                                <path d="m7 35 11-10 7 6 7-10 10 14"/>
                            </svg>
                        </div>
                        <h2 class="mt-4 text-lg font-bold text-zinc-900">
                            {{ $isSinhala ? 'දැනට ඡායාරූප එකතු නොමැත' : 'No albums to display' }}
                        </h2>
                        <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                            {{ $isSinhala ? 'මෙම පිටුවේ පෙන්වීමට ඡායාරූප එකතු නොමැත. නව ඡායාරූප සඳහා නැවත පිවිසෙන්න.' : 'There are no albums on this page. Check back soon for new photographs.' }}
                        </p>
                        <div class="mt-6">
                            @if ($albums->currentPage() > 1)
                                <a href="{{ \App\Services\GalleryLocale::indexUrl(app()->getLocale()) }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 transition-all">
                                    ← {{ $isSinhala ? 'පළමු පිටුවට යන්න' : 'Go to the first page' }}
                                </a>
                            @else
                                <a href="{{ app()->getLocale() === 'en' ? route('news.index') : route('news.index.localized', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all">
                                    <span>{{ $isSinhala ? 'පුවත් බලන්න' : 'Browse news' }}</span>
                                    →
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if ($albums->hasPages() && $albums->currentPage() <= $albums->lastPage())
                    <div class="mt-10 flex justify-center">
                        {{ $albums->onEachSide(1)->links('public.galleries.pagination') }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
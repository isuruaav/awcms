@extends('layouts.public')

@section('title', $settings?->default_seo_title ?: $settings?->site_name ?: config('app.name'))
@section('description', $settings?->default_seo_description ?: $settings?->site_tagline ?: 'Official website')

@section('content')
    @if($slides->isNotEmpty())
        @php $firstSlide = $slides->first(); $firstImage = $firstSlide?->image ? app(\App\Services\MediaUrlService::class)->mediumOrOriginal($firstSlide->image) : null; @endphp
        <section class="relative overflow-hidden bg-zinc-950 text-white">
            @if($firstImage)<img src="{{ $firstImage }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35">@endif
            <div class="absolute inset-0 bg-gradient-to-r from-zinc-950 via-zinc-950/80 to-zinc-950/30"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32"><div class="max-w-3xl"><p class="text-sm font-bold uppercase tracking-[0.2em]" style="color:var(--site-accent)">{{ $settings?->site_tagline ?: 'Official Website' }}</p><h1 class="mt-4 text-4xl font-black leading-tight sm:text-5xl">{{ $firstSlide?->title }}</h1>@if($firstSlide?->subtitle)<p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-200">{{ $firstSlide->subtitle }}</p>@endif @if($firstSlide?->button_label && $firstSlide?->button_url)<a href="{{ $firstSlide->button_url }}" class="mt-7 inline-flex rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:var(--site-primary)">{{ $firstSlide->button_label }}</a>@endif</div></div>
        </section>
    @else
        <section class="bg-zinc-950 text-white"><div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"><p class="text-sm font-bold uppercase tracking-[0.2em]" style="color:var(--site-accent)">{{ $settings?->site_tagline ?: 'Official Website' }}</p><h1 class="mt-4 max-w-3xl text-4xl font-black sm:text-5xl">{{ $settings?->site_name ?: config('app.name') }}</h1><p class="mt-5 max-w-2xl text-zinc-300">Official information, news, publications and media.</p></div></section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em]" style="color:var(--site-primary)">Latest Updates</p><h2 class="mt-1 text-2xl font-black text-zinc-950">News</h2></div><a href="{{ route('news.index') }}" class="text-sm font-bold" style="color:var(--site-primary)">View all news →</a></div>
        <div class="mt-6 grid gap-5 md:grid-cols-3">@forelse($latestNews as $news)<article class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm"><p class="text-xs font-semibold text-zinc-400">{{ $news->published_at?->format('d M Y') }}</p><h3 class="mt-2 text-lg font-black text-zinc-950"><a href="{{ route('news.show', ['slug' => $news->slug]) }}" class="hover:underline">{{ $news->title }}</a></h3>@if($news->summary)<p class="mt-3 text-sm leading-6 text-zinc-600">{{ \Illuminate\Support\Str::limit($news->summary, 140) }}</p>@endif</article>@empty<p class="text-sm text-zinc-500">No published news yet.</p>@endforelse</div>
    </section>


    @if($settings?->commander_name || $settings?->commander_message)
        @php
            $commanderImageUrl = $settings?->commanderImage
                ? app(\App\Services\MediaUrlService::class)->mediumOrOriginal($settings->commanderImage)
                : null;
        @endphp
        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 rounded-3xl border border-zinc-200 bg-white p-7 shadow-sm md:grid-cols-[220px_1fr] md:p-10">
                @if($commanderImageUrl)
                    <img src="{{ $commanderImageUrl }}" alt="{{ $settings->commander_name }}" class="h-64 w-full rounded-2xl object-cover md:h-full">
                @endif
                <div class="self-center">
                    <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color:var(--site-primary)">Message</p>
                    @if($settings?->commander_name)<h2 class="mt-2 text-2xl font-black text-zinc-950">{{ $settings->commander_name }}</h2>@endif
                    @if($settings?->commander_title)<p class="mt-1 text-sm font-semibold text-zinc-500">{{ $settings->commander_title }}</p>@endif
                    @if($settings?->commander_message)<p class="mt-5 whitespace-pre-line text-sm leading-7 text-zinc-700">{{ $settings->commander_message }}</p>@endif
                </div>
            </div>
        </section>
    @endif

    <section class="bg-white"><div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"><div class="grid gap-10 lg:grid-cols-2"><div><div class="flex items-end justify-between"><h2 class="text-2xl font-black text-zinc-950">Recent Galleries</h2><a href="{{ route('galleries.index') }}" class="text-sm font-bold" style="color:var(--site-primary)">All galleries →</a></div><div class="mt-5 space-y-3">@forelse($latestGalleries as $gallery)<a href="{{ route('galleries.show', ['slug' => $gallery->slug]) }}" class="block rounded-xl border border-zinc-200 p-4 font-bold text-zinc-800 hover:bg-zinc-50">{{ $gallery->title }}</a>@empty<p class="text-sm text-zinc-500">No galleries yet.</p>@endforelse</div></div><div><div class="flex items-end justify-between"><h2 class="text-2xl font-black text-zinc-950">Recent Documents</h2><a href="{{ route('documents.index') }}" class="text-sm font-bold" style="color:var(--site-primary)">All documents →</a></div><div class="mt-5 space-y-3">@forelse($latestDocuments as $document)<a href="{{ route('documents.show', ['slug' => $document->slug]) }}" class="flex items-center justify-between gap-4 rounded-xl border border-zinc-200 p-4 hover:bg-zinc-50"><span class="font-bold text-zinc-800">{{ $document->title }}</span><span class="text-xs text-zinc-400">v{{ $document->current_version }}</span></a>@empty<p class="text-sm text-zinc-500">No documents yet.</p>@endforelse</div></div></div></div></section>
@endsection

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $pageTitle ?? config('app.name') }}
    </title>

    <meta
        name="description"
        content="{{ $metaDescription ?? '' }}"
    >

    <meta
        name="robots"
        content="{{ $robots ?? 'index,follow' }}"
    >

    @if (! empty($canonicalUrl))
        <link
            rel="canonical"
            href="{{ $canonicalUrl }}"
        >
    @endif

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900">
    <header
        class="border-b border-zinc-200 bg-white"
    >
        <div
            class="mx-auto flex max-w-7xl items-center
                   justify-between gap-4 px-5 py-4
                   sm:px-6 lg:px-8"
        >
            <a
                href="{{ route('home') }}"
                class="text-lg font-bold text-emerald-800"
            >
                {{ config('app.name') }}
            </a>

            <a
                href="{{ route('home') }}"
                class="text-sm font-semibold text-zinc-600
                       hover:text-emerald-700"
            >
                Home
            </a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer
        class="mt-16 border-t border-zinc-200 bg-white"
    >
        <div
            class="mx-auto max-w-7xl px-5 py-8
                   text-center text-sm text-zinc-500
                   sm:px-6 lg:px-8"
        >
            &copy; {{ now()->year }}
            {{ config('app.name') }}.
            All rights reserved.
        </div>
    </footer>
</body>
</html>
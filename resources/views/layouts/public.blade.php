<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', config('app.name'))
    </title>

    <meta name="description" content="@yield('description', 'Official website news and information.')">

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    @yield('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 text-zinc-900">

    <header class="border-b
               border-zinc-200
               bg-white">
        <div
            class="mx-auto flex
                   max-w-7xl
                   items-center
                   justify-between
                   gap-6
                   px-4 py-5
                   sm:px-6
                   lg:px-8">
            <a href="{{ route('home') }}"
                class="text-lg
                       font-black
                       tracking-tight
                       text-zinc-950">
                {{ config('app.name') }}
            </a>

            <nav
                class="flex
                       items-center
                       gap-5
                       text-sm
                       font-semibold">
                <a href="{{ route('home') }}" class="text-zinc-600
                           hover:text-zinc-950">
                    Home
                </a>

                <a href="{{ route('news.index') }}"
                    class="text-zinc-600
                           hover:text-zinc-950">
                    News
                </a>

                @auth
                    <a href="{{ route('admin.dashboard') }}"
                        class="text-emerald-700
                               hover:text-emerald-800">
                        Admin
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16
               border-t
               border-zinc-200
               bg-white">
        <div
            class="mx-auto
                   max-w-7xl
                   px-4 py-8
                   text-center
                   text-sm
                   text-zinc-500
                   sm:px-6
                   lg:px-8">
            &copy;
            {{ now()->year }}
            {{ config('app.name') }}.
            All rights reserved.
        </div>
    </footer>

</body>

</html>

@props([
    'title' => 'AWCMS',
])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ $title }} | {{ config('app.name', 'AWCMS') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles
</head>

<body class="min-h-full bg-zinc-100 text-zinc-950 antialiased">
    <div class="min-h-screen lg:flex">

        {{-- Sidebar --}}
        <aside
            class="border-b border-zinc-200 bg-zinc-950 text-white
                   lg:min-h-screen lg:w-72 lg:border-b-0
                   lg:border-r lg:border-zinc-800"
        >
            <div class="flex h-20 items-center px-6">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3"
                >
                    <div
                        class="flex size-10 items-center justify-center
                               rounded-xl bg-emerald-600 font-bold"
                    >
                        A
                    </div>

                    <div>
                        <p class="font-bold tracking-wide">
                            AWCMS
                        </p>

                        <p class="text-xs text-zinc-400">
                            Administration
                        </p>
                    </div>
                </a>
            </div>

            <nav class="space-y-1 px-4 pb-6">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center rounded-xl px-4 py-3
                           text-sm font-medium transition
                           {{ request()->routeIs('admin.dashboard')
                                ? 'bg-emerald-600 text-white'
                                : 'text-zinc-300 hover:bg-zinc-800 hover:text-white' }}"
                >
                    Dashboard
                </a>

                {{-- These modules will be connected later. --}}
                <div class="px-4 pt-6 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                    Content Management
                </div>

                <span class="block rounded-xl px-4 py-3 text-sm text-zinc-500">
                    Pages
                </span>

                <span class="block rounded-xl px-4 py-3 text-sm text-zinc-500">
                    News
                </span>

                <span class="block rounded-xl px-4 py-3 text-sm text-zinc-500">
                    Galleries
                </span>

                <span class="block rounded-xl px-4 py-3 text-sm text-zinc-500">
                    Documents
                </span>
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="min-w-0 flex-1">

            {{-- Top navigation --}}
            <header
                class="flex min-h-20 items-center justify-between
                       border-b border-zinc-200 bg-white px-4
                       shadow-sm sm:px-6 lg:px-8"
            >
                <div>
                    <p class="text-sm text-zinc-500">
                        Secure Administration Panel
                    </p>

                    <h1 class="font-semibold text-zinc-950">
                        {{ $title }}
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-zinc-900">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-zinc-500">
                            {{ auth()->user()->email }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg border border-zinc-300
                                   bg-white px-4 py-2 text-sm font-medium
                                   text-zinc-700 transition
                                   hover:bg-zinc-100"
                        >
                            Sign Out
                        </button>
                    </form>
                </div>
            </header>

            {{-- Page content --}}
            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
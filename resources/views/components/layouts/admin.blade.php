@props([
    'title' => 'Dashboard',
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

    <title>
        {{ $title }} | {{ config('awcms.name', 'AWCMS') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @livewireStyles
</head>

<body
    class="min-h-full bg-zinc-100 text-zinc-950 antialiased"
    x-data="{ sidebarOpen: false }"
>
    {{-- Mobile overlay --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-zinc-950/70 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Mobile sidebar --}}
    <aside
        x-cloak
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-zinc-950
               text-white shadow-2xl lg:hidden"
    >
        <div
            class="flex h-20 items-center justify-between
                   border-b border-zinc-800 px-5"
        >
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
                    <p class="font-bold">
                        {{ config('awcms.name', 'AWCMS') }}
                    </p>

                    <p class="text-xs text-zinc-400">
                        Administration
                    </p>
                </div>
            </a>

            <button
                type="button"
                class="rounded-lg p-2 text-zinc-400
                       hover:bg-zinc-800 hover:text-white"
                @click="sidebarOpen = false"
                aria-label="Close navigation"
            >
                ✕
            </button>
        </div>

        @include('admin.partials.sidebar-navigation')
    </aside>

    <div class="min-h-screen lg:flex">
        {{-- Desktop sidebar --}}
        <aside
            class="hidden min-h-screen w-72 shrink-0
                   bg-zinc-950 text-white lg:block"
        >
            <div
                class="flex h-20 items-center
                       border-b border-zinc-800 px-6"
            >
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
                            {{ config('awcms.name', 'AWCMS') }}
                        </p>

                        <p class="text-xs text-zinc-400">
                            Administration
                        </p>
                    </div>
                </a>
            </div>

            @include('admin.partials.sidebar-navigation')
        </aside>

        {{-- Main content area --}}
        <div class="min-w-0 flex-1">
            <header
                class="sticky top-0 z-30 flex min-h-20
                       items-center justify-between
                       border-b border-zinc-200 bg-white/95
                       px-4 shadow-sm backdrop-blur
                       sm:px-6 lg:px-8"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-zinc-200
                               bg-white p-2.5 text-zinc-700
                               hover:bg-zinc-100 lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Open navigation"
                    >
                        ☰
                    </button>

                    <div class="min-w-0">
                        <p class="text-xs font-medium text-zinc-500">
                            Secure Administration Panel
                        </p>

                        <p
                            class="truncate font-semibold text-zinc-950"
                        >
                            {{ $title }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-zinc-900">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-zinc-500">
                            {{ auth()->user()->roles->pluck('name')->join(', ') }}
                        </p>
                    </div>

                    <div
                        class="flex size-10 items-center justify-center
                               rounded-full bg-emerald-100
                               text-sm font-bold text-emerald-700"
                    >
                        {{ auth()->user()->initials() }}
                    </div>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl border border-zinc-300
                                   bg-white px-3 py-2 text-sm
                                   font-medium text-zinc-700
                                   transition hover:bg-zinc-100"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-7xl">
                    {{ $slot }}
                </div>
            </main>

            <footer
                class="border-t border-zinc-200 bg-white
                       px-4 py-4 sm:px-6 lg:px-8"
            >
                <div
                    class="mx-auto flex max-w-7xl flex-col
                           gap-1 text-xs text-zinc-500
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >
                    <p>
                        © {{ now()->year }}
                        {{ config('awcms.full_name') }}
                    </p>

                    <p>
                        Version {{ config('awcms.version') }}
                    </p>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
</body>
</html>
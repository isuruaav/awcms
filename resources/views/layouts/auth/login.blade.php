<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-[#06101d] font-sans antialiased text-white">
        <main class="relative flex min-h-svh items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
            <div
                class="pointer-events-none absolute inset-0 opacity-40
                       [background-image:linear-gradient(rgba(14,165,233,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(14,165,233,0.07)_1px,transparent_1px)]
                       [background-size:32px_32px]"
                aria-hidden="true"
            ></div>

            <div
                class="pointer-events-none absolute left-1/2 top-1/2
                       h-[520px] w-[520px]
                       -translate-x-1/2 -translate-y-1/2
                       rounded-full bg-cyan-500/10 blur-3xl"
                aria-hidden="true"
            ></div>

            <section
                class="relative z-10 w-full max-w-md
                       rounded-[2rem] border border-cyan-400/25
                       bg-[#070c20]/95 px-6 py-8
                       shadow-[0_30px_90px_rgba(0,0,0,0.55),0_0_40px_rgba(6,182,212,0.08)]
                       backdrop-blur sm:px-10 sm:py-10"
            >
                {{ $slot }}
            </section>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
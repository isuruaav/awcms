<x-layouts::auth.login :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <header class="text-center">
            <a
                href="{{ route('home') }}"
                class="mx-auto flex w-fit flex-col items-center"
                aria-label="{{ config('awcms.name', 'AWCMS') }} home"
            >
                <span
                    class="flex size-20 items-center justify-center
                           rounded-2xl border border-cyan-300/20
                           bg-slate-800/80 p-2
                           shadow-lg shadow-cyan-950/40"
                >
                    <img
                        src="{{ asset('crest.png') }}"
                        alt="{{ config('awcms.name', 'AWCMS') }} crest"
                        class="size-full object-contain"
                    >
                </span>
            </a>

            <h1 class="mt-6 text-2xl font-black tracking-tight text-white sm:text-3xl">
                {{ config('awcms.name', 'AWCMS') }}
            </h1>

            <p class="mt-2 text-sm leading-6 text-slate-400">
                Enter your credentials to access the administration portal
            </p>
        </header>

        <x-auth-session-status
            class="rounded-xl border border-emerald-400/20
                   bg-emerald-400/10 px-4 py-3
                   text-center text-sm text-emerald-200"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="name@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute top-0 text-sm end-0"
                        :href="route('password.request')"
                        wire:navigate
                    >
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox
                name="remember"
                :label="__('Remember me')"
                :checked="old('remember')"
            />

            <button
                type="submit"
                class="inline-flex min-h-12 w-full cursor-pointer
                       items-center justify-center gap-3 rounded-xl
                       bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-600
                       px-5 py-3 text-sm font-black text-slate-950
                       shadow-lg shadow-cyan-950/30 transition
                       hover:brightness-110
                       focus-visible:outline-2
                       focus-visible:outline-offset-2
                       focus-visible:outline-cyan-400"
                data-test="login-button"
            >
                <span>{{ __('Sign In to System') }}</span>

                <svg
                    class="size-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 12h14m-5-5 5 5-5 5"
                    />
                </svg>
            </button>
        </form>

        <div class="flex items-center gap-3" aria-hidden="true">
            <span class="h-px flex-1 bg-white/10"></span>

            <span
                class="text-[10px] font-bold uppercase
                       tracking-[0.2em] text-slate-500"
            >
                Secure Access
            </span>

            <span class="h-px flex-1 bg-white/10"></span>
        </div>

        <x-passkey-verify />

        @if (Route::has('register'))
            <div class="space-x-1 text-center text-sm text-slate-400">
                <span>{{ __('Don\'t have an account?') }}</span>

                <flux:link
                    :href="route('register')"
                    wire:navigate
                >
                    {{ __('Sign up') }}
                </flux:link>
            </div>
        @endif

        <p class="text-center text-xs leading-5 text-slate-500">
            Authorized personnel only. Login activity may be monitored and recorded.
        </p>
    </div>
</x-layouts::auth.login>
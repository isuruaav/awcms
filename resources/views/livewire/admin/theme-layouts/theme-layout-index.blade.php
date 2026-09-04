<div class="space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            Theme Management
        </p>

        <h1 class="mt-1 text-2xl font-black text-zinc-950">
            Header &amp; Footer
        </h1>

        <p class="mt-1 text-sm text-zinc-500">
            Manage separate English, Sinhala and Tamil header and footer layouts
            using sanitized HTML, Tailwind classes, custom CSS and approved CMS placeholders.
        </p>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $themeAvailable)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="font-bold text-amber-950">
                No valid active theme is configured
            </h2>

            <p class="mt-2 text-sm text-amber-800">
                Set
                <code class="rounded bg-amber-100 px-1.5 py-0.5 font-mono">
                    AWCMS_ACTIVE_THEME
                </code>
                to an installed theme slug, then clear the configuration cache.
            </p>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-zinc-200 bg-zinc-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        Active theme
                    </p>

                    <p class="mt-1 font-bold text-zinc-950">
                        {{ $themeName }}

                        <span class="font-mono text-xs font-medium text-zinc-500">
                            ({{ $themeSlug }})
                        </span>
                    </p>
                </div>

                @if ($layout)
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-blue-100 px-3 py-1 font-bold text-blue-800">
                            {{ $layout->locale->nativeLabel() }}
                        </span>

                        @if (
                            $layout->status->value === 'published'
                            && (
                                $layout->draft_html !== $layout->published_html
                                || $layout->draft_css !== $layout->published_css
                            )
                        )
                            <span class="rounded-full bg-amber-100 px-3 py-1 font-bold text-amber-800">
                                Unpublished draft changes
                            </span>
                        @else
                            <span
                                class="rounded-full px-3 py-1 font-bold
                                    {{ $layout->status->value === 'published'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-amber-100 text-amber-800' }}"
                            >
                                {{ $layout->status->label() }}
                            </span>
                        @endif

                        <span class="rounded-full bg-zinc-200 px-3 py-1 font-semibold text-zinc-700">
                            Revision {{ $layout->revision_number }}
                        </span>

                        @if ($layout->published_at)
                            <span class="text-zinc-500">
                                Published {{ $layout->published_at->format('Y-m-d H:i') }}
                            </span>
                        @endif
                    </div>
                @else
                    <span class="w-fit rounded-full bg-zinc-200 px-3 py-1 text-xs font-bold text-zinc-700">
                        Not configured
                    </span>
                @endif
            </div>

            {{-- Language tabs --}}
            <div class="border-b border-zinc-200 bg-white px-6 pt-5">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-zinc-500">
                    Layout language
                </p>

                <div
                    class="flex flex-wrap gap-2"
                    role="tablist"
                    aria-label="Theme layout language"
                >
                    @foreach ($locales as $localeOption)
                        <button
                            type="button"
                            wire:key="theme-layout-locale-{{ $localeOption->value }}"
                            wire:click="selectLocale('{{ $localeOption->value }}')"
                            wire:confirm="Switch to {{ $localeOption->label() }}? Any unsaved editor changes will be discarded."
                            class="rounded-t-xl border border-b-0 px-5 py-3 text-sm font-bold
                                {{ $locale === $localeOption->value
                                    ? 'border-zinc-300 bg-zinc-50 text-emerald-700'
                                    : 'border-transparent bg-white text-zinc-600 hover:bg-zinc-50 hover:text-zinc-950' }}"
                            role="tab"
                            aria-selected="{{ $locale === $localeOption->value ? 'true' : 'false' }}"
                        >
                            <span>{{ $localeOption->nativeLabel() }}</span>

                            <span class="ml-1 text-xs font-semibold opacity-60">
                                {{ strtoupper($localeOption->value) }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Region tabs --}}
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 pt-4">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-zinc-500">
                    Layout region
                </p>

                <div
                    class="flex flex-wrap gap-2"
                    role="tablist"
                    aria-label="Theme layout region"
                >
                    <button
                        type="button"
                        wire:click="selectRegion('header')"
                        wire:confirm="Switch to Header? Any unsaved editor changes will be discarded."
                        class="rounded-t-xl border border-b-0 px-5 py-3 text-sm font-bold
                            {{ $region === 'header'
                                ? 'border-zinc-300 bg-white text-emerald-700'
                                : 'border-transparent bg-zinc-100 text-zinc-600 hover:text-zinc-950' }}"
                        role="tab"
                        aria-selected="{{ $region === 'header' ? 'true' : 'false' }}"
                    >
                        Header / Top Bar / Navigation
                    </button>

                    <button
                        type="button"
                        wire:click="selectRegion('footer')"
                        wire:confirm="Switch to Footer? Any unsaved editor changes will be discarded."
                        class="rounded-t-xl border border-b-0 px-5 py-3 text-sm font-bold
                            {{ $region === 'footer'
                                ? 'border-zinc-300 bg-white text-emerald-700'
                                : 'border-transparent bg-zinc-100 text-zinc-600 hover:text-zinc-950' }}"
                        role="tab"
                        aria-selected="{{ $region === 'footer' ? 'true' : 'false' }}"
                    >
                        Footer
                    </button>
                </div>
            </div>

            <form wire:submit="saveDraft" class="space-y-6 p-6">
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <p class="font-bold">
                        Safe multilingual code editor
                    </p>

                    <p class="mt-1 leading-6">
                        Each language and region is saved independently.
                        PHP, Blade directives, JavaScript, event attributes, forms,
                        embeds and external CSS imports are blocked.
                    </p>

                    <p class="mt-2 leading-6">
                        Menu Builder remains the place to manage navigation items.
                        Use
                        <code class="font-mono">[[primary_menu]]</code>
                        inside the Header HTML to position the localized menu.
                    </p>
                </div>

                @if ($locale !== 'en')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        If this language does not have a published layout, the public
                        website will use the published English layout as its safe fallback.
                    </div>
                @endif

                <div>
                    <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <label for="layout-html" class="block text-sm font-bold text-zinc-900">
                                {{ ucfirst($region) }} HTML
                            </label>

                            <p class="mt-1 text-xs text-zinc-500">
                                Maximum 100,000 characters. Content is sanitized again when saved.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-zinc-500">
                            {{ strtoupper($locale) }} · HTML + approved placeholders
                        </span>
                    </div>

                    <textarea
                        id="layout-html"
                        wire:model="layoutHtml"
                        rows="24"
                        spellcheck="false"
                        autocapitalize="off"
                        autocomplete="off"
                        class="w-full rounded-xl border border-zinc-300 bg-zinc-950 px-4 py-4 font-mono text-[13px] leading-6 text-zinc-100 shadow-inner focus:border-emerald-500 focus:ring-emerald-500"
                        placeholder="<header>[[site_logo]] [[site_name]] [[primary_menu]]</header>"
                    ></textarea>

                    @error('layoutHtml')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <label for="layout-css" class="block text-sm font-bold text-zinc-900">
                                Scoped custom CSS
                            </label>

                            <p class="mt-1 text-xs text-zinc-500">
                                Maximum 50,000 characters. Use a theme-specific wrapper
                                class to avoid affecting admin or page content.
                            </p>
                        </div>

                        <span class="text-xs font-medium text-zinc-500">
                            No &lt;style&gt;, &#64;import or url()
                        </span>
                    </div>

                    <textarea
                        id="layout-css"
                        wire:model="layoutCss"
                        rows="14"
                        spellcheck="false"
                        autocapitalize="off"
                        autocomplete="off"
                        class="w-full rounded-xl border border-zinc-300 bg-zinc-950 px-4 py-4 font-mono text-[13px] leading-6 text-zinc-100 shadow-inner focus:border-emerald-500 focus:ring-emerald-500"
                        placeholder=".school-site-header { background: #0f172a; }"
                    ></textarea>

                    @error('layoutCss')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="button"
                        wire:click="publish"
                        wire:confirm="Publish the saved {{ strtoupper($locale) }} {{ $region }} draft?"
                        wire:loading.attr="disabled"
                        class="rounded-xl border border-emerald-700 px-5 py-3 text-sm font-bold text-emerald-700 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Publish Saved Draft
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Save {{ strtoupper($locale) }} Draft
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">
                    Approved CMS placeholders
                </h2>

                <p class="mt-1 text-xs text-zinc-500">
                    Place these tokens inside the HTML editor. They will be replaced
                    with escaped and secure CMS content on the public website.
                </p>
            </div>

            <div class="grid gap-3 p-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($placeholders as $placeholder)
                    <code class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-800">
                        [[{{ $placeholder }}]]
                    </code>
                @endforeach
            </div>
        </section>
    @endif
</div>
@props([
    'title' => '',
    'slug' => '',
    'seoTitle' => '',
    'metaDescription' => '',
    'canonicalUrl' => '',
    'robotsIndex' => true,
    'ogTitle' => '',
    'ogDescription' => '',
    'ogImage' => '',
])

<section
    class="overflow-hidden rounded-2xl
           border border-zinc-200
           bg-white shadow-sm"
>
    <div
        class="border-b border-zinc-200
               bg-zinc-50 px-6 py-5"
    >
        <div
            class="flex flex-col gap-2
                   sm:flex-row sm:items-start
                   sm:justify-between"
        >
            <div>
                <h2
                    class="text-lg font-bold
                           text-zinc-950"
                >
                    SEO & Social Sharing
                </h2>

                <p
                    class="mt-1 text-sm
                           text-zinc-600"
                >
                    Control how this page appears in
                    search engines and social media.
                </p>
            </div>

            <span
                class="inline-flex w-fit rounded-full
                       bg-emerald-50 px-3 py-1
                       text-xs font-semibold
                       text-emerald-700"
            >
                Optional
            </span>
        </div>
    </div>

    <div class="space-y-8 p-6">
        {{-- Search Engine --}}
        <div class="space-y-5">
            <div>
                <h3
                    class="text-sm font-bold
                           uppercase tracking-wide
                           text-zinc-700"
                >
                    Search Engine Metadata
                </h3>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Leave optional fields empty to use
                    automatic page information.
                </p>
            </div>

            {{-- SEO title --}}
            <div>
                <div
                    class="mb-2 flex items-center
                           justify-between gap-4"
                >
                    <label
                        for="seo-title"
                        class="text-sm font-semibold
                               text-zinc-800"
                    >
                        SEO Title
                    </label>

                    <span
                        class="text-xs
                               {{ mb_strlen($seoTitle) > 60
                                    ? 'text-amber-700'
                                    : 'text-zinc-500' }}"
                    >
                        {{ mb_strlen($seoTitle) }}/70
                    </span>
                </div>

                <input
                    id="seo-title"
                    type="text"
                    maxlength="70"
                    wire:model.live.debounce.300ms="seoTitle"
                    placeholder="{{ $title !== ''
                        ? $title
                        : 'Search engine page title' }}"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >

                <p
                    class="mt-2 text-xs
                           text-zinc-500"
                >
                    Recommended: approximately 50–60 characters.
                    Empty = page title.
                </p>

                @error('seoTitle')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Meta description --}}
            <div>
                <div
                    class="mb-2 flex items-center
                           justify-between gap-4"
                >
                    <label
                        for="meta-description"
                        class="text-sm font-semibold
                               text-zinc-800"
                    >
                        Meta Description
                    </label>

                    <span
                        class="text-xs
                               {{ mb_strlen($metaDescription) > 155
                                    ? 'text-amber-700'
                                    : 'text-zinc-500' }}"
                    >
                        {{ mb_strlen($metaDescription) }}/160
                    </span>
                </div>

                <textarea
                    id="meta-description"
                    rows="3"
                    maxlength="160"
                    wire:model.live.debounce.300ms="metaDescription"
                    placeholder="A concise description for search results..."
                    class="w-full resize-y
                           rounded-xl border
                           border-zinc-300 bg-white
                           px-4 py-3 text-sm
                           outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                ></textarea>

                <p
                    class="mt-2 text-xs
                           text-zinc-500"
                >
                    Empty = Short Description, then page
                    content as fallback.
                </p>

                @error('metaDescription')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Canonical URL --}}
            <div>
                <label
                    for="canonical-url"
                    class="mb-2 block
                           text-sm font-semibold
                           text-zinc-800"
                >
                    Canonical URL
                </label>

                <input
                    id="canonical-url"
                    type="url"
                    wire:model="canonicalUrl"
                    placeholder="https://www.example.com/page"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >

                <p
                    class="mt-2 text-xs
                           text-zinc-500"
                >
                    Normally leave this empty. AWCMS will
                    automatically use this page's public URL.
                </p>

                @error('canonicalUrl')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Robots --}}
            <div
                class="rounded-xl border
                       border-zinc-200 bg-zinc-50
                       p-4"
            >
                <label
                    class="flex cursor-pointer
                           items-start gap-3"
                >
                    <input
                        type="checkbox"
                        wire:model.live="robotsIndex"
                        class="mt-1 h-4 w-4
                               rounded border-zinc-300
                               text-emerald-700
                               focus:ring-emerald-500"
                    >

                    <span>
                        <span
                            class="block text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            Allow search engines to index this page
                        </span>

                        <span
                            class="mt-1 block text-xs
                                   leading-5
                                   text-zinc-500"
                        >
                            Disable this for pages that should
                            remain available by URL but should
                            not appear in search results.
                        </span>
                    </span>
                </label>

                <div
                    class="mt-3 rounded-lg
                           bg-white px-3 py-2
                           font-mono text-xs
                           text-zinc-600"
                >
                    robots:
                    {{ $robotsIndex
                        ? 'index,follow'
                        : 'noindex,nofollow' }}
                </div>

                @error('robotsIndex')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Search preview --}}
        <div
            class="rounded-2xl border
                   border-blue-100 bg-blue-50/50
                   p-5"
        >
            <p
                class="text-xs font-bold
                       uppercase tracking-wide
                       text-blue-700"
            >
                Search Result Preview
            </p>

            <div
                class="mt-4 rounded-xl
                       border border-zinc-200
                       bg-white p-5"
            >
                <p
                    class="truncate text-xs
                           text-zinc-600"
                >
                    {{ config('app.url') }}/pages/{{ $slug !== ''
                        ? $slug
                        : 'page-slug' }}
                </p>

                <p
                    class="mt-1 text-xl
                           font-medium text-blue-800"
                >
                    {{ $seoTitle !== ''
                        ? $seoTitle
                        : ($title !== ''
                            ? $title
                            : 'Page Title') }}
                </p>

                <p
                    class="mt-2 text-sm
                           leading-6 text-zinc-600"
                >
                    {{ $metaDescription !== ''
                        ? $metaDescription
                        : 'The page description will be generated automatically when a custom meta description is not provided.' }}
                </p>
            </div>
        </div>

        {{-- Open Graph --}}
        <div
            class="space-y-5 border-t
                   border-zinc-200 pt-7"
        >
            <div>
                <h3
                    class="text-sm font-bold
                           uppercase tracking-wide
                           text-zinc-700"
                >
                    Social Sharing / Open Graph
                </h3>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Used when the page is shared to platforms
                    such as Facebook and messaging apps.
                </p>
            </div>

            {{-- OG title --}}
            <div>
                <div
                    class="mb-2 flex items-center
                           justify-between gap-4"
                >
                    <label
                        for="og-title"
                        class="text-sm font-semibold
                               text-zinc-800"
                    >
                        Open Graph Title
                    </label>

                    <span class="text-xs text-zinc-500">
                        {{ mb_strlen($ogTitle) }}/95
                    </span>
                </div>

                <input
                    id="og-title"
                    type="text"
                    maxlength="95"
                    wire:model.live.debounce.300ms="ogTitle"
                    placeholder="Social media title"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >

                <p class="mt-2 text-xs text-zinc-500">
                    Empty = SEO Title.
                </p>

                @error('ogTitle')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- OG description --}}
            <div>
                <div
                    class="mb-2 flex items-center
                           justify-between gap-4"
                >
                    <label
                        for="og-description"
                        class="text-sm font-semibold
                               text-zinc-800"
                    >
                        Open Graph Description
                    </label>

                    <span class="text-xs text-zinc-500">
                        {{ mb_strlen($ogDescription) }}/200
                    </span>
                </div>

                <textarea
                    id="og-description"
                    rows="3"
                    maxlength="200"
                    wire:model.live.debounce.300ms="ogDescription"
                    placeholder="Description shown when this page is shared..."
                    class="w-full resize-y
                           rounded-xl border
                           border-zinc-300 bg-white
                           px-4 py-3 text-sm
                           outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                ></textarea>

                <p class="mt-2 text-xs text-zinc-500">
                    Empty = Meta Description.
                </p>

                @error('ogDescription')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- OG image --}}
            <div>
                <label
                    for="og-image"
                    class="mb-2 block
                           text-sm font-semibold
                           text-zinc-800"
                >
                    Open Graph Image URL
                </label>

                <input
                    id="og-image"
                    type="url"
                    wire:model.live.debounce.500ms="ogImage"
                    placeholder="https://www.example.com/images/share.jpg"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >

                <p class="mt-2 text-xs text-zinc-500">
                    Media Library image selection will replace
                    this manual URL field later.
                </p>

                @error('ogImage')
                    <p
                        class="mt-2 text-sm
                               font-medium text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            @if ($ogImage !== '')
                <div
                    class="overflow-hidden rounded-xl
                           border border-zinc-200
                           bg-zinc-50"
                >
                    <div
                        class="border-b border-zinc-200
                               px-4 py-3"
                    >
                        <p
                            class="text-xs font-semibold
                                   text-zinc-600"
                        >
                            Image Preview
                        </p>
                    </div>

                    <img
                        src="{{ $ogImage }}"
                        alt=""
                        loading="lazy"
                        class="max-h-72 w-full
                               object-cover"
                    >
                </div>
            @endif

            {{-- Social preview --}}
            <div
                class="rounded-2xl border
                       border-violet-100
                       bg-violet-50/50 p-5"
            >
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-violet-700"
                >
                    Social Sharing Preview
                </p>

                <div
                    class="mt-4 overflow-hidden
                           rounded-xl border
                           border-zinc-200
                           bg-white"
                >
                    @if ($ogImage !== '')
                        <img
                            src="{{ $ogImage }}"
                            alt=""
                            loading="lazy"
                            class="h-56 w-full object-cover"
                        >
                    @else
                        <div
                            class="flex h-40
                                   items-center justify-center
                                   bg-zinc-100
                                   text-sm text-zinc-400"
                        >
                            No Open Graph image
                        </div>
                    @endif

                    <div class="p-4">
                        <p
                            class="text-xs uppercase
                                   tracking-wide
                                   text-zinc-400"
                        >
                            {{ parse_url(
                                config('app.url'),
                                PHP_URL_HOST,
                            ) ?: config('app.name') }}
                        </p>

                        <p
                            class="mt-1 text-base
                                   font-bold
                                   text-zinc-900"
                        >
                            {{ $ogTitle !== ''
                                ? $ogTitle
                                : ($seoTitle !== ''
                                    ? $seoTitle
                                    : ($title !== ''
                                        ? $title
                                        : 'Page Title')) }}
                        </p>

                        <p
                            class="mt-1 text-sm
                                   leading-5
                                   text-zinc-600"
                        >
                            {{ $ogDescription !== ''
                                ? $ogDescription
                                : ($metaDescription !== ''
                                    ? $metaDescription
                                    : 'Social media description will use the page metadata automatically.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
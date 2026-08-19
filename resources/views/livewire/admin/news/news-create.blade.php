<div class="mx-auto max-w-7xl space-y-6">
    {{-- Header --}}
    <div
        class="flex flex-col gap-4
               sm:flex-row sm:items-start
               sm:justify-between"
    >
        <div>
            <a
                href="{{ route('admin.news.index') }}"
                wire:navigate
                class="text-sm font-semibold
                       text-emerald-700
                       hover:text-emerald-800"
            >
                ← Back to News
            </a>

            <h1
                class="mt-2 text-2xl
                       font-bold text-zinc-950"
            >
                Create News Article
            </h1>

            <p
                class="mt-1 text-sm
                       text-zinc-600"
            >
                Create a new article and save it
                as a draft for the publishing workflow.
            </p>
        </div>

        <span
            class="inline-flex self-start
                   rounded-full bg-zinc-100
                   px-3 py-1.5
                   text-xs font-bold
                   uppercase tracking-wide
                   text-zinc-600"
        >
            Draft
        </span>
    </div>

    {{-- Validation summary --}}
    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 px-5 py-4"
        >
            <p
                class="text-sm font-bold
                       text-red-800"
            >
                Please correct the highlighted fields.
            </p>

            <ul
                class="mt-2 list-inside list-disc
                       space-y-1 text-sm
                       text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        wire:submit="save"
        class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_360px]"
    >
        {{-- ==================================================
             MAIN COLUMN
        =================================================== --}}
        <div class="space-y-6">
            {{-- Main details --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Article Details
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-zinc-500"
                    >
                        Enter the main information
                        for the news article.
                    </p>
                </div>

                <div class="space-y-5 p-6">
                    {{-- Title --}}
                    <div>
                        <label
                            for="news-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Title
                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <input
                            id="news-title"
                            type="text"
                            maxlength="255"
                            wire:model="title"
                            placeholder="Enter article title"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm text-zinc-900
                                   outline-none
                                   placeholder:text-zinc-400
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10"
                        >

                        @error('title')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div>
                        <label
                            for="news-slug"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            URL Slug
                        </label>

                        <div
                            class="flex overflow-hidden
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   focus-within:border-emerald-500
                                   focus-within:ring-4
                                   focus-within:ring-emerald-500/10"
                        >
                            <span
                                class="flex items-center
                                       border-r border-zinc-200
                                       bg-zinc-50
                                       px-3 text-sm
                                       text-zinc-500"
                            >
                                /news/
                            </span>

                            <input
                                id="news-slug"
                                type="text"
                                maxlength="255"
                                wire:model="slug"
                                placeholder="automatic-from-title"
                                class="min-w-0 flex-1
                                       border-0 bg-white
                                       px-4 py-3
                                       text-sm text-zinc-900
                                       outline-none
                                       focus:ring-0"
                            >
                        </div>

                        <p
                            class="mt-2 text-xs
                                   text-zinc-500"
                        >
                            Leave blank to generate automatically
                            from the article title.
                        </p>

                        @error('slug')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Summary --}}
                    <div>
                        <div
                            class="mb-2 flex
                                   items-center justify-between"
                        >
                            <label
                                for="news-summary"
                                class="text-sm font-semibold
                                       text-zinc-800"
                            >
                                Summary
                            </label>

                            <span
                                class="text-xs
                                       text-zinc-400"
                            >
                                Max 2000 characters
                            </span>
                        </div>

                        <textarea
                            id="news-summary"
                            rows="4"
                            maxlength="2000"
                            wire:model="summary"
                            placeholder="Short introduction or summary..."
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm leading-6
                                   text-zinc-900
                                   outline-none
                                   placeholder:text-zinc-400
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10"
                        ></textarea>

                        @error('summary')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Body --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Article Body
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-zinc-500"
                    >
                        Use the approved rich-text formatting tools.
                    </p>
                </div>

                <div class="p-6">
                    <label
                        for="news-content-editor"
                        class="mb-2 block
                               text-sm font-semibold
                               text-zinc-800"
                    >
                        Content
                        <span class="text-red-600">
                            *
                        </span>
                    </label>

                    <div
                        wire:ignore
                        class="overflow-hidden
                               rounded-xl
                               border border-zinc-300
                               bg-white"
                    >
                        <input
                            id="news-content-input"
                            type="hidden"
                            value="{{ $content }}"
                        >

                        <trix-editor
                            id="news-content-editor"
                            input="news-content-input"
                            x-data
                            x-on:trix-change="
                                $wire.set(
                                    'content',
                                    $event.target.value
                                )
                            "
                            class="min-h-72
                                   border-0
                                   bg-white
                                   p-4
                                   text-sm leading-7
                                   text-zinc-900"
                        ></trix-editor>
                    </div>

                    <p
                        class="mt-2 text-xs
                               text-zinc-500"
                    >
                        File attachments are disabled.
                        Use the Media Library for images.
                    </p>

                    @error('content')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600"
                        >
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>

            {{-- SEO --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Search Engine Optimisation
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-zinc-500"
                    >
                        Optional metadata for search
                        and social previews.
                    </p>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label
                            for="news-seo-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            SEO Title
                        </label>

                        <input
                            id="news-seo-title"
                            type="text"
                            maxlength="255"
                            wire:model="seoTitle"
                            placeholder="Optional SEO title"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10"
                        >

                        @error('seoTitle')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <div
                            class="mb-2 flex
                                   items-center justify-between"
                        >
                            <label
                                for="news-seo-description"
                                class="text-sm font-semibold
                                       text-zinc-800"
                            >
                                SEO Description
                            </label>

                            <span
                                class="text-xs text-zinc-400"
                            >
                                Max 320
                            </span>
                        </div>

                        <textarea
                            id="news-seo-description"
                            rows="3"
                            maxlength="320"
                            wire:model="seoDescription"
                            placeholder="Optional search description..."
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm leading-6
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10"
                        ></textarea>

                        @error('seoDescription')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>
        </div>

        {{-- ==================================================
             RIGHT SIDEBAR
        =================================================== --}}
        <aside class="space-y-6">
            {{-- Publish settings --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           px-5 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Article Settings
                    </h2>
                </div>

                <div class="space-y-5 p-5">
                    {{-- Category --}}
                    <div>
                        <label
                            for="news-category-id"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Category
                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <select
                            id="news-category-id"
                            wire:model="categoryId"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm"
                        >
                            <option value="">
                                Select category
                            </option>

                            @foreach ($categories as $categoryOption)
                                <option
                                    value="{{ $categoryOption->id }}"
                                >
                                    {{ $categoryOption->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('categoryId')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        @if ($categories->isEmpty())
                            <p
                                class="mt-2 text-xs
                                       font-medium text-amber-700"
                            >
                                No active categories are available.
                            </p>
                        @endif
                    </div>

                    {{-- Planned date --}}
                    <div>
                        <label
                            for="news-published-at"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Planned Publication
                        </label>

                        <input
                            id="news-published-at"
                            type="datetime-local"
                            wire:model="publishedAt"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm"
                        >

                        <p
                            class="mt-2 text-xs
                                   leading-5 text-zinc-500"
                        >
                            This does not publish the article.
                            The workflow controls publication.
                        </p>

                        @error('publishedAt')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Featured --}}
                    <label
                        class="flex cursor-pointer
                               items-start gap-3
                               rounded-xl
                               border border-zinc-200
                               bg-zinc-50 p-4"
                    >
                        <input
                            type="checkbox"
                            wire:model="isFeatured"
                            class="mt-1 h-4 w-4
                                   rounded border-zinc-300"
                        >

                        <span>
                            <span
                                class="block text-sm
                                       font-semibold
                                       text-zinc-800"
                            >
                                Featured Article
                            </span>

                            <span
                                class="mt-1 block
                                       text-xs leading-5
                                       text-zinc-500"
                            >
                                Allow this article to appear
                                in featured news areas.
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            {{-- Main image --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           px-5 py-4"
                >
                    <h2 class="font-bold text-zinc-900">
                        Main Image
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-zinc-500"
                    >
                        Public Media Library images only.
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <label
                            for="news-featured-image"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Featured Image
                        </label>

                        <select
                            id="news-featured-image"
                            wire:model="featuredImageId"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm"
                        >
                            <option value="">
                                No featured image
                            </option>

                            @foreach ($images as $image)
                                <option value="{{ $image->id }}">
                                    #{{ $image->id }}
                                    —
                                    {{ $image->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('featuredImageId')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if ($featuredImageId !== '')
                        @php
                            $selectedImage = $images->firstWhere(
                                'id',
                                (int) $featuredImageId
                            );
                        @endphp

                        @if ($selectedImage)
                            <div
                                class="rounded-xl
                                       border border-emerald-200
                                       bg-emerald-50 p-4"
                            >
                                <p
                                    class="text-xs font-bold
                                           uppercase tracking-wide
                                           text-emerald-700"
                                >
                                    Selected
                                </p>

                                <p
                                    class="mt-2 text-sm
                                           font-semibold
                                           text-zinc-900"
                                >
                                    {{ $selectedImage->title }}
                                </p>

                                <p
                                    class="mt-1 text-xs
                                           text-zinc-500"
                                >
                                    {{ $selectedImage->original_name }}
                                </p>

                                <a
                                    href="{{ route(
                                        'admin.media.edit',
                                        ['media' => $selectedImage->id]
                                    ) }}"
                                    target="_blank"
                                    class="mt-3 inline-flex
                                           text-xs font-semibold
                                           text-emerald-700
                                           hover:text-emerald-800"
                                >
                                    View Media →
                                </a>
                            </div>
                        @endif
                    @endif

                    <a
                        href="{{ route('admin.media.index') }}"
                        target="_blank"
                        class="inline-flex text-sm
                               font-semibold text-emerald-700
                               hover:text-emerald-800"
                    >
                        Open Media Library →
                    </a>
                </div>
            </section>

            {{-- Save --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white p-5 shadow-sm"
            >
                <div
                    class="rounded-xl
                           bg-blue-50 p-4"
                >
                    <p
                        class="text-sm font-semibold
                               text-blue-800"
                    >
                        Draft only
                    </p>

                    <p
                        class="mt-1 text-xs
                               leading-5 text-blue-700"
                    >
                        Creating this article will not
                        make it publicly visible.
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="mt-4 inline-flex w-full
                           items-center justify-center
                           rounded-xl bg-emerald-700
                           px-5 py-3
                           text-sm font-bold
                           text-white
                           transition
                           hover:bg-emerald-800
                           disabled:cursor-not-allowed
                           disabled:opacity-60"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        Save Draft
                    </span>

                    <span
                        wire:loading
                        wire:target="save"
                    >
                        Saving...
                    </span>
                </button>

                <a
                    href="{{ route('admin.news.index') }}"
                    wire:navigate
                    class="mt-3 inline-flex w-full
                           items-center justify-center
                           rounded-xl border
                           border-zinc-300
                           bg-white px-5 py-3
                           text-sm font-semibold
                           text-zinc-700
                           transition
                           hover:bg-zinc-50"
                >
                    Cancel
                </a>
            </section>
        </aside>
    </form>
</div>
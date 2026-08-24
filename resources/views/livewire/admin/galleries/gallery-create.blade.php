<div class="space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col gap-4
               sm:flex-row
               sm:items-center
               sm:justify-between"
    >
        <div>
            <p
                class="text-xs font-bold
                       uppercase tracking-[0.18em]
                       text-emerald-700"
            >
                Gallery Management
            </p>

            <h1
                class="mt-1
                       text-2xl font-black
                       tracking-tight
                       text-zinc-950"
            >
                Create Gallery
            </h1>

            <p
                class="mt-1
                       text-sm leading-6
                       text-zinc-500"
            >
                Create the gallery album first.
                Images can be added and arranged after saving.
            </p>
        </div>

        <a
            href="{{ route('admin.galleries.index') }}"
            wire:navigate
            class="inline-flex items-center
                   justify-center
                   rounded-xl
                   border border-zinc-300
                   bg-white
                   px-4 py-2.5
                   text-sm font-semibold
                   text-zinc-700
                   shadow-sm
                   transition
                   hover:bg-zinc-50"
        >
            Back to Galleries
        </a>
    </div>


    {{-- =====================================================
         VALIDATION ERRORS
    ====================================================== --}}

    @if ($errors->any())
        <div
            class="rounded-xl
                   border border-red-200
                   bg-red-50
                   px-4 py-3
                   text-sm text-red-700"
        >
            <p class="font-bold">
                Please correct the following:
            </p>

            <ul
                class="mt-2
                       list-disc
                       space-y-1
                       pl-5"
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

        {{-- =================================================
             MAIN COLUMN
        ================================================== --}}

        <div class="space-y-6">

            {{-- BASIC INFORMATION --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Gallery Information
                    </h2>

                    <p
                        class="mt-1
                               text-xs
                               text-zinc-500"
                    >
                        Main information used for the
                        gallery album.
                    </p>
                </div>

                <div class="space-y-5 p-6">

                    {{-- Title --}}

                    <div>
                        <label
                            for="gallery-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Gallery Title

                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <input
                            id="gallery-title"
                            type="text"
                            wire:model="title"
                            maxlength="255"
                            autofocus
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   transition
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                            placeholder="Example: Army Day Celebration 2026"
                        >

                        @error('title')
                            <p
                                class="mt-1.5
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
                            for="gallery-slug"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            URL Slug
                        </label>

                        <input
                            id="gallery-slug"
                            type="text"
                            wire:model="slug"
                            maxlength="255"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                            placeholder="Leave blank to generate automatically"
                        >

                        <p
                            class="mt-1.5
                                   text-xs
                                   text-zinc-500"
                        >
                            Leave blank and AWCMS will generate
                            a unique URL slug from the title.
                        </p>

                        @error('slug')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Event Date --}}

                    <div>
                        <label
                            for="gallery-event-date"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Event Date
                        </label>

                        <input
                            id="gallery-event-date"
                            type="date"
                            wire:model="eventDate"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                        >

                        @error('eventDate')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Description --}}

                    <div>
                        <label
                            for="gallery-description"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Description
                        </label>

                        <textarea
                            id="gallery-description"
                            wire:model="description"
                            rows="7"
                            maxlength="10000"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   leading-6
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                            placeholder="Short description about the event or gallery..."
                        ></textarea>

                        @error('description')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>


            {{-- COVER IMAGE --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Cover Image
                    </h2>

                    <p
                        class="mt-1
                               text-xs
                               text-zinc-500"
                    >
                        Only Public images from the
                        Media Library are selectable.
                    </p>
                </div>

                <div class="space-y-4 p-6">
                    <div>
                        <label
                            for="gallery-cover"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Select Cover Image
                        </label>

                        <select
                            id="gallery-cover"
                            wire:model="coverMediaId"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900"
                        >
                            <option value="">
                                No cover image
                            </option>

                            @foreach ($mediaAssets as $asset)
                                <option
                                    value="{{ $asset->id }}"
                                >
                                    #{{ $asset->id }}
                                    —
                                    {{ $asset->title ?: $asset->original_name }}
                                </option>
                            @endforeach
                        </select>

                        @error('coverMediaId')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @can('media.view')
                        <a
                            href="{{ route('admin.media.index') }}"
                            target="_blank"
                            class="inline-flex
                                   text-sm font-semibold
                                   text-emerald-700
                                   hover:text-emerald-800"
                        >
                            Open Media Library →
                        </a>
                    @endcan

                    @if ($mediaAssets->isEmpty())
                        <div
                            class="rounded-xl
                                   border border-amber-200
                                   bg-amber-50
                                   p-4"
                        >
                            <p
                                class="text-sm font-semibold
                                       text-amber-800"
                            >
                                No Public images are currently
                                available.
                            </p>

                            <p
                                class="mt-1
                                       text-xs leading-5
                                       text-amber-700"
                            >
                                Upload a Public image through the
                                Media Library before selecting a cover.
                            </p>
                        </div>
                    @endif
                </div>
            </section>


            {{-- SEO --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Search Engine Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">

                    <div>
                        <label
                            for="gallery-seo-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            SEO Title
                        </label>

                        <input
                            id="gallery-seo-title"
                            type="text"
                            wire:model="seoTitle"
                            maxlength="255"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900"
                        >

                        @error('seoTitle')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="gallery-seo-description"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            SEO Description
                        </label>

                        <textarea
                            id="gallery-seo-description"
                            wire:model="seoDescription"
                            maxlength="320"
                            rows="4"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   leading-6
                                   text-zinc-900"
                        ></textarea>

                        <div
                            class="mt-1
                                   text-right
                                   text-xs
                                   text-zinc-400"
                        >
                            {{ mb_strlen($seoDescription) }}/320
                        </div>

                        @error('seoDescription')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>
        </div>


        {{-- =================================================
             SIDEBAR
        ================================================== --}}

        <aside class="space-y-6">

            {{-- STATUS --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Initial Status
                </p>

                <div
                    class="mt-3
                           inline-flex
                           rounded-full
                           bg-zinc-100
                           px-3 py-1.5
                           text-sm font-bold
                           text-zinc-700"
                >
                    Draft
                </div>

                <p
                    class="mt-3
                           text-sm leading-6
                           text-zinc-500"
                >
                    New galleries always start as Draft.
                    Add gallery images before publishing.
                </p>
            </section>


            {{-- PUBLICATION --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <h2
                    class="font-bold
                           text-zinc-900"
                >
                    Publication
                </h2>

                <div class="mt-4">
                    <label
                        for="gallery-published-at"
                        class="mb-1.5 block
                               text-sm font-semibold
                               text-zinc-800"
                    >
                        Planned Publish Date
                    </label>

                    <input
                        id="gallery-published-at"
                        type="datetime-local"
                        wire:model="publishedAt"
                        class="w-full
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-3 py-2.5
                               text-sm
                               text-zinc-900"
                    >

                    <p
                        class="mt-2
                               text-xs leading-5
                               text-zinc-500"
                    >
                        Optional. The gallery still remains
                        Draft until an authorised user
                        publishes it.
                    </p>

                    @error('publishedAt')
                        <p
                            class="mt-1.5
                                   text-sm font-medium
                                   text-red-600"
                        >
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>


            {{-- SAVE --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex
                           w-full
                           items-center
                           justify-center
                           rounded-xl
                           bg-emerald-700
                           px-5 py-3
                           text-sm font-bold
                           text-white
                           shadow-sm
                           transition
                           hover:bg-emerald-800
                           disabled:cursor-not-allowed
                           disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="save">
                        Create Gallery
                    </span>

                    <span wire:loading wire:target="save">
                        Creating...
                    </span>
                </button>

                <a
                    href="{{ route('admin.galleries.index') }}"
                    wire:navigate
                    class="mt-3
                           inline-flex
                           w-full
                           items-center
                           justify-center
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-5 py-3
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
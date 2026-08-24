<div class="space-y-6">

    @php
        $status = $gallery->status;

        $statusClasses = match ($status) {
            \App\Enums\GalleryStatus::Draft =>
                'bg-zinc-100 text-zinc-700',

            \App\Enums\GalleryStatus::Published =>
                'bg-emerald-100 text-emerald-700',

            \App\Enums\GalleryStatus::Archived =>
                'bg-amber-100 text-amber-700',

            default =>
                'bg-red-100 text-red-700',
        };
    @endphp


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-center
               lg:justify-between"
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
                Edit Gallery
            </h1>

            <div
                class="mt-2
                       flex flex-wrap
                       items-center
                       gap-2"
            >
                <span
                    class="inline-flex
                           rounded-full
                           px-2.5 py-1
                           text-xs font-bold
                           {{ $statusClasses }}"
                >
                    {{
                        $status instanceof
                        \App\Enums\GalleryStatus
                            ? $status->label()
                            : 'Invalid'
                    }}
                </span>

                <span
                    class="text-xs
                           text-zinc-400"
                >
                    Gallery #{{ $gallery->id }}
                </span>
            </div>
        </div>

        <a
            href="{{ route('admin.galleries.index') }}"
            wire:navigate
            class="inline-flex
                   items-center
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
         FLASH
    ====================================================== --}}

    @if (session('status'))
        <div
            class="rounded-xl
                   border border-emerald-200
                   bg-emerald-50
                   px-4 py-3
                   text-sm font-semibold
                   text-emerald-800"
        >
            {{ session('status') }}
        </div>
    @endif


    {{-- =====================================================
         VALIDATION
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
                The action could not be completed.
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


    {{-- =====================================================
         EDIT LOCK
    ====================================================== --}}

    @if (! $editable)
        <div
            class="rounded-xl
                   border border-amber-200
                   bg-amber-50
                   px-4 py-3"
        >
            <p
                class="text-sm font-bold
                       text-amber-800"
            >
                Editing locked
            </p>

            <p
                class="mt-1
                       text-xs leading-5
                       text-amber-700"
            >
                Only Draft galleries can be edited.
                Published galleries may be archived,
                but their content cannot be changed.
            </p>
        </div>
    @endif


    <form
        wire:submit="save"
        class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_360px]"
    >

        {{-- =================================================
             MAIN CONTENT
        ================================================== --}}

        <div class="space-y-6">

            {{-- =============================================
                 GALLERY INFORMATION
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
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
                </div>

                <div class="space-y-5 p-6">

                    <div>
                        <label
                            for="gallery-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Gallery Title
                        </label>

                        <input
                            id="gallery-title"
                            type="text"
                            wire:model="title"
                            maxlength="255"
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm text-zinc-900
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500"
                        >

                        @error('title')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


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
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm text-zinc-900
                                   disabled:bg-zinc-100"
                        >

                        @error('slug')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


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
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   disabled:bg-zinc-100"
                        >

                        @error('eventDate')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


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
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm leading-6
                                   disabled:bg-zinc-100"
                        ></textarea>

                        @error('description')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>


            {{-- =============================================
                 COVER IMAGE
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2 class="font-bold text-zinc-900">
                        Cover Image
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Public Media Library images only.
                    </p>
                </div>

                <div class="space-y-4 p-6">

                    @if ($coverPreviewUrl)
                        <img
                            src="{{ $coverPreviewUrl }}"
                            alt=""
                            class="h-48 w-full
                                   rounded-xl
                                   bg-zinc-100
                                   object-cover"
                        >
                    @endif

                    <select
                        wire:model="coverMediaId"
                        @disabled(! $editable)
                        class="w-full rounded-xl
                               border border-zinc-300
                               bg-white
                               px-3 py-2.5
                               text-sm
                               disabled:bg-zinc-100"
                    >
                        <option value="">
                            No cover image
                        </option>

                        @foreach ($coverMediaAssets as $asset)
                            <option value="{{ $asset->id }}">
                                #{{ $asset->id }}
                                —
                                {{
                                    $asset->title
                                    ?: $asset->original_name
                                }}
                            </option>
                        @endforeach
                    </select>

                    @error('coverMediaId')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

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
                </div>
            </section>


            {{-- =============================================
                 GALLERY IMAGES
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="flex flex-col gap-2
                           border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <h2 class="font-bold text-zinc-900">
                            Gallery Images
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $gallery->images->count() }}
                            image(s) attached
                        </p>
                    </div>
                </div>


                {{-- ADD IMAGE --}}

                @if ($editable)
                    <div
                        class="border-b border-zinc-200
                               bg-emerald-50/40
                               p-6"
                    >
                        <h3
                            class="text-sm font-bold
                                   text-zinc-900"
                        >
                            Add Media Library Image
                        </h3>

                        <div
                            class="mt-4
                                   grid gap-4
                                   lg:grid-cols-2"
                        >
                            <div class="lg:col-span-2">
                                <label
                                    class="mb-1.5 block
                                           text-sm font-semibold
                                           text-zinc-700"
                                >
                                    Image
                                </label>

                                <select
                                    wire:model="selectedMediaId"
                                    class="w-full rounded-xl
                                           border border-zinc-300
                                           bg-white
                                           px-3 py-2.5
                                           text-sm"
                                >
                                    <option value="">
                                        Select Public image...
                                    </option>

                                    @foreach ($mediaAssets as $asset)
                                        <option value="{{ $asset->id }}">
                                            #{{ $asset->id }}
                                            —
                                            {{
                                                $asset->title
                                                ?: $asset->original_name
                                            }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('selectedMediaId')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            <div>
                                <label
                                    class="mb-1.5 block
                                           text-sm font-semibold
                                           text-zinc-700"
                                >
                                    Caption
                                </label>

                                <input
                                    type="text"
                                    wire:model="newCaption"
                                    maxlength="1000"
                                    class="w-full rounded-xl
                                           border border-zinc-300
                                           bg-white
                                           px-3 py-2.5
                                           text-sm"
                                >

                                @error('newCaption')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            <div>
                                <label
                                    class="mb-1.5 block
                                           text-sm font-semibold
                                           text-zinc-700"
                                >
                                    Alternative Text
                                </label>

                                <input
                                    type="text"
                                    wire:model="newAltText"
                                    maxlength="255"
                                    class="w-full rounded-xl
                                           border border-zinc-300
                                           bg-white
                                           px-3 py-2.5
                                           text-sm"
                                    placeholder="Describe the image"
                                >

                                @error('newAltText')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="addImage"
                            wire:loading.attr="disabled"
                            wire:target="addImage"
                            class="mt-4 inline-flex
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-emerald-700
                                   px-4 py-2.5
                                   text-sm font-bold
                                   text-white
                                   hover:bg-emerald-800
                                   disabled:opacity-60"
                        >
                            Add Image
                        </button>
                    </div>
                @endif


                {{-- EXISTING IMAGES --}}

                <div class="p-6">
                    @if ($gallery->images->isNotEmpty())

                        <div class="space-y-4">
                            @foreach ($gallery->images as $image)

                                @php
                                    $media = $image->media;

                                    $previewUrl =
                                        $previewUrls[$image->id]
                                        ?? null;
                                @endphp

                                <article
                                    wire:key="gallery-image-{{ $image->id }}"
                                    class="rounded-2xl
                                           border border-zinc-200
                                           bg-zinc-50
                                           p-4"
                                >
                                    <div
                                        class="grid gap-5
                                               lg:grid-cols-[180px_minmax(0,1fr)]"
                                    >

                                        {{-- PREVIEW --}}

                                        <div>
                                            @if ($previewUrl)
                                                <img
                                                    src="{{ $previewUrl }}"
                                                    alt="{{ $imageAltTexts[$image->id] ?? '' }}"
                                                    loading="lazy"
                                                    class="h-36 w-full
                                                           rounded-xl
                                                           bg-zinc-200
                                                           object-cover"
                                                >
                                            @else
                                                <div
                                                    class="flex h-36
                                                           items-center
                                                           justify-center
                                                           rounded-xl
                                                           bg-zinc-200
                                                           text-xs
                                                           font-semibold
                                                           text-zinc-500"
                                                >
                                                    Preview unavailable
                                                </div>
                                            @endif

                                            <div
                                                class="mt-2
                                                       flex items-center
                                                       justify-between
                                                       text-xs
                                                       text-zinc-500"
                                            >
                                                <span>
                                                    Position
                                                    {{ $loop->iteration }}
                                                </span>

                                                <span>
                                                    Media #
                                                    {{ $image->media_asset_id }}
                                                </span>
                                            </div>
                                        </div>


                                        {{-- METADATA --}}

                                        <div class="space-y-4">

                                            <div>
                                                <p
                                                    class="text-sm
                                                           font-bold
                                                           text-zinc-900"
                                                >
                                                    {{
                                                        $media?->title
                                                        ?: $media?->original_name
                                                        ?: 'Unavailable Media'
                                                    }}
                                                </p>
                                            </div>


                                            <div
                                                class="grid gap-4
                                                       md:grid-cols-2"
                                            >
                                                <div>
                                                    <label
                                                        class="mb-1.5 block
                                                               text-xs font-bold
                                                               uppercase
                                                               tracking-wide
                                                               text-zinc-500"
                                                    >
                                                        Caption
                                                    </label>

                                                    <input
                                                        type="text"
                                                        wire:model="imageCaptions.{{ $image->id }}"
                                                        maxlength="1000"
                                                        @disabled(! $editable)
                                                        class="w-full
                                                               rounded-xl
                                                               border border-zinc-300
                                                               bg-white
                                                               px-3 py-2
                                                               text-sm
                                                               disabled:bg-zinc-100"
                                                    >

                                                    @error(
                                                        'imageCaptions.'
                                                        .$image->id
                                                    )
                                                        <p
                                                            class="mt-1
                                                                   text-xs
                                                                   text-red-600"
                                                        >
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>


                                                <div>
                                                    <label
                                                        class="mb-1.5 block
                                                               text-xs font-bold
                                                               uppercase
                                                               tracking-wide
                                                               text-zinc-500"
                                                    >
                                                        Alt Text
                                                    </label>

                                                    <input
                                                        type="text"
                                                        wire:model="imageAltTexts.{{ $image->id }}"
                                                        maxlength="255"
                                                        @disabled(! $editable)
                                                        class="w-full
                                                               rounded-xl
                                                               border border-zinc-300
                                                               bg-white
                                                               px-3 py-2
                                                               text-sm
                                                               disabled:bg-zinc-100"
                                                    >

                                                    @error(
                                                        'imageAltTexts.'
                                                        .$image->id
                                                    )
                                                        <p
                                                            class="mt-1
                                                                   text-xs
                                                                   text-red-600"
                                                        >
                                                            {{ $message }}
                                                        </p>
                                                    @enderror
                                                </div>
                                            </div>


                                            @if ($editable)
                                                <div
                                                    class="flex flex-wrap
                                                           items-center
                                                           gap-2"
                                                >
                                                    <button
                                                        type="button"
                                                        wire:click="updateImage({{ $image->id }})"
                                                        class="rounded-lg
                                                               bg-zinc-900
                                                               px-3 py-2
                                                               text-xs font-bold
                                                               text-white
                                                               hover:bg-zinc-800"
                                                    >
                                                        Save Details
                                                    </button>


                                                    <button
                                                        type="button"
                                                        wire:click="moveImageUp({{ $image->id }})"
                                                        @disabled($loop->first)
                                                        class="rounded-lg
                                                               border border-zinc-300
                                                               bg-white
                                                               px-3 py-2
                                                               text-xs font-bold
                                                               text-zinc-700
                                                               disabled:cursor-not-allowed
                                                               disabled:opacity-40"
                                                    >
                                                        ↑ Up
                                                    </button>


                                                    <button
                                                        type="button"
                                                        wire:click="moveImageDown({{ $image->id }})"
                                                        @disabled($loop->last)
                                                        class="rounded-lg
                                                               border border-zinc-300
                                                               bg-white
                                                               px-3 py-2
                                                               text-xs font-bold
                                                               text-zinc-700
                                                               disabled:cursor-not-allowed
                                                               disabled:opacity-40"
                                                    >
                                                        ↓ Down
                                                    </button>


                                                    <button
                                                        type="button"
                                                        wire:click="removeImage({{ $image->id }})"
                                                        wire:confirm="Remove this image from the gallery?"
                                                        class="rounded-lg
                                                               border border-red-200
                                                               bg-red-50
                                                               px-3 py-2
                                                               text-xs font-bold
                                                               text-red-700
                                                               hover:bg-red-100"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </article>

                            @endforeach
                        </div>

                    @else

                        <div
                            class="rounded-xl
                                   border border-dashed
                                   border-zinc-300
                                   bg-zinc-50
                                   px-6 py-12
                                   text-center"
                        >
                            <p
                                class="font-bold
                                       text-zinc-800"
                            >
                                No images added yet
                            </p>

                            <p
                                class="mt-1
                                       text-sm
                                       text-zinc-500"
                            >
                                Add at least one Public
                                Media Library image before
                                publishing this gallery.
                            </p>
                        </div>

                    @endif
                </div>
            </section>


            {{-- =============================================
                 SEO
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2 class="font-bold text-zinc-900">
                        Search Engine Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            SEO Title
                        </label>

                        <input
                            type="text"
                            wire:model="seoTitle"
                            maxlength="255"
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   disabled:bg-zinc-100"
                        >
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            SEO Description
                        </label>

                        <textarea
                            wire:model="seoDescription"
                            rows="4"
                            maxlength="320"
                            @disabled(! $editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm leading-6
                                   disabled:bg-zinc-100"
                        ></textarea>

                        <p
                            class="mt-1
                                   text-right
                                   text-xs
                                   text-zinc-400"
                        >
                            {{ mb_strlen($seoDescription) }}/320
                        </p>
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
                    Status
                </p>

                <span
                    class="mt-3
                           inline-flex
                           rounded-full
                           px-3 py-1.5
                           text-sm font-bold
                           {{ $statusClasses }}"
                >
                    {{
                        $status instanceof
                        \App\Enums\GalleryStatus
                            ? $status->label()
                            : 'Invalid'
                    }}
                </span>

                @if ($status === \App\Enums\GalleryStatus::Published)
                    <p
                        class="mt-3
                               text-xs leading-5
                               text-zinc-500"
                    >
                        Published:
                        {{
                            $gallery->published_at
                                ?->format('d M Y H:i')
                            ?? '—'
                        }}
                    </p>
                @endif

                @if ($status === \App\Enums\GalleryStatus::Archived)
                    <p
                        class="mt-3
                               text-xs leading-5
                               text-zinc-500"
                    >
                        Archived:
                        {{
                            $gallery->archived_at
                                ?->format('d M Y H:i')
                            ?? '—'
                        }}
                    </p>
                @endif
            </section>


            {{-- PUBLICATION DATE --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <h2 class="font-bold text-zinc-900">
                    Publication
                </h2>

                <label
                    class="mt-4 mb-1.5 block
                           text-sm font-semibold
                           text-zinc-700"
                >
                    Publish Date
                </label>

                <input
                    type="datetime-local"
                    wire:model="publishedAt"
                    @disabled(! $editable)
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white
                           px-3 py-2.5
                           text-sm
                           disabled:bg-zinc-100"
                >

                <p
                    class="mt-2
                           text-xs leading-5
                           text-zinc-500"
                >
                    A future date can be used for
                    scheduled public visibility.
                </p>
            </section>


            {{-- SAVE --}}

            @if ($editable)
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
                               hover:bg-emerald-800
                               disabled:opacity-60"
                    >
                        <span
                            wire:loading.remove
                            wire:target="save"
                        >
                            Save Gallery
                        </span>

                        <span
                            wire:loading
                            wire:target="save"
                        >
                            Saving...
                        </span>
                    </button>
                </section>
            @endif


            {{-- WORKFLOW --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <h2 class="font-bold text-zinc-900">
                    Workflow
                </h2>

                <div class="mt-4 space-y-3">

                    @can('galleries.publish')
                        @if (
                            $status ===
                            \App\Enums\GalleryStatus::Draft
                        )
                            <button
                                type="button"
                                wire:click="publish"
                                wire:confirm="Publish this gallery?"
                                wire:loading.attr="disabled"
                                wire:target="publish"
                                class="inline-flex
                                       w-full
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-emerald-700
                                       px-5 py-3
                                       text-sm font-bold
                                       text-white
                                       hover:bg-emerald-800
                                       disabled:opacity-60"
                            >
                                Publish Gallery
                            </button>
                        @endif
                    @endcan


                    @can('galleries.archive')
                        @if (
                            $status ===
                            \App\Enums\GalleryStatus::Published
                        )
                            <button
                                type="button"
                                wire:click="archive"
                                wire:confirm="Archive this gallery?"
                                wire:loading.attr="disabled"
                                wire:target="archive"
                                class="inline-flex
                                       w-full
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-amber-600
                                       px-5 py-3
                                       text-sm font-bold
                                       text-white
                                       hover:bg-amber-700
                                       disabled:opacity-60"
                            >
                                Archive Gallery
                            </button>
                        @endif
                    @endcan


                    <a
                        href="{{ route('admin.galleries.index') }}"
                        wire:navigate
                        class="inline-flex
                               w-full
                               items-center
                               justify-center
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-5 py-3
                               text-sm font-semibold
                               text-zinc-700
                               hover:bg-zinc-50"
                    >
                        Back to Galleries
                    </a>
                </div>
            </section>
        </aside>
    </form>
</div>
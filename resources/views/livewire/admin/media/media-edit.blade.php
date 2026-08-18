<div class="mx-auto max-w-7xl space-y-6">
    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-start
               lg:justify-between"
    >
        <div>
            <div
                class="flex flex-wrap
                       items-center gap-3"
            >
                <a
                    href="{{ route('admin.media.index') }}"
                    class="text-sm font-semibold
                           text-emerald-700
                           hover:underline"
                >
                    ← Media Library
                </a>
            </div>

            <h1
                class="mt-3 text-2xl
                       font-bold
                       text-zinc-950"
            >
                Media Details
            </h1>

            <p
                class="mt-1 text-sm
                       text-zinc-500"
            >
                Asset #{{ $mediaAsset->id }}
            </p>
        </div>

        @if ($canUpdate)
            <div
                class="rounded-xl
                       border border-emerald-200
                       bg-emerald-50
                       px-4 py-3
                       text-xs font-semibold
                       text-emerald-800"
            >
                You can edit this media asset.
            </div>
        @else
            <div
                class="rounded-xl
                       border border-zinc-200
                       bg-zinc-50
                       px-4 py-3
                       text-xs font-semibold
                       text-zinc-600"
            >
                Read-only access
            </div>
        @endif
    </div>

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

    <div
        class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_380px]"
    >
        <div class="space-y-6">
            {{-- Editable metadata --}}
            <form
                wire:submit="save"
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Media Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label
                            for="media-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Title
                        </label>

                        <input
                            id="media-title"
                            type="text"
                            maxlength="255"
                            wire:model="title"
                            @disabled(! $canUpdate)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500"
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

                    @if ($mediaAsset->isImage())
                        <div>
                            <label
                                for="media-alt"
                                class="mb-2 block
                                       text-sm font-semibold
                                       text-zinc-800"
                            >
                                Alternative Text
                            </label>

                            <input
                                id="media-alt"
                                type="text"
                                maxlength="255"
                                wire:model="altText"
                                @disabled(! $canUpdate)
                                class="w-full rounded-xl
                                       border border-zinc-300
                                       bg-white px-4 py-3
                                       text-sm
                                       disabled:bg-zinc-100"
                            >

                            <p
                                class="mt-2 text-xs
                                       text-zinc-500"
                            >
                                Describe the image
                                clearly for accessibility.
                            </p>

                            @error('altText')
                                <p
                                    class="mt-2
                                           text-sm font-medium
                                           text-red-600"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label
                            for="media-caption"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Caption
                        </label>

                        <textarea
                            id="media-caption"
                            rows="5"
                            maxlength="2000"
                            wire:model="caption"
                            @disabled(! $canUpdate)
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm leading-6
                                   disabled:bg-zinc-100"
                        ></textarea>

                        @error('caption')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="media-visibility"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800"
                        >
                            Visibility
                        </label>

                        <select
                            id="media-visibility"
                            wire:model="visibility"
                            @disabled(! $canUpdate)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm
                                   disabled:bg-zinc-100"
                        >
                            @foreach ($visibilities as $item)
                                <option
                                    value="{{ $item->value }}"
                                >
                                    {{ $item->label() }}
                                </option>
                            @endforeach
                        </select>

                        @error('visibility')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600"
                            >
                                {{ $message }}
                            </p>
                        @enderror

                        <p
                            class="mt-2 text-xs
                                   leading-5
                                   text-zinc-500"
                        >
                            Changing between Public and
                            Internal/Restricted will move
                            the stored file between the
                            configured public and private disks.
                        </p>
                    </div>
                </div>

                @if ($canUpdate)
                    <div
                        class="flex justify-end
                               border-t
                               border-zinc-200
                               bg-zinc-50
                               px-6 py-4"
                    >
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="rounded-xl
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
                                Save Changes
                            </span>

                            <span
                                wire:loading
                                wire:target="save"
                            >
                                Saving...
                            </span>
                        </button>
                    </div>
                @endif
            </form>

            {{-- Technical details --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-6 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        File Information
                    </h2>
                </div>

                <dl
                    class="grid gap-x-6 gap-y-5
                           p-6
                           md:grid-cols-2"
                >
                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Original Filename
                        </dt>

                        <dd class="mt-1 break-all text-sm text-zinc-800">
                            {{ $mediaAsset->original_name ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Stored Filename
                        </dt>

                        <dd class="mt-1 break-all text-sm text-zinc-800">
                            {{ $mediaAsset->stored_name ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            MIME Type
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $mediaAsset->mime_type ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Extension
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $mediaAsset->extension ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            File Size
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $fileSize }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Dimensions
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $dimensions }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Storage Disk
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $mediaAsset->disk ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Source
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{
                                $mediaAsset->source instanceof
                                \App\Enums\MediaSource
                                    ? $mediaAsset->source->label()
                                    : '—'
                            }}
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            SHA-256 Checksum
                        </dt>

                        <dd
                            class="mt-1 break-all
                                   rounded-lg bg-zinc-50
                                   p-3 font-mono
                                   text-xs text-zinc-700"
                        >
                            {{ $mediaAsset->checksum ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Uploaded By
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{ $mediaAsset->uploader?->name ?? 'Unknown' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            Uploaded
                        </dt>

                        <dd class="mt-1 text-sm text-zinc-800">
                            {{
                                $mediaAsset->created_at
                                    ?->format('Y-m-d H:i')
                                ?? '—'
                            }}
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            UUID
                        </dt>

                        <dd
                            class="mt-1 break-all
                                   font-mono text-xs
                                   text-zinc-700"
                        >
                            {{ $mediaAsset->uuid }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Preview --}}
        <aside>
            <section
                class="sticky top-6
                       overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm"
            >
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-5 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Preview
                    </h2>
                </div>

                <div class="p-5">
                    @if ($publicUrl)
                        <img
                            src="{{ $publicUrl }}"
                            alt="{{ $mediaAsset->alt_text ?? '' }}"
                            class="max-h-[520px]
                                   w-full
                                   rounded-xl
                                   bg-zinc-100
                                   object-contain"
                        >
                    @elseif ($mediaAsset->isDocument())
                        <div
                            class="flex min-h-64
                                   flex-col items-center
                                   justify-center
                                   rounded-xl bg-zinc-100"
                        >
                            <div
                                class="rounded-xl
                                       bg-white
                                       px-5 py-4
                                       text-2xl font-black
                                       text-red-700
                                       shadow-sm"
                            >
                                PDF
                            </div>

                            <p
                                class="mt-4 px-4
                                       text-center text-sm
                                       font-semibold
                                       text-zinc-700"
                            >
                                {{ $mediaAsset->original_name }}
                            </p>
                        </div>
                    @elseif (! $mediaAsset->isPublic())
                        <div
                            class="flex min-h-64
                                   flex-col items-center
                                   justify-center
                                   rounded-xl
                                   bg-zinc-100
                                   px-6 text-center"
                        >
                            <div
                                class="text-3xl"
                                aria-hidden="true"
                            >
                                🔒
                            </div>

                            <p
                                class="mt-3 text-sm
                                       font-bold
                                       text-zinc-700"
                            >
                                Private Media
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-zinc-500"
                            >
                                Direct private-file preview
                                is intentionally disabled.
                            </p>
                        </div>
                    @else
                        <div
                            class="flex min-h-64
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-zinc-100
                                   text-sm
                                   text-zinc-400"
                        >
                            No preview available
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>
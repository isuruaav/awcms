<div class="mx-auto max-w-7xl space-y-6">
    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-start
               lg:justify-between">
        <div>
            <div class="flex flex-wrap
                       items-center gap-3">
                <a href="{{ route('admin.media.index') }}"
                    class="text-sm font-semibold
                           text-emerald-700
                           hover:underline">
                    ← Media Library
                </a>
            </div>

            <h1 class="mt-3 text-2xl
                       font-bold
                       text-zinc-950">
                Media Details
            </h1>

            <p class="mt-1 text-sm
                       text-zinc-500">
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
                       text-emerald-800">
                You can edit this media asset.
            </div>
        @else
            <div
                class="rounded-xl
                       border border-zinc-200
                       bg-zinc-50
                       px-4 py-3
                       text-xs font-semibold
                       text-zinc-600">
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
                   text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-6">
            {{-- Editable metadata --}}
            <form wire:submit="save"
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm">
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold
                               text-zinc-900">
                        Media Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label for="media-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Title
                        </label>

                        <input id="media-title" type="text" maxlength="255" wire:model="title"
                            @disabled(!$canUpdate)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">

                        @error('title')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if ($mediaAsset->isImage())
                        <div>
                            <label for="media-alt"
                                class="mb-2 block
                                       text-sm font-semibold
                                       text-zinc-800">
                                Alternative Text
                            </label>

                            <input id="media-alt" type="text" maxlength="255" wire:model="altText"
                                @disabled(!$canUpdate)
                                class="w-full rounded-xl
                                       border border-zinc-300
                                       bg-white px-4 py-3
                                       text-sm
                                       disabled:bg-zinc-100">

                            <p class="mt-2 text-xs
                                       text-zinc-500">
                                Describe the image
                                clearly for accessibility.
                            </p>

                            @error('altText')
                                <p
                                    class="mt-2
                                           text-sm font-medium
                                           text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="media-caption"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Caption
                        </label>

                        <textarea id="media-caption" rows="5" maxlength="2000" wire:model="caption" @disabled(!$canUpdate)
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm leading-6
                                   disabled:bg-zinc-100"></textarea>

                        @error('caption')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="media-visibility"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Visibility
                        </label>

                        <select id="media-visibility" wire:model="visibility" @disabled(!$canUpdate)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white px-4 py-3
                                   text-sm
                                   disabled:bg-zinc-100">
                            @foreach ($visibilities as $item)
                                <option value="{{ $item->value }}">
                                    {{ $item->label() }}
                                </option>
                            @endforeach
                        </select>

                        @error('visibility')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p
                            class="mt-2 text-xs
                                   leading-5
                                   text-zinc-500">
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
                               px-6 py-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-xl
                                   bg-emerald-700
                                   px-5 py-3
                                   text-sm font-bold
                                   text-white
                                   hover:bg-emerald-800
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                Save Changes
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>
                    </div>
                @endif
            </form>
            {{-- Replace physical file --}}
            @if ($canReplace)
                <form wire:submit="replaceFile"
                    class="overflow-hidden
               rounded-2xl
               border border-amber-200
               bg-white shadow-sm">
                    <div
                        class="border-b
                   border-amber-200
                   bg-amber-50
                   px-6 py-4">
                        <div
                            class="flex flex-col gap-2
                       sm:flex-row
                       sm:items-center
                       sm:justify-between">
                            <div>
                                <h2 class="font-bold
                               text-zinc-900">
                                    Replace File
                                </h2>

                                <p
                                    class="mt-1 text-xs
                               leading-5
                               text-zinc-600">
                                    Replace the physical file while
                                    keeping this media asset's existing
                                    ID, UUID and content references.
                                </p>
                            </div>

                            <span
                                class="inline-flex
                           w-fit rounded-full
                           bg-amber-100
                           px-3 py-1
                           text-xs font-bold
                           text-amber-800">
                                File Replacement
                            </span>
                        </div>
                    </div>

                    <div class="space-y-5 p-6">

                        {{-- Current file --}}
                        <div
                            class="rounded-xl
                       border border-zinc-200
                       bg-zinc-50
                       p-4">
                            <p
                                class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500">
                                Current File
                            </p>

                            <p
                                class="mt-2 break-all
                           text-sm font-semibold
                           text-zinc-800">
                                {{ $mediaAsset->original_name ?? 'Unknown file' }}
                            </p>

                            <div
                                class="mt-3 flex flex-wrap
                           gap-x-5 gap-y-2
                           text-xs text-zinc-500">
                                <span>
                                    Type:
                                    <strong class="text-zinc-700">
                                        {{ $mediaAsset->mime_type ?? '—' }}
                                    </strong>
                                </span>

                                <span>
                                    Size:
                                    <strong class="text-zinc-700">
                                        {{ $fileSize }}
                                    </strong>
                                </span>

                                @if ($mediaAsset->isImage())
                                    <span>
                                        Dimensions:
                                        <strong class="text-zinc-700">
                                            {{ $dimensions }}
                                        </strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Replacement upload --}}
                        <div>
                            <label for="media-replacement-file"
                                class="mb-2 block
                           text-sm font-semibold
                           text-zinc-800">
                                Select Replacement File
                            </label>

                            <input id="media-replacement-file" type="file" wire:model="replacementFile"
                                @if ($replacementAccept !== '') accept="{{ $replacementAccept }}" @endif
                                class="block w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           text-sm text-zinc-700
                           file:mr-4
                           file:border-0
                           file:bg-zinc-100
                           file:px-4
                           file:py-3
                           file:text-sm
                           file:font-semibold
                           file:text-zinc-700
                           hover:file:bg-zinc-200">

                            @error('replacementFile')
                                <p
                                    class="mt-2
                               text-sm font-medium
                               text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <div wire:loading wire:target="replacementFile"
                                class="mt-2
                           text-xs font-semibold
                           text-blue-600">
                                Uploading selected file...
                            </div>

                            <div
                                class="mt-3 space-y-1
                           text-xs leading-5
                           text-zinc-500">
                                @if ($replacementAccept !== '')
                                    <p>
                                        Allowed:
                                        <span class="font-semibold text-zinc-700">
                                            {{ $replacementAccept }}
                                        </span>
                                    </p>
                                @endif

                                <p>
                                    Maximum file size:
                                    <span class="font-semibold text-zinc-700">
                                        {{ number_format($replacementMaxKb / 1024, 1) }} MB
                                    </span>
                                </p>

                                @if ($mediaAsset->isImage())
                                    <p>
                                        The replacement must be another
                                        approved image file. Thumbnail and
                                        medium WebP variants will be regenerated
                                        automatically.
                                    </p>
                                @elseif ($mediaAsset->isDocument())
                                    <p>
                                        The replacement must be another
                                        approved document of the same media type.
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Safety warning --}}
                        <div
                            class="rounded-xl
                       border border-amber-200
                       bg-amber-50
                       p-4">
                            <div class="flex gap-3">
                                <div class="text-lg" aria-hidden="true">
                                    ⚠️
                                </div>

                                <div>
                                    <p class="text-sm font-bold
                                   text-amber-900">
                                        Existing file will be replaced
                                    </p>

                                    <p
                                        class="mt-1 text-xs
                                   leading-5
                                   text-amber-800">
                                        The Media ID and UUID remain unchanged,
                                        so Pages, News, Galleries and other
                                        content referencing this media asset
                                        will continue using the same record.
                                        The old physical file will be removed
                                        after the replacement succeeds.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-3
                   border-t border-amber-200
                   bg-amber-50
                   px-6 py-4
                   sm:flex-row
                   sm:items-center
                   sm:justify-between">
                        <p class="text-xs
                       text-amber-800">
                            This operation is recorded in the
                            activity audit log.
                        </p>

                        <button type="submit"
                            wire:confirm="Replace the current physical file? The old file will be removed after the replacement succeeds."
                            wire:loading.attr="disabled" wire:target="replacementFile,replaceFile"
                            @disabled(!$replacementFile)
                            class="inline-flex
                       items-center
                       justify-center
                       rounded-xl
                       bg-amber-600
                       px-5 py-3
                       text-sm font-bold
                       text-white
                       hover:bg-amber-700
                       disabled:cursor-not-allowed
                       disabled:opacity-50">
                            <span wire:loading.remove wire:target="replaceFile">
                                Replace File
                            </span>

                            <span wire:loading wire:target="replaceFile">
                                Replacing...
                            </span>
                        </button>
                    </div>
                </form>
            @endif
            {{-- Technical details --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white shadow-sm">
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold
                               text-zinc-900">
                        File Information
                    </h2>
                </div>

                <dl
                    class="grid gap-x-6 gap-y-5
                           p-6
                           md:grid-cols-2">
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
                            {{ $mediaAsset->source instanceof \App\Enums\MediaSource ? $mediaAsset->source->label() : '—' }}
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
                                   text-xs text-zinc-700">
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
                            {{ $mediaAsset->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-xs font-bold uppercase text-zinc-500">
                            UUID
                        </dt>

                        <dd
                            class="mt-1 break-all
                                   font-mono text-xs
                                   text-zinc-700">
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
                       bg-white shadow-sm">
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-5 py-4">
                    <h2 class="font-bold
                               text-zinc-900">
                        Preview
                    </h2>
                </div>

                <div class="p-5">
                    @if ($publicUrl)
                        <img src="{{ $publicUrl }}" alt="{{ $mediaAsset->alt_text ?? '' }}"
                            class="max-h-[520px]
                                   w-full
                                   rounded-xl
                                   bg-zinc-100
                                   object-contain">
                    @elseif ($mediaAsset->isDocument())
                        <div
                            class="flex min-h-64
                                   flex-col items-center
                                   justify-center
                                   rounded-xl bg-zinc-100">
                            <div
                                class="rounded-xl
                                       bg-white
                                       px-5 py-4
                                       text-2xl font-black
                                       text-red-700
                                       shadow-sm">
                                PDF
                            </div>

                            <p
                                class="mt-4 px-4
                                       text-center text-sm
                                       font-semibold
                                       text-zinc-700">
                                {{ $mediaAsset->original_name }}
                            </p>
                        </div>
                    @elseif (!$mediaAsset->isPublic())
                        <div
                            class="flex min-h-64
                                   flex-col items-center
                                   justify-center
                                   rounded-xl
                                   bg-zinc-100
                                   px-6 text-center">
                            <div class="text-3xl" aria-hidden="true">
                                🔒
                            </div>

                            <p
                                class="mt-3 text-sm
                                       font-bold
                                       text-zinc-700">
                                Private Media
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-zinc-500">
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
                                   text-zinc-400">
                            No preview available
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>

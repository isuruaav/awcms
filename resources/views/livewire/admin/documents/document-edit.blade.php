<div class="space-y-6">

    @php
        $status = $document->status;

        $statusClasses = match ($status) {
            \App\Enums\DocumentStatus::Draft => 'bg-zinc-100 text-zinc-700',

            \App\Enums\DocumentStatus::Published => 'bg-emerald-100 text-emerald-700',

            \App\Enums\DocumentStatus::Archived => 'bg-amber-100 text-amber-700',

            default => 'bg-red-100 text-red-700',
        };

        $canAddVersion =
            $status === \App\Enums\DocumentStatus::Draft ||
            ($status === \App\Enums\DocumentStatus::Published &&
                \Illuminate\Support\Facades\Gate::allows('documents.publish'));

        $canRestoreVersion =
            $status === \App\Enums\DocumentStatus::Draft ||
            ($status === \App\Enums\DocumentStatus::Published &&
                \Illuminate\Support\Facades\Gate::allows('documents.publish'));

        $stablePublicUrl = route('documents.show', [
            'slug' => $document->slug,
        ]);

        $isPubliclyAvailable =
            $status === \App\Enums\DocumentStatus::Published &&
            $document->published_at !== null &&
            $document->published_at->lte(now()) &&
            $document->current_version > 0 &&
            $currentVersion !== null &&
            $currentVersion->media !== null;
    @endphp


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-center
               lg:justify-between">
        <div>
            <p
                class="text-xs font-bold
                       uppercase tracking-[0.18em]
                       text-emerald-700">
                Document Management
            </p>

            <h1
                class="mt-1
                       text-2xl font-black
                       tracking-tight
                       text-zinc-950">
                Edit Document
            </h1>

            <div
                class="mt-2
                       flex flex-wrap
                       items-center
                       gap-2">
                <span
                    class="inline-flex
                           rounded-full
                           px-2.5 py-1
                           text-xs font-bold
                           {{ $statusClasses }}">
                    {{ $status instanceof \App\Enums\DocumentStatus ? $status->label() : 'Invalid' }}
                </span>

                <span class="text-xs text-zinc-400">
                    Document #{{ $document->id }}
                </span>

                @if ($document->current_version > 0)
                    <span
                        class="inline-flex
                               rounded-full
                               bg-blue-50
                               px-2.5 py-1
                               text-xs font-bold
                               text-blue-700">
                        Current PDF v{{ $document->current_version }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap
                   items-center
                   gap-3">
            @can('documents.categories.manage')
                <a href="{{ route('admin.documents.categories.index') }}" wire:navigate
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
                           hover:bg-zinc-50">
                    Manage Categories
                </a>
            @endcan

            <a href="{{ route('admin.documents.index') }}" wire:navigate
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
                       hover:bg-zinc-50">
                Back to Documents
            </a>
        </div>
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
                   text-emerald-800">
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
                   text-sm text-red-700">
            <p class="font-bold">
                The action could not be completed.
            </p>

            <ul
                class="mt-2
                       list-disc
                       space-y-1
                       pl-5">
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

    @if (!$editable)
        <div
            class="rounded-xl
                   border border-amber-200
                   bg-amber-50
                   px-4 py-3">
            <p class="text-sm font-bold
                       text-amber-800">
                Metadata editing locked
            </p>

            <p class="mt-1
                       text-xs leading-5
                       text-amber-700">
                Only Draft documents can have their title,
                slug, category, dates, description and SEO
                information edited.
            </p>

            @if ($status === \App\Enums\DocumentStatus::Published)
                <p class="mt-2
                           text-xs leading-5
                           text-amber-700">
                    An authorised Publisher may still add a new
                    PDF version to a published document without
                    changing its stable document URL.
                </p>
            @endif
        </div>
    @endif


    <form wire:submit="save" class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_360px]">

        {{-- =================================================
             MAIN CONTENT
        ================================================== --}}

        <div class="space-y-6">

            {{-- =============================================
                 DOCUMENT INFORMATION
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Document Information
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Main information shown in the
                        administration and public document listing.
                    </p>
                </div>

                <div class="space-y-5 p-6">

                    {{-- Title --}}

                    <div>
                        <label for="document-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Document Title
                        </label>

                        <input id="document-title" type="text" wire:model="title" maxlength="255"
                            @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm text-zinc-900
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">

                        @error('title')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Slug --}}

                    <div>
                        <label for="document-slug"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            URL Slug
                        </label>

                        <input id="document-slug" type="text" wire:model="slug" maxlength="255"
                            @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm text-zinc-900
                                   disabled:bg-zinc-100">

                        <p
                            class="mt-1.5
                                   text-xs
                                   text-zinc-500">
                            Stable public document path:
                            /documents/{{ $document->slug }}
                        </p>

                        @error('slug')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Category --}}

                    <div>
                        <label for="document-category"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Category
                        </label>

                        <select id="document-category" wire:model="categoryId" @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   disabled:bg-zinc-100">
                            <option value="">
                                No category
                            </option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('categoryId')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Document Date --}}

                    <div>
                        <label for="document-date"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Document Date
                        </label>

                        <input id="document-date" type="date" wire:model="documentDate" @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   disabled:bg-zinc-100">

                        @error('documentDate')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Description --}}

                    <div>
                        <label for="document-description"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Description
                        </label>

                        <textarea id="document-description" wire:model="description" rows="7" maxlength="10000"
                            @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm leading-6
                                   disabled:bg-zinc-100"></textarea>

                        @error('description')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </section>


            {{-- =============================================
                 PDF VERSION MANAGEMENT
            ============================================== --}}

            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div
                    class="flex flex-col gap-2
                           border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4
                           sm:flex-row
                           sm:items-center
                           sm:justify-between">
                    <div>
                        <h2 class="font-bold text-zinc-900">
                            PDF Versions
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $document->versions->count() }}
                            version(s) retained in history.
                        </p>
                    </div>

                    @if ($document->current_version > 0)
                        <span
                            class="inline-flex
                                   w-fit
                                   rounded-full
                                   bg-emerald-100
                                   px-3 py-1
                                   text-xs font-bold
                                   text-emerald-700">
                            Current v{{ $document->current_version }}
                        </span>
                    @endif
                </div>


                {{-- ADD VERSION --}}

                @can('documents.update')
                    @if ($canAddVersion)
                        <div
                            class="border-b border-zinc-200
                                   bg-emerald-50/40
                                   p-6">
                            <h3 class="text-sm font-bold
                                       text-zinc-900">
                                Add New PDF Version
                            </h3>

                            @if ($status === \App\Enums\DocumentStatus::Published)
                                <div
                                    class="mt-3
                                           rounded-xl
                                           border border-amber-200
                                           bg-amber-50
                                           px-4 py-3
                                           text-xs leading-5
                                           text-amber-800">
                                    This document is already published.
                                    Adding a new version will make the new
                                    PDF the current live version immediately.
                                    Previous versions remain in history.
                                </div>
                            @endif

                            <div class="mt-4 space-y-4">

                                <div>
                                    <label for="document-pdf-media"
                                        class="mb-1.5 block
                                               text-sm font-semibold
                                               text-zinc-700">
                                        PDF from Media Library
                                    </label>

                                    <select id="document-pdf-media" wire:model="selectedMediaId"
                                        class="w-full
                                               rounded-xl
                                               border border-zinc-300
                                               bg-white
                                               px-3 py-2.5
                                               text-sm">
                                        <option value="">
                                            Select Public PDF...
                                        </option>

                                        @foreach ($mediaAssets as $asset)
                                            <option value="{{ $asset->id }}">
                                                #{{ $asset->id }}
                                                —
                                                {{ $asset->title ?: $asset->original_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($mediaAssets->isEmpty())
                                        <p
                                            class="mt-2
                                                   text-xs
                                                   text-amber-700">
                                            No unused Public PDF files are
                                            available in the Media Library.
                                        </p>
                                    @endif

                                    @error('selectedMediaId')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>


                                <div>
                                    <label for="document-version-label"
                                        class="mb-1.5 block
                                               text-sm font-semibold
                                               text-zinc-700">
                                        Version Label
                                    </label>

                                    <input id="document-version-label" type="text" wire:model="versionLabel"
                                        maxlength="100"
                                        class="w-full
                                               rounded-xl
                                               border border-zinc-300
                                               bg-white
                                               px-3 py-2.5
                                               text-sm"
                                        placeholder="Example: Version 1.0">

                                    @error('versionLabel')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>


                                <div>
                                    <label for="document-change-note"
                                        class="mb-1.5 block
                                               text-sm font-semibold
                                               text-zinc-700">
                                        Change Note
                                    </label>

                                    <textarea id="document-change-note" wire:model="changeNote" maxlength="2000" rows="4"
                                        class="w-full
                                               rounded-xl
                                               border border-zinc-300
                                               bg-white
                                               px-3 py-2.5
                                               text-sm leading-6"
                                        placeholder="Describe what changed in this PDF version..."></textarea>

                                    @error('changeNote')
                                        <p class="mt-1.5 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>


                            <div
                                class="mt-4
                                       flex flex-wrap
                                       items-center
                                       gap-3">
                                <button type="button" wire:click="addVersion" wire:loading.attr="disabled"
                                    wire:target="addVersion"
                                    class="inline-flex
                                           items-center
                                           justify-center
                                           rounded-xl
                                           bg-emerald-700
                                           px-4 py-2.5
                                           text-sm font-bold
                                           text-white
                                           hover:bg-emerald-800
                                           disabled:cursor-not-allowed
                                           disabled:opacity-60">
                                    <span wire:loading.remove wire:target="addVersion">
                                        Add PDF Version
                                    </span>

                                    <span wire:loading wire:target="addVersion">
                                        Adding...
                                    </span>
                                </button>

                                @can('media.view')
                                    <a href="{{ route('admin.media.index') }}" target="_blank"
                                        class="inline-flex
                                               text-sm font-semibold
                                               text-emerald-700
                                               hover:text-emerald-800">
                                        Open Media Library →
                                    </a>
                                @endcan
                            </div>

                        </div>
                    @elseif ($status === \App\Enums\DocumentStatus::Published)
                        <div
                            class="border-b border-zinc-200
                                   bg-amber-50
                                   px-6 py-4
                                   text-sm text-amber-800">
                            Replacing the live PDF requires
                            <strong>documents.publish</strong>
                            permission.
                        </div>
                    @endif
                @endcan


                {{-- VERSION HISTORY --}}

                <div class="p-6">

                    @if ($document->versions->isNotEmpty())

                        <div class="space-y-4">

                            @foreach ($document->versions as $version)
                                @php
                                    $media = $version->media;

                                    $isCurrent = $version->version === $document->current_version;
                                @endphp

                                <article wire:key="document-version-{{ $version->id }}"
                                    class="rounded-2xl
                                           border
                                           {{ $isCurrent ? 'border-emerald-300 bg-emerald-50/40' : 'border-zinc-200 bg-zinc-50' }}
                                           p-5">
                                    <div
                                        class="flex flex-col gap-4
                                               lg:flex-row
                                               lg:items-start
                                               lg:justify-between">
                                        <div class="min-w-0">

                                            <div
                                                class="flex flex-wrap
                                                       items-center
                                                       gap-2">
                                                <span
                                                    class="inline-flex
                                                           rounded-lg
                                                           bg-zinc-900
                                                           px-2.5 py-1
                                                           text-sm font-black
                                                           text-white">
                                                    v{{ $version->version }}
                                                </span>

                                                @if ($isCurrent)
                                                    <span
                                                        class="inline-flex
                                                               rounded-full
                                                               bg-emerald-100
                                                               px-2.5 py-1
                                                               text-xs font-bold
                                                               text-emerald-700">
                                                        Current Version
                                                    </span>
                                                @endif

                                                @if ($version->version_label)
                                                    <span
                                                        class="text-sm
                                                               font-semibold
                                                               text-zinc-700">
                                                        {{ $version->version_label }}
                                                    </span>
                                                @endif
                                            </div>


                                            <div class="mt-3">

                                                @if ($media)
                                                    <p
                                                        class="font-bold
                                                               text-zinc-900">
                                                        {{ $media->title ?: $media->original_name }}
                                                    </p>

                                                    <div
                                                        class="mt-1
                                                               flex flex-wrap
                                                               gap-x-4 gap-y-1
                                                               text-xs
                                                               text-zinc-500">
                                                        <span>
                                                            Media #{{ $version->media_asset_id }}
                                                        </span>

                                                        @if ($media->original_name)
                                                            <span>
                                                                {{ $media->original_name }}
                                                            </span>
                                                        @endif

                                                        @if ($media->size_bytes)
                                                            <span>
                                                                {{ number_format($media->size_bytes / 1024, 1) }}
                                                                KB
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p
                                                        class="font-bold
                                                               text-red-700">
                                                        Media record unavailable
                                                    </p>
                                                @endif

                                            </div>


                                            @if ($version->change_note)
                                                <div
                                                    class="mt-4
                                                           rounded-xl
                                                           border border-zinc-200
                                                           bg-white
                                                           px-4 py-3">
                                                    <p
                                                        class="text-xs
                                                               font-bold
                                                               uppercase
                                                               tracking-wide
                                                               text-zinc-500">
                                                        Change Note
                                                    </p>

                                                    <p
                                                        class="mt-1
                                                               whitespace-pre-line
                                                               text-sm leading-6
                                                               text-zinc-700">
                                                        {{ $version->change_note }}
                                                    </p>
                                                </div>
                                            @endif

                                        </div>


                                        <div
                                            class="shrink-0
           space-y-3
           text-left
           lg:text-right">
                                            <div class="text-xs
               leading-5
               text-zinc-500">
                                                <p>
                                                    Added:
                                                    {{ $version->created_at?->format('d M Y H:i') ?? '—' }}
                                                </p>

                                                <p>
                                                    By:
                                                    {{ $version->uploader?->name ?? 'System / unavailable' }}
                                                </p>
                                            </div>

                                            @if (!$isCurrent && $canRestoreVersion && $media)
                                                @can('documents.update')
                                                    @if ($status === \App\Enums\DocumentStatus::Published)
                                                        <button type="button"
                                                            wire:click="restoreVersion({{ $version->id }})"
                                                            wire:confirm="Restore PDF version {{ $version->version }}? This will immediately replace the current live PDF while keeping all versions in history."
                                                            wire:loading.attr="disabled" wire:target="restoreVersion"
                                                            class="inline-flex
                           items-center
                           justify-center
                           rounded-xl
                           border border-amber-300
                           bg-amber-50
                           px-3 py-2
                           text-xs font-bold
                           text-amber-800
                           transition
                           hover:bg-amber-100
                           disabled:cursor-not-allowed
                           disabled:opacity-60">
                                                            <span wire:loading.remove wire:target="restoreVersion">
                                                                Restore v{{ $version->version }}
                                                            </span>

                                                            <span wire:loading wire:target="restoreVersion">
                                                                Restoring...
                                                            </span>
                                                        </button>
                                                    @elseif ($status === \App\Enums\DocumentStatus::Draft)
                                                        <button type="button"
                                                            wire:click="restoreVersion({{ $version->id }})"
                                                            wire:confirm="Restore PDF version {{ $version->version }} as the current version?"
                                                            wire:loading.attr="disabled" wire:target="restoreVersion"
                                                            class="inline-flex
                           items-center
                           justify-center
                           rounded-xl
                           border border-blue-300
                           bg-blue-50
                           px-3 py-2
                           text-xs font-bold
                           text-blue-700
                           transition
                           hover:bg-blue-100
                           disabled:cursor-not-allowed
                           disabled:opacity-60">
                                                            <span wire:loading.remove wire:target="restoreVersion">
                                                                Restore v{{ $version->version }}
                                                            </span>

                                                            <span wire:loading wire:target="restoreVersion">
                                                                Restoring...
                                                            </span>
                                                        </button>
                                                    @endif
                                                @endcan
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
                                   text-center">
                            <p class="font-bold
                                       text-zinc-800">
                                No PDF versions added yet
                            </p>

                            <p
                                class="mt-1
                                       text-sm
                                       text-zinc-500">
                                Add at least one valid Public PDF
                                before publishing this document.
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
                       shadow-sm">
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Search Engine Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">

                    <div>
                        <label for="document-seo-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            SEO Title
                        </label>

                        <input id="document-seo-title" type="text" wire:model="seoTitle" maxlength="255"
                            @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   disabled:bg-zinc-100">

                        @error('seoTitle')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div>
                        <label for="document-seo-description"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            SEO Description
                        </label>

                        <textarea id="document-seo-description" wire:model="seoDescription" rows="4" maxlength="320"
                            @disabled(!$editable)
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm leading-6
                                   disabled:bg-zinc-100"></textarea>

                        <p
                            class="mt-1
                                   text-right
                                   text-xs
                                   text-zinc-400">
                            {{ mb_strlen($seoDescription) }}/320
                        </p>

                        @error('seoDescription')
                            <p class="mt-1.5 text-sm text-red-600">
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
                       shadow-sm">
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500">
                    Status
                </p>

                <span
                    class="mt-3
                           inline-flex
                           rounded-full
                           px-3 py-1.5
                           text-sm font-bold
                           {{ $statusClasses }}">
                    {{ $status instanceof \App\Enums\DocumentStatus ? $status->label() : 'Invalid' }}
                </span>

                @if ($status === \App\Enums\DocumentStatus::Published)
                    <p
                        class="mt-3
                               text-xs leading-5
                               text-zinc-500">
                        Published:
                        {{ $document->published_at?->format('d M Y H:i') ?? '—' }}
                    </p>
                @endif

                @if ($status === \App\Enums\DocumentStatus::Archived)
                    <p
                        class="mt-3
                               text-xs leading-5
                               text-zinc-500">
                        Archived:
                        {{ $document->archived_at?->format('d M Y H:i') ?? '—' }}
                    </p>
                @endif
            </section>


            {{-- PUBLIC URL --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm">
                <div
                    class="flex items-start
                           justify-between
                           gap-3">
                    <div>
                        <h2 class="font-bold
                                   text-zinc-900">
                            Public URL
                        </h2>

                        <p
                            class="mt-1
                                   text-xs leading-5
                                   text-zinc-500">
                            Stable document link. Replacing or restoring
                            a PDF version does not change this URL.
                        </p>
                    </div>

                    @if ($isPubliclyAvailable)
                        <span
                            class="inline-flex
                                   shrink-0
                                   rounded-full
                                   bg-emerald-100
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-emerald-700">
                            Live
                        </span>
                    @else
                        <span
                            class="inline-flex
                                   shrink-0
                                   rounded-full
                                   bg-zinc-100
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-zinc-600">
                            Not Live
                        </span>
                    @endif
                </div>

                <div
                    class="mt-4
                           rounded-xl
                           border border-zinc-200
                           bg-zinc-50
                           p-3">
                    <p
                        class="break-all
                               text-xs leading-5
                               text-zinc-600">
                        {{ $stablePublicUrl }}
                    </p>
                </div>

                <div x-data="{
                    copied: false,
                
                    async copyLink() {
                        const url = @js($stablePublicUrl);
                
                        try {
                            await navigator.clipboard.writeText(url);
                
                            this.copied = true;
                
                            setTimeout(() => {
                                this.copied = false;
                            }, 1800);
                        } catch (error) {
                            this.copied = false;
                        }
                    }
                }" class="mt-4
                           grid gap-2">
                    <button type="button" x-on:click="copyLink"
                        class="inline-flex
                               w-full
                               items-center
                               justify-center
                               rounded-xl
                               border border-sky-200
                               bg-sky-50
                               px-4 py-2.5
                               text-sm font-bold
                               text-sky-700
                               transition
                               hover:bg-sky-100">
                        <span x-show="!copied">
                            Copy Public Link
                        </span>

                        <span x-show="copied" x-cloak>
                            Copied
                        </span>
                    </button>

                    @if ($isPubliclyAvailable)
                        <a href="{{ $stablePublicUrl }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex
                                   w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   border border-emerald-200
                                   bg-emerald-50
                                   px-4 py-2.5
                                   text-sm font-bold
                                   text-emerald-700
                                   transition
                                   hover:bg-emerald-100">
                            View Public Page
                        </a>
                    @else
                        <div
                            class="rounded-xl
                                   border border-amber-200
                                   bg-amber-50
                                   px-3 py-2.5
                                   text-xs leading-5
                                   text-amber-800">
                            <p>
                                This URL is reserved for the document,
                                but the public page is not currently available.
                            </p>

                            @if ($status === \App\Enums\DocumentStatus::Draft)
                                <p class="mt-1">
                                    Publish the document to make it public.
                                </p>
                            @elseif ($status === \App\Enums\DocumentStatus::Archived)
                                <p class="mt-1">
                                    Archived documents are not publicly visible.
                                </p>
                            @elseif ($document->published_at !== null && $document->published_at->isFuture())
                                <p class="mt-1">
                                    Public visibility is scheduled for
                                    {{ $document->published_at->format('d M Y H:i') }}.
                                </p>
                            @elseif ($document->current_version < 1)
                                <p class="mt-1">
                                    Add a valid PDF version before publishing.
                                </p>
                            @elseif ($currentVersion === null || $currentVersion->media === null)
                                <p class="mt-1">
                                    The current PDF media record is unavailable.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </section>


            {{-- CURRENT PDF --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm">
                <h2 class="font-bold text-zinc-900">
                    Current PDF
                </h2>

                @if ($currentVersion)

                    <div class="mt-4">
                        <span
                            class="inline-flex
                                   rounded-lg
                                   bg-emerald-100
                                   px-3 py-1.5
                                   text-sm font-black
                                   text-emerald-700">
                            Version {{ $currentVersion->version }}
                        </span>

                        @if ($currentVersion->version_label)
                            <p
                                class="mt-3
                                       text-sm font-semibold
                                       text-zinc-700">
                                {{ $currentVersion->version_label }}
                            </p>
                        @endif

                        <p
                            class="mt-2
                                   break-words
                                   text-xs leading-5
                                   text-zinc-500">
                            {{ $currentVersion->media?->title ?: $currentVersion->media?->original_name ?: 'Media unavailable' }}
                        </p>
                    </div>
                @else
                    <div
                        class="mt-4
                               rounded-xl
                               bg-red-50
                               px-4 py-3
                               text-sm
                               font-semibold
                               text-red-700">
                        No current PDF version.
                    </div>

                @endif
            </section>


            {{-- PUBLICATION DATE --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm">
                <h2 class="font-bold text-zinc-900">
                    Publication
                </h2>

                <label for="document-publish-date"
                    class="mt-4 mb-1.5 block
                           text-sm font-semibold
                           text-zinc-700">
                    Publish Date
                </label>

                <input id="document-publish-date" type="datetime-local" wire:model="publishedAt"
                    @disabled(!$editable)
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-3 py-2.5
                           text-sm
                           disabled:bg-zinc-100">

                <p class="mt-2
                           text-xs leading-5
                           text-zinc-500">
                    A future date can be used for
                    scheduled public visibility.
                </p>

                @error('publishedAt')
                    <p class="mt-1.5 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </section>


            {{-- SAVE --}}

            @if ($editable)
                <section
                    class="rounded-2xl
                           border border-zinc-200
                           bg-white
                           p-5
                           shadow-sm">
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
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
                               disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">
                            Save Document
                        </span>

                        <span wire:loading wire:target="save">
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
                       shadow-sm">
                <h2 class="font-bold text-zinc-900">
                    Workflow
                </h2>

                <div class="mt-4 space-y-3">

                    @can('documents.publish')
                        @if ($status === \App\Enums\DocumentStatus::Draft)
                            @if ($document->current_version > 0)
                                <button type="button" wire:click="publish" wire:confirm="Publish this document?"
                                    wire:loading.attr="disabled" wire:target="publish"
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
                                           disabled:opacity-60">
                                    Publish Document
                                </button>
                            @else
                                <div
                                    class="rounded-xl
                                           border border-amber-200
                                           bg-amber-50
                                           px-4 py-3
                                           text-xs leading-5
                                           text-amber-800">
                                    Add a valid PDF version before
                                    publishing this document.
                                </div>
                            @endif
                        @endif
                    @endcan


                    @can('documents.archive')
                        @if ($status === \App\Enums\DocumentStatus::Published)
                            <button type="button" wire:click="archive" wire:confirm="Archive this document?"
                                wire:loading.attr="disabled" wire:target="archive"
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
                                       disabled:opacity-60">
                                Archive Document
                            </button>
                        @endif
                    @endcan


                    <a href="{{ route('admin.documents.index') }}" wire:navigate
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
                               hover:bg-zinc-50">
                        Back to Documents
                    </a>

                </div>
            </section>

        </aside>

    </form>

</div>

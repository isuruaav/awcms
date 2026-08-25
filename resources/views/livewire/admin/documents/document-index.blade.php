<div class="space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col
               gap-4
               sm:flex-row
               sm:items-center
               sm:justify-between">
        <div>
            <p
                class="text-xs
                       font-bold
                       uppercase
                       tracking-[0.18em]
                       text-emerald-700">
                Content Management
            </p>

            <h1
                class="mt-1
                       text-2xl
                       font-black
                       tracking-tight
                       text-zinc-950">
                Documents
            </h1>

            <p
                class="mt-1
                       text-sm
                       leading-6
                       text-zinc-500">
                Create and manage public PDF documents,
                publications, reports and downloadable files.
            </p>
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
                           text-sm
                           font-bold
                           text-zinc-700
                           shadow-sm
                           transition
                           hover:bg-zinc-50">
                    Manage Categories
                </a>
            @endcan

            @can('documents.create')
                <a href="{{ route('admin.documents.create') }}" wire:navigate
                    class="inline-flex
                           items-center
                           justify-center
                           rounded-xl
                           bg-emerald-700
                           px-4 py-2.5
                           text-sm
                           font-bold
                           text-white
                           shadow-sm
                           transition
                           hover:bg-emerald-800">
                    Create Document
                </a>
            @endcan
        </div>
    </div>


    {{-- =====================================================
         FLASH MESSAGE
    ====================================================== --}}

    @if (session('status'))
        <div
            class="rounded-xl
                   border
                   border-emerald-200
                   bg-emerald-50
                   px-4 py-3
                   text-sm
                   font-semibold
                   text-emerald-800">
            {{ session('status') }}
        </div>
    @endif


    {{-- =====================================================
         ERRORS
    ====================================================== --}}

    @if ($errors->any())
        <div
            class="rounded-xl
                   border
                   border-red-200
                   bg-red-50
                   px-4 py-3
                   text-sm
                   text-red-700">
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
         SUMMARY CARDS
    ====================================================== --}}

    <section class="grid gap-4
               sm:grid-cols-2
               xl:grid-cols-4">
        <div
            class="rounded-2xl
                   border border-zinc-200
                   bg-white
                   p-5
                   shadow-sm">
            <p
                class="text-xs
                       font-bold
                       uppercase
                       tracking-wide
                       text-zinc-500">
                Total Documents
            </p>

            <p
                class="mt-2
                       text-3xl
                       font-black
                       text-zinc-950">
                {{ $totalCount }}
            </p>
        </div>

        <div
            class="rounded-2xl
                   border border-zinc-200
                   bg-white
                   p-5
                   shadow-sm">
            <p
                class="text-xs
                       font-bold
                       uppercase
                       tracking-wide
                       text-zinc-500">
                Draft
            </p>

            <p
                class="mt-2
                       text-3xl
                       font-black
                       text-zinc-700">
                {{ $draftCount }}
            </p>
        </div>

        <div
            class="rounded-2xl
                   border border-zinc-200
                   bg-white
                   p-5
                   shadow-sm">
            <p
                class="text-xs
                       font-bold
                       uppercase
                       tracking-wide
                       text-zinc-500">
                Published
            </p>

            <p
                class="mt-2
                       text-3xl
                       font-black
                       text-emerald-700">
                {{ $publishedCount }}
            </p>
        </div>

        <div
            class="rounded-2xl
                   border border-zinc-200
                   bg-white
                   p-5
                   shadow-sm">
            <p
                class="text-xs
                       font-bold
                       uppercase
                       tracking-wide
                       text-zinc-500">
                Archived
            </p>

            <p
                class="mt-2
                       text-3xl
                       font-black
                       text-amber-700">
                {{ $archivedCount }}
            </p>
        </div>
    </section>


    {{-- =====================================================
         FILTERS
    ====================================================== --}}

    <section
        class="rounded-2xl
               border border-zinc-200
               bg-white
               p-5
               shadow-sm">
        <div class="grid gap-4
                   lg:grid-cols-[minmax(0,1fr)_220px_130px_auto]">
            <div>
                <label for="document-search"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Search
                </label>

                <input id="document-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Title, slug, description or category..."
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
                           focus:ring-emerald-100">
            </div>

            <div>
                <label for="document-status"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Status
                </label>

                <select id="document-status" wire:model.live="statusFilter"
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-3 py-2.5
                           text-sm
                           text-zinc-900">
                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="document-per-page"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Rows
                </label>

                <select id="document-per-page" wire:model.live="perPage"
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-3 py-2.5
                           text-sm">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button" wire:click="resetFilters"
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-4 py-2.5
                           text-sm
                           font-semibold
                           text-zinc-700
                           transition
                           hover:bg-zinc-50
                           lg:w-auto">
                    Reset
                </button>
            </div>
        </div>
    </section>


    {{-- =====================================================
         TABLE
    ====================================================== --}}

    <section
        class="overflow-hidden
               rounded-2xl
               border border-zinc-200
               bg-white
               shadow-sm">
        <div class="overflow-x-auto">

            <table class="min-w-full
                       divide-y
                       divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>

                        {{-- DOCUMENT --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('title')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600
                                       hover:text-zinc-950">
                                Document

                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- STATUS --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('status')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Status

                                @if ($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- CATEGORY --}}
                        <th
                            class="px-5 py-3
                                   text-left
                                   text-xs
                                   font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-600">
                            Category
                        </th>


                        {{-- DATE --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('document_date')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Document Date

                                @if ($sortField === 'document_date')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- VERSION --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('current_version')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Version

                                @if ($sortField === 'current_version')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- PUBLISHED --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('published_at')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Published

                                @if ($sortField === 'published_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- UPDATED --}}
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('updated_at')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Updated

                                @if ($sortField === 'updated_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>


                        {{-- ACTIONS --}}
                        <th
                            class="px-5 py-3
                                   text-right
                                   text-xs
                                   font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-600">
                            Actions
                        </th>

                    </tr>
                </thead>


                <tbody class="divide-y
                           divide-zinc-100">
                    @forelse ($documents as $document)

                        @php
                            $status = $document->status;

                            $statusClasses = match ($status) {
                                \App\Enums\DocumentStatus::Draft => 'bg-zinc-100 text-zinc-700',

                                \App\Enums\DocumentStatus::Published => 'bg-emerald-100 text-emerald-700',

                                \App\Enums\DocumentStatus::Archived => 'bg-amber-100 text-amber-700',

                                default => 'bg-zinc-100 text-zinc-600',
                            };

                            $isPubliclyAvailable =
                                $status === \App\Enums\DocumentStatus::Published &&
                                $document->published_at !== null &&
                                $document->published_at->lte(now()) &&
                                $document->current_version > 0;

                            $publicUrl = $isPubliclyAvailable
                                ? route('documents.show', [
                                    'slug' => $document->slug,
                                ])
                                : null;
                        @endphp


                        <tr wire:key="document-{{ $document->id }}" class="align-top">

                            {{-- DOCUMENT --}}
                            <td class="px-5 py-4">

                                <div class="min-w-64">

                                    <p class="font-bold
                                               text-zinc-900">
                                        {{ $document->title }}
                                    </p>

                                    <p
                                        class="mt-1
                                               text-xs
                                               text-zinc-500">
                                        /{{ $document->slug }}
                                    </p>

                                    @if ($document->description)
                                        <p
                                            class="mt-2
                                                   max-w-md
                                                   text-sm
                                                   leading-5
                                                   text-zinc-500">
                                            {{ \Illuminate\Support\Str::limit($document->description, 110) }}
                                        </p>
                                    @endif

                                </div>

                            </td>


                            {{-- STATUS --}}
                            <td class="px-5 py-4">

                                @if ($status instanceof \App\Enums\DocumentStatus)
                                    <span
                                        class="inline-flex
                                               rounded-full
                                               px-2.5 py-1
                                               text-xs
                                               font-bold
                                               {{ $statusClasses }}">
                                        {{ $status->label() }}
                                    </span>

                                    @if ($status === \App\Enums\DocumentStatus::Published && !$isPubliclyAvailable)
                                        <p
                                            class="mt-2
                                                   max-w-28
                                                   text-xs
                                                   leading-4
                                                   text-zinc-400">
                                            @if ($document->published_at !== null && $document->published_at->isFuture())
                                                Scheduled
                                            @elseif ($document->current_version < 1)
                                                No public PDF
                                            @else
                                                Not public yet
                                            @endif
                                        </p>
                                    @endif
                                @else
                                    <span class="text-sm
                                               text-red-600">
                                        Invalid
                                    </span>
                                @endif

                            </td>


                            {{-- CATEGORY --}}
                            <td
                                class="px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                @if ($document->category)
                                    <span
                                        class="inline-flex
                                               rounded-lg
                                               bg-zinc-100
                                               px-2.5 py-1
                                               text-xs
                                               font-semibold
                                               text-zinc-700">
                                        {{ $document->category->name }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">
                                        —
                                    </span>
                                @endif

                            </td>


                            {{-- DOCUMENT DATE --}}
                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $document->document_date?->format('d M Y') ?? '—' }}
                            </td>


                            {{-- VERSION --}}
                            <td class="px-5 py-4">

                                @if ($document->current_version > 0)
                                    <div
                                        class="flex
                                               flex-col
                                               gap-1">
                                        <span
                                            class="inline-flex
                                                   w-fit
                                                   min-w-10
                                                   justify-center
                                                   rounded-lg
                                                   bg-zinc-100
                                                   px-2.5 py-1
                                                   text-sm
                                                   font-bold
                                                   text-zinc-700">
                                            v{{ $document->current_version }}
                                        </span>

                                        <span
                                            class="text-xs
                                                   text-zinc-400">
                                            {{ $document->versions_count }}
                                            {{ $document->versions_count === 1 ? 'file' : 'files' }}
                                        </span>
                                    </div>
                                @else
                                    <span
                                        class="inline-flex
                                               rounded-lg
                                               bg-red-50
                                               px-2.5 py-1
                                               text-xs
                                               font-semibold
                                               text-red-700">
                                        No PDF
                                    </span>
                                @endif

                            </td>


                            {{-- PUBLISHED --}}
                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $document->published_at?->format('d M Y H:i') ?? '—' }}
                            </td>


                            {{-- UPDATED --}}
                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $document->updated_at?->format('d M Y H:i') ?? '—' }}
                            </td>


                            {{-- ACTIONS --}}
                            <td class="px-5 py-4">

                                <div
                                    class="flex
                                           min-w-max
                                           flex-wrap
                                           justify-end
                                           gap-2">

                                    {{-- PUBLIC VIEW --}}
                                    @if ($isPubliclyAvailable && is_string($publicUrl))
                                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="rounded-lg
                                                   border
                                                   border-emerald-200
                                                   bg-emerald-50
                                                   px-3 py-2
                                                   text-xs
                                                   font-bold
                                                   text-emerald-700
                                                   transition
                                                   hover:bg-emerald-100">
                                            View
                                        </a>


                                        {{-- COPY STABLE PUBLIC LINK --}}
                                        <div x-data="{
                                            copied: false,
                                        
                                            async copyLink() {
                                                const url = @js($publicUrl);
                                        
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
                                        }">
                                            <button type="button" x-on:click="copyLink"
                                                class="rounded-lg
                                                       border
                                                       border-sky-200
                                                       bg-sky-50
                                                       px-3 py-2
                                                       text-xs
                                                       font-bold
                                                       text-sky-700
                                                       transition
                                                       hover:bg-sky-100">
                                                <span x-show="!copied">
                                                    Copy Link
                                                </span>

                                                <span x-show="copied" x-cloak>
                                                    Copied
                                                </span>
                                            </button>
                                        </div>
                                    @endif


                                    {{-- EDIT --}}
                                    @can('documents.update')
                                        <a href="{{ route('admin.documents.edit', [
                                            'document' => $document->id,
                                        ]) }}"
                                            wire:navigate
                                            class="rounded-lg
                                                   border
                                                   border-zinc-300
                                                   bg-white
                                                   px-3 py-2
                                                   text-xs
                                                   font-bold
                                                   text-zinc-700
                                                   transition
                                                   hover:bg-zinc-50">
                                            Edit
                                        </a>
                                    @endcan


                                    {{-- DELETE --}}
                                    @can('documents.delete')
                                        @if ($status !== \App\Enums\DocumentStatus::Published)
                                            <button type="button" wire:click="delete({{ $document->id }})"
                                                wire:confirm="Delete this document? The document record will be moved to the deleted state."
                                                wire:loading.attr="disabled" wire:target="delete({{ $document->id }})"
                                                class="rounded-lg
                                                       border
                                                       border-red-200
                                                       bg-red-50
                                                       px-3 py-2
                                                       text-xs
                                                       font-bold
                                                       text-red-700
                                                       transition
                                                       hover:bg-red-100
                                                       disabled:cursor-not-allowed
                                                       disabled:opacity-50">
                                                Delete
                                            </button>
                                        @endif
                                    @endcan

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8"
                                class="px-6
                                       py-16
                                       text-center">
                                <p
                                    class="text-base
                                           font-bold
                                           text-zinc-800">
                                    No documents found
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           text-zinc-500">
                                    Change the filters or create
                                    the first document.
                                </p>
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>

        </div>


        @if ($documents->hasPages())
            <div class="border-t
                       border-zinc-200
                       px-5 py-4">
                {{ $documents->links() }}
            </div>
        @endif

    </section>

</div>

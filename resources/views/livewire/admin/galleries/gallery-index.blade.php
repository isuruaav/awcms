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
                Galleries
            </h1>

            <p
                class="mt-1
                       text-sm
                       leading-6
                       text-zinc-500">
                Create and manage public image albums,
                event photographs and gallery content.
            </p>
        </div>

        @can('galleries.create')
            <a href="{{ route('admin.galleries.create') }}" wire:navigate
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
                Create Gallery
            </a>
        @endcan
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
                Total Galleries
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
                <label for="gallery-search"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Search
                </label>

                <input id="gallery-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Title, slug or description..."
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
                <label for="gallery-status"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Status
                </label>

                <select id="gallery-status" wire:model.live="statusFilter"
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
                <label for="gallery-per-page"
                    class="mb-1.5
                           block
                           text-sm
                           font-semibold
                           text-zinc-700">
                    Rows
                </label>

                <select id="gallery-per-page" wire:model.live="perPage"
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-3 py-2.5
                           text-sm">
                    <option value="10">
                        10
                    </option>

                    <option value="15">
                        15
                    </option>

                    <option value="25">
                        25
                    </option>

                    <option value="50">
                        50
                    </option>
                </select>
            </div>

            <div class="flex
                       items-end">
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
                        <th class="px-5 py-3
                                   text-left">
                            <button type="button" wire:click="sort('title')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600
                                       hover:text-zinc-950">
                                Gallery

                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3
                                   text-left">
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

                        <th class="px-5 py-3
                                   text-left">
                            <button type="button" wire:click="sort('event_date')"
                                class="text-xs
                                       font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-600">
                                Event Date

                                @if ($sortField === 'event_date')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th
                            class="px-5 py-3
                                   text-left
                                   text-xs
                                   font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-600">
                            Images
                        </th>

                        <th class="px-5 py-3
                                   text-left">
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

                        <th class="px-5 py-3
                                   text-left">
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
                    @forelse ($galleries as $gallery)

                        @php
                            $status = $gallery->status;

                            $statusClasses = match ($status) {
                                \App\Enums\GalleryStatus::Draft => 'bg-zinc-100 text-zinc-700',

                                \App\Enums\GalleryStatus::Published => 'bg-emerald-100 text-emerald-700',

                                \App\Enums\GalleryStatus::Archived => 'bg-amber-100 text-amber-700',

                                default => 'bg-zinc-100 text-zinc-600',
                            };
                        @endphp

                        <tr wire:key="gallery-{{ $gallery->id }}" class="align-top">
                            <td class="px-5 py-4">
                                <div class="min-w-64">
                                    <p class="font-bold
                                               text-zinc-900">
                                        {{ $gallery->title }}
                                    </p>

                                    <p
                                        class="mt-1
                                               text-xs
                                               text-zinc-500">
                                        /{{ $gallery->slug }}
                                    </p>

                                    @if ($gallery->description)
                                        <p
                                            class="mt-2
                                                   max-w-md
                                                   text-sm
                                                   leading-5
                                                   text-zinc-500">
                                            {{ \Illuminate\Support\Str::limit($gallery->description, 110) }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($status instanceof \App\Enums\GalleryStatus)
                                    <span
                                        class="inline-flex
                                               rounded-full
                                               px-2.5 py-1
                                               text-xs
                                               font-bold
                                               {{ $statusClasses }}">
                                        {{ $status->label() }}
                                    </span>
                                @else
                                    <span class="text-sm
                                               text-red-600">
                                        Invalid
                                    </span>
                                @endif
                            </td>

                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $gallery->event_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex
                                           min-w-10
                                           justify-center
                                           rounded-lg
                                           bg-zinc-100
                                           px-2.5 py-1
                                           text-sm
                                           font-bold
                                           text-zinc-700">
                                    {{ $gallery->images_count }}
                                </span>
                            </td>

                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $gallery->published_at?->format('d M Y H:i') ?? '—' }}
                            </td>

                            <td
                                class="whitespace-nowrap
                                       px-5 py-4
                                       text-sm
                                       text-zinc-600">
                                {{ $gallery->updated_at?->format('d M Y H:i') ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="flex
                                           justify-end
                                           gap-2">
                                    @can('galleries.update')
                                        <a href="{{ route('admin.galleries.edit', [
                                            'gallery' => $gallery->id,
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

                                    @can('galleries.delete')
                                        @if ($status !== \App\Enums\GalleryStatus::Published)
                                            <button type="button" wire:click="delete({{ $gallery->id }})"
                                                wire:confirm="Delete this gallery? This action will move it to the deleted state."
                                                class="rounded-lg
                                                       border
                                                       border-red-200
                                                       bg-red-50
                                                       px-3 py-2
                                                       text-xs
                                                       font-bold
                                                       text-red-700
                                                       transition
                                                       hover:bg-red-100">
                                                Delete
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-6
                                       py-16
                                       text-center">
                                <p
                                    class="text-base
                                           font-bold
                                           text-zinc-800">
                                    No galleries found
                                </p>

                                <p
                                    class="mt-1
                                           text-sm
                                           text-zinc-500">
                                    Change the filters or create
                                    the first gallery.
                                </p>
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($galleries->hasPages())
            <div class="border-t
                       border-zinc-200
                       px-5 py-4">
                {{ $galleries->links() }}
            </div>
        @endif
    </section>
</div>

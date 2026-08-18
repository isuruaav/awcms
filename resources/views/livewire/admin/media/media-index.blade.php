<div class="mx-auto max-w-7xl space-y-6">
    {{-- Header --}}
    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-start
               lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-bold
                       text-zinc-950"
            >
                Media Library
            </h1>

            <p
                class="mt-1 text-sm
                       leading-6
                       text-zinc-600"
            >
                Search, review and manage website
                images and documents.
            </p>
        </div>

        @can('media.upload')
            <a
                href="{{ route('admin.media.upload') }}"
                class="inline-flex
                       items-center
                       justify-center
                       rounded-xl
                       bg-emerald-700
                       px-5 py-3
                       text-sm font-bold
                       text-white
                       hover:bg-emerald-800"
            >
                + Upload Media
            </a>
        @endcan
    </div>

    {{-- Active / Trash --}}
    <div
        class="grid gap-4
               sm:grid-cols-2"
    >
        <button
            type="button"
            wire:click="$set('view', 'active')"
            @class([
                'rounded-2xl border p-5 text-left transition',
                'border-emerald-300 bg-emerald-50'
                    => $view === 'active',
                'border-zinc-200 bg-white hover:bg-zinc-50'
                    => $view !== 'active',
            ])
        >
            <p
                class="text-xs font-semibold
                       uppercase tracking-wide
                       text-zinc-500"
            >
                Active Media
            </p>

            <p
                class="mt-2 text-2xl
                       font-black
                       text-zinc-950"
            >
                {{ $activeCount }}
            </p>
        </button>

        <button
            type="button"
            wire:click="$set('view', 'trash')"
            @class([
                'rounded-2xl border p-5 text-left transition',
                'border-red-300 bg-red-50'
                    => $view === 'trash',
                'border-zinc-200 bg-white hover:bg-zinc-50'
                    => $view !== 'trash',
            ])
        >
            <p
                class="text-xs font-semibold
                       uppercase tracking-wide
                       text-zinc-500"
            >
                Trash
            </p>

            <p
                class="mt-2 text-2xl
                       font-black
                       text-zinc-950"
            >
                {{ $trashCount }}
            </p>
        </button>
    </div>

    {{-- Search / Filters --}}
    <section
        class="rounded-2xl
               border border-zinc-200
               bg-white p-5
               shadow-sm"
    >
        <div
            class="grid gap-4
                   lg:grid-cols-[minmax(0,1fr)_200px_220px_auto]"
        >
            <div>
                <label
                    for="media-search"
                    class="mb-2 block
                           text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Search
                </label>

                <input
                    id="media-search"
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Title, filename, alt text..."
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm"
                >
            </div>

            <div>
                <label
                    for="media-type-filter"
                    class="mb-2 block
                           text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Type
                </label>

                <select
                    id="media-type-filter"
                    wire:model.live="typeFilter"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm"
                >
                    <option value="">
                        All Types
                    </option>

                    @foreach ($mediaTypes as $mediaType)
                        <option
                            value="{{ $mediaType->value }}"
                        >
                            {{ $mediaType->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="media-visibility-filter"
                    class="mb-2 block
                           text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Visibility
                </label>

                <select
                    id="media-visibility-filter"
                    wire:model.live="visibilityFilter"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm"
                >
                    <option value="">
                        All Visibility
                    </option>

                    @foreach ($visibilities as $visibility)
                        <option
                            value="{{ $visibility->value }}"
                        >
                            {{ $visibility->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-3
                           text-sm font-semibold
                           text-zinc-700
                           hover:bg-zinc-50"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </section>

    {{-- Display Mode --}}
    <div
        class="flex flex-col gap-3
               sm:flex-row
               sm:items-center
               sm:justify-between"
    >
        <p
            class="text-sm
                   text-zinc-600"
        >
            Showing
            <strong>
                {{ $media->firstItem() ?? 0 }}
            </strong>
            –
            <strong>
                {{ $media->lastItem() ?? 0 }}
            </strong>
            of
            <strong>
                {{ $media->total() }}
            </strong>
            items
        </p>

        <div
            class="inline-flex
                   rounded-xl
                   border border-zinc-200
                   bg-white p-1"
        >
            <button
                type="button"
                wire:click="setDisplay('grid')"
                @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold',
                    'bg-zinc-900 text-white'
                        => $display === 'grid',
                    'text-zinc-600 hover:bg-zinc-100'
                        => $display !== 'grid',
                ])
            >
                Grid
            </button>

            <button
                type="button"
                wire:click="setDisplay('list')"
                @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold',
                    'bg-zinc-900 text-white'
                        => $display === 'list',
                    'text-zinc-600 hover:bg-zinc-100'
                        => $display !== 'list',
                ])
            >
                List
            </button>
        </div>
    </div>

    @if ($media->isEmpty())
        <div
            class="rounded-2xl
                   border-2 border-dashed
                   border-zinc-200
                   bg-zinc-50
                   px-6 py-16
                   text-center"
        >
            <p
                class="text-lg font-bold
                       text-zinc-800"
            >
                No media found
            </p>

            <p
                class="mt-2 text-sm
                       text-zinc-500"
            >
                @if ($view === 'trash')
                    The media trash is empty.
                @else
                    Upload a file or change the
                    current search filters.
                @endif
            </p>

            @if (
                $view === 'active'
                && auth()->user()?->can('media.upload')
            )
                <a
                    href="{{ route('admin.media.upload') }}"
                    class="mt-5 inline-flex
                           rounded-xl
                           bg-emerald-700
                           px-5 py-3
                           text-sm font-bold
                           text-white"
                >
                    Upload Media
                </a>
            @endif
        </div>
    @elseif ($display === 'grid')
        {{-- Grid View --}}
        <div
            class="grid gap-5
                   sm:grid-cols-2
                   lg:grid-cols-3
                   xl:grid-cols-4"
        >
            @foreach ($media as $asset)
                @php
                    $type = $asset->getAttribute('type');
                    $visibility = $asset->getAttribute('visibility');
                    $url = $publicUrls[(int) $asset->id] ?? null;
                @endphp

                <article
                    wire:key="media-grid-{{ $asset->id }}"
                    class="overflow-hidden
                           rounded-2xl
                           border border-zinc-200
                           bg-white shadow-sm"
                >
                    <div
                        class="flex aspect-[4/3]
                               items-center
                               justify-center
                               overflow-hidden
                               bg-zinc-100"
                    >
                        @if (
                            $type === \App\Enums\MediaType::Image
                            && $url
                        )
                            <img
                                src="{{ $url }}"
                                alt="{{ $asset->alt_text ?? '' }}"
                                loading="lazy"
                                class="h-full w-full
                                       object-cover"
                            >
                        @elseif (
                            $type === \App\Enums\MediaType::Document
                        )
                            <div class="text-center">
                                <div
                                    class="mx-auto
                                           inline-flex
                                           rounded-xl
                                           bg-white
                                           px-4 py-3
                                           text-xl
                                           font-black
                                           text-red-700
                                           shadow-sm"
                                >
                                    PDF
                                </div>

                                <p
                                    class="mt-3 text-xs
                                           font-semibold
                                           text-zinc-500"
                                >
                                    Document
                                </p>
                            </div>
                        @elseif (
                            $visibility !==
                            \App\Enums\MediaVisibility::Public
                        )
                            <div class="text-center">
                                <div
                                    class="text-2xl"
                                    aria-hidden="true"
                                >
                                    🔒
                                </div>

                                <p
                                    class="mt-2 text-xs
                                           font-semibold
                                           text-zinc-500"
                                >
                                    Private Media
                                </p>
                            </div>
                        @else
                            <div
                                class="text-center
                                       text-sm
                                       text-zinc-400"
                            >
                                No Preview
                            </div>
                        @endif
                    </div>

                    <div class="p-4">
                        <div
                            class="flex items-start
                                   justify-between
                                   gap-3"
                        >
                            <div class="min-w-0">
                                <h2
                                    class="truncate
                                           text-sm
                                           font-bold
                                           text-zinc-900"
                                >
                                    {{ $asset->title }}
                                </h2>

                                <p
                                    class="mt-1 truncate
                                           text-xs
                                           text-zinc-500"
                                >
                                    {{ $asset->original_name }}
                                </p>
                            </div>

                            @if (
                                $type instanceof
                                \App\Enums\MediaType
                            )
                                <span
                                    class="shrink-0
                                           rounded-full
                                           bg-zinc-100
                                           px-2.5 py-1
                                           text-[11px]
                                           font-bold
                                           text-zinc-600"
                                >
                                    {{ $type->label() }}
                                </span>
                            @endif
                        </div>

                        <div
                            class="mt-4 flex
                                   items-center
                                   justify-between
                                   gap-3"
                        >
                            @if (
                                $visibility instanceof
                                \App\Enums\MediaVisibility
                            )
                                <span
                                    @class([
                                        'rounded-full px-2.5 py-1 text-[11px] font-bold',
                                        'bg-emerald-100 text-emerald-700'
                                            => $visibility ===
                                                \App\Enums\MediaVisibility::Public,
                                        'bg-amber-100 text-amber-700'
                                            => $visibility ===
                                                \App\Enums\MediaVisibility::Internal,
                                        'bg-red-100 text-red-700'
                                            => $visibility ===
                                                \App\Enums\MediaVisibility::Restricted,
                                    ])
                                >
                                    {{ $visibility->label() }}
                                </span>
                            @endif

                            <span
                                class="text-[11px]
                                       text-zinc-400"
                            >
                                #{{ $asset->id }}
                            </span>
                        </div>

                        @if (
                            $view === 'trash'
                            && $asset->deleted_at
                        )
                            <p
                                class="mt-3
                                       border-t
                                       border-zinc-100
                                       pt-3
                                       text-xs
                                       text-red-600"
                            >
                                Deleted
                                {{ $asset->deleted_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        {{-- List View --}}
        <div
            class="overflow-hidden
                   rounded-2xl
                   border border-zinc-200
                   bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table
                    class="min-w-full
                           divide-y
                           divide-zinc-200"
                >
                    <thead class="bg-zinc-50">
                        <tr>
                            <th
                                class="px-5 py-3
                                       text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500"
                            >
                                Media
                            </th>

                            <th
                                class="px-5 py-3
                                       text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500"
                            >
                                Type
                            </th>

                            <th
                                class="px-5 py-3
                                       text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500"
                            >
                                Visibility
                            </th>

                            <th
                                class="px-5 py-3
                                       text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500"
                            >
                                Size
                            </th>

                            <th
                                class="px-5 py-3
                                       text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500"
                            >
                                Uploaded By
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y
                               divide-zinc-100"
                    >
                        @foreach ($media as $asset)
                            @php
                                $type = $asset->getAttribute('type');
                                $visibility = $asset->getAttribute('visibility');
                            @endphp

                            <tr
                                wire:key="media-list-{{ $asset->id }}"
                                class="hover:bg-zinc-50"
                            >
                                <td class="px-5 py-4">
                                    <p
                                        class="max-w-xs
                                               truncate
                                               text-sm
                                               font-bold
                                               text-zinc-900"
                                    >
                                        {{ $asset->title }}
                                    </p>

                                    <p
                                        class="mt-1 max-w-xs
                                               truncate
                                               text-xs
                                               text-zinc-500"
                                    >
                                        {{ $asset->original_name }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4
                                           text-sm
                                           text-zinc-600"
                                >
                                    @if (
                                        $type instanceof
                                        \App\Enums\MediaType
                                    )
                                        {{ $type->label() }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if (
                                        $visibility instanceof
                                        \App\Enums\MediaVisibility
                                    )
                                        <span
                                            class="text-sm
                                                   font-semibold
                                                   text-zinc-700"
                                        >
                                            {{ $visibility->label() }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td
                                    class="px-5 py-4
                                           text-sm
                                           text-zinc-600"
                                >
                                    @if ($asset->size_bytes)
                                        @if (
                                            $asset->size_bytes
                                            >= 1048576
                                        )
                                            {{
                                                number_format(
                                                    $asset->size_bytes
                                                    / 1048576,
                                                    2,
                                                )
                                            }}
                                            MB
                                        @else
                                            {{
                                                number_format(
                                                    $asset->size_bytes
                                                    / 1024,
                                                    1,
                                                )
                                            }}
                                            KB
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>

                                <td
                                    class="px-5 py-4
                                           text-sm
                                           text-zinc-600"
                                >
                                    {{
                                        $asset->uploader?->name
                                        ?? 'Unknown'
                                    }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($media->hasPages())
        <div>
            {{ $media->links() }}
        </div>
    @endif
</div>
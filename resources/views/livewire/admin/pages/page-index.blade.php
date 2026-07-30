<div class="space-y-6">
  <div
    class="flex flex-col gap-4 sm:flex-row
           sm:items-start sm:justify-between"
>
    <div>
        <h1 class="text-2xl font-bold text-zinc-950">
            Pages
        </h1>

        <p class="mt-1 text-sm text-zinc-600">
            Create, review and publish website pages.
        </p>
    </div>

    @can('pages.create')
        <a
            href="{{ route('admin.pages.create') }}"
            wire:navigate
            class="inline-flex items-center justify-center
                   rounded-xl bg-emerald-700 px-4 py-2.5
                   text-sm font-semibold text-white
                   shadow-sm hover:bg-emerald-800"
        >
            Create Page
        </a>
    @endcan
</div>

    {{-- Filters --}}
    <section
        class="rounded-2xl border border-zinc-200
               bg-white p-5 shadow-sm"
    >
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label
                    for="page-search"
                    class="mb-2 block text-sm font-medium text-zinc-700"
                >
                    Search pages
                </label>

                <input
                    id="page-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by title, slug or excerpt..."
                    class="w-full rounded-xl border border-zinc-300
                           bg-white px-4 py-2.5 text-sm outline-none
                           placeholder:text-zinc-400
                           focus:border-emerald-500
                           focus:ring-4 focus:ring-emerald-500/10"
                >
            </div>

            <div>
                <label
                    for="page-status"
                    class="mb-2 block text-sm font-medium text-zinc-700"
                >
                    Workflow status
                </label>

                <select
                    id="page-status"
                    wire:model.live="status"
                    class="w-full rounded-xl border border-zinc-300
                           bg-white px-4 py-2.5 text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4 focus:ring-emerald-500/10"
                >
                    <option value="all">
                        All statuses
                    </option>

                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div
            class="mt-4 flex flex-col gap-3 border-t
                   border-zinc-100 pt-4 sm:flex-row
                   sm:items-center sm:justify-between"
        >
            <button
                type="button"
                wire:click="resetFilters"
                class="text-left text-sm font-semibold
                       text-emerald-700 hover:text-emerald-800"
            >
                Reset filters
            </button>

            <div class="flex items-center gap-2">
                <label
                    for="pages-per-page"
                    class="text-sm text-zinc-500"
                >
                    Rows
                </label>

                <select
                    id="pages-per-page"
                    wire:model.live="perPage"
                    class="rounded-lg border border-zinc-300
                           bg-white px-3 py-2 text-sm"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </section>

    {{-- Pages table --}}
    <section
        class="overflow-hidden rounded-2xl
               border border-zinc-200 bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <button
                                type="button"
                                wire:click="sort('title')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900"
                            >
                                Title

                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button
                                type="button"
                                wire:click="sort('status')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900"
                            >
                                Status

                                @if ($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Author
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button
                                type="button"
                                wire:click="sort('updated_at')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900"
                            >
                                Updated

                                @if ($sortField === 'updated_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button
                                type="button"
                                wire:click="sort('published_at')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900"
                            >
                                Published

                                @if ($sortField === 'published_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100">
                    @forelse ($pages as $page)
                        @php
                            $statusClasses = match ($page->status) {
                                \App\Enums\PageStatus::Draft =>
                                    'bg-zinc-100 text-zinc-700',

                                \App\Enums\PageStatus::Submitted =>
                                    'bg-blue-50 text-blue-700',

                                \App\Enums\PageStatus::Approved =>
                                    'bg-violet-50 text-violet-700',

                                \App\Enums\PageStatus::Published =>
                                    'bg-emerald-50 text-emerald-700',

                                \App\Enums\PageStatus::Archived =>
                                    'bg-amber-50 text-amber-700',
                            };
                        @endphp

                        <tr
                            wire:key="page-{{ $page->id }}"
                            class="hover:bg-zinc-50/70"
                        >
                            <td class="px-5 py-4">
                                <p class="font-semibold text-zinc-900">
                                    {{ $page->title }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    /{{ $page->slug }}
                                </p>

                                @if ($page->excerpt)
                                    <p class="mt-2 max-w-md text-sm text-zinc-600">
                                        {{ \Illuminate\Support\Str::limit(
                                            $page->excerpt,
                                            100,
                                        ) }}
                                    </p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex rounded-full
                                           px-2.5 py-1 text-xs font-semibold
                                           {{ $statusClasses }}"
                                >
                                    {{ $page->status->label() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm font-medium text-zinc-800">
                                    {{ $page->creator?->name ?? 'Unknown' }}
                                </p>

                                @if (
                                    $page->updater
                                    && ! $page->updater->is($page->creator)
                                )
                                    <p class="mt-1 text-xs text-zinc-500">
                                        Edited by {{ $page->updater->name }}
                                    </p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm text-zinc-700">
                                    {{ $page->updated_at->format('Y-m-d') }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ $page->updated_at->format('H:i') }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($page->published_at)
                                    <p class="text-sm text-zinc-700">
                                        {{ $page->published_at->format('Y-m-d') }}
                                    </p>

                                    <p class="text-xs text-zinc-500">
                                        {{ $page->published_at->format('H:i') }}
                                    </p>
                                @else
                                    <span class="text-sm text-zinc-400">
                                        Not published
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-5 py-14 text-center"
                            >
                                <p class="font-semibold text-zinc-700">
                                    No pages found
                                </p>

                                <p class="mt-1 text-sm text-zinc-500">
                                    Create a page or change the current filters.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4">
                {{ $pages->links() }}
            </div>
        @endif
    </section>
</div>
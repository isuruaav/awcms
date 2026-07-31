<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-950">
                Pages
            </h1>

            <p class="mt-1 text-sm text-zinc-600">
                Create, review and publish website pages.
            </p>
        </div>

        @can('pages.create')
            <a href="{{ route('admin.pages.create') }}" wire:navigate
                class="inline-flex items-center justify-center
                       rounded-xl bg-emerald-700 px-4 py-2.5
                       text-sm font-semibold text-white
                       shadow-sm hover:bg-emerald-800">
                Create Page
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 px-4 py-3 text-sm
                   font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif



    @error('workflow')
        <div
            class="rounded-xl border border-red-200
               bg-red-50 px-4 py-3 text-sm
               font-medium text-red-800">
            {{ $message }}
        </div>
    @enderror
    {{-- Filters --}}
    <section class="rounded-2xl border border-zinc-200
               bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="page-search" class="mb-2 block text-sm font-medium text-zinc-700">
                    Search pages
                </label>

                <input id="page-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search by title, slug or excerpt..."
                    class="w-full rounded-xl border border-zinc-300
                           bg-white px-4 py-2.5 text-sm outline-none
                           placeholder:text-zinc-400
                           focus:border-emerald-500
                           focus:ring-4 focus:ring-emerald-500/10">
            </div>

            <div>
                <label for="page-status" class="mb-2 block text-sm font-medium text-zinc-700">
                    Workflow status
                </label>

                <select id="page-status" wire:model.live="status"
                    class="w-full rounded-xl border border-zinc-300
                           bg-white px-4 py-2.5 text-sm outline-none
                           focus:border-emerald-500
                           focus:ring-4 focus:ring-emerald-500/10">
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

        <div>
            <label for="page-record-state" class="mb-2 block text-sm font-medium text-zinc-700">
                Records
            </label>

            <select id="page-record-state" wire:model.live="recordState"
                class="w-full rounded-xl border border-zinc-300
               bg-white px-4 py-2.5 text-sm outline-none
               focus:border-emerald-500
               focus:ring-4 focus:ring-emerald-500/10">
                <option value="active">
                    Active pages
                </option>

                <option value="trashed">
                    Trash
                </option>
            </select>
        </div>

        <div
            class="mt-4 flex flex-col gap-3 border-t
                   border-zinc-100 pt-4 sm:flex-row
                   sm:items-center sm:justify-between">
            <button type="button" wire:click="resetFilters"
                class="text-left text-sm font-semibold
                       text-emerald-700 hover:text-emerald-800">
                Reset filters
            </button>

            <div class="flex items-center gap-2">
                <label for="pages-per-page" class="text-sm text-zinc-500">
                    Rows
                </label>

                <select id="pages-per-page" wire:model.live="perPage"
                    class="rounded-lg border border-zinc-300
                           bg-white px-3 py-2 text-sm">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </section>

    {{-- Pages table --}}
    <section class="overflow-hidden rounded-2xl
               border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('title')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900">
                                Title

                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('status')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900">
                                Status

                                @if ($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th
                            class="px-5 py-3 text-left text-xs
                                   font-semibold uppercase tracking-wide
                                   text-zinc-600">
                            Author
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('updated_at')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900">
                                Updated

                                @if ($sortField === 'updated_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('published_at')"
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-zinc-600
                                       hover:text-zinc-900">
                                Published

                                @if ($sortField === 'published_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>

                        <th
                            class="px-5 py-3 text-right text-xs
                                   font-semibold uppercase tracking-wide
                                   text-zinc-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100">
                    @forelse ($pages as $page)
                        @php
                            $statusClasses = match ($page->status) {
                                \App\Enums\PageStatus::Draft => 'bg-zinc-100 text-zinc-700',

                                \App\Enums\PageStatus::Submitted => 'bg-blue-50 text-blue-700',

                                \App\Enums\PageStatus::Approved => 'bg-violet-50 text-violet-700',

                                \App\Enums\PageStatus::Published => 'bg-emerald-50 text-emerald-700',

                                \App\Enums\PageStatus::Archived => 'bg-amber-50 text-amber-700',
                            };
                        @endphp

                        <tr wire:key="page-{{ $page->id }}" class="hover:bg-zinc-50/70">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-zinc-900">
                                    {{ $page->title }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    /{{ $page->slug }}
                                </p>

                                @if ($page->excerpt)
                                    <p class="mt-2 max-w-md text-sm text-zinc-600">
                                        {{ \Illuminate\Support\Str::limit($page->excerpt, 100) }}
                                    </p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex rounded-full
                                           px-2.5 py-1 text-xs font-semibold
                                           {{ $statusClasses }}">
                                    {{ $page->status->label() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm font-medium text-zinc-800">
                                    {{ $page->creator?->name ?? 'Unknown' }}
                                </p>

                                @if ($page->updater && !$page->updater->is($page->creator))
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

                            <td class="px-5 py-4">
                                <div class="flex min-w-48 flex-wrap justify-end gap-2">
                                    @if ($recordState === 'trashed')
                                        @can('pages.delete')
                                            <button type="button" wire:click="restorePage({{ $page->id }})"
                                                wire:confirm="Restore this page from Trash?" wire:loading.attr="disabled"
                                                wire:target="restorePage({{ $page->id }})"
                                                class="inline-flex rounded-lg
                           bg-emerald-700 px-3 py-2
                           text-xs font-semibold text-white
                           hover:bg-emerald-800
                           disabled:cursor-not-allowed
                           disabled:opacity-60">
                                                Restore
                                            </button>
                                        @else
                                            <span class="text-xs text-zinc-400">
                                                View only
                                            </span>
                                        @endcan
                                    @else
                                        @can('pages.view')
                                            <a href="{{ route('admin.pages.preview', $page) }}"
                                                target="_blank" rel="noopener noreferrer"
                                                class="inline-flex rounded-lg border
               border-blue-200 bg-blue-50
               px-3 py-2 text-xs font-semibold
               text-blue-700 hover:bg-blue-100">
                                                Preview
                                            </a>
                                        @endcan
                                        {{-- Draft --}}
                                        @if ($page->status === \App\Enums\PageStatus::Draft)
                                            @can('pages.update')
                                                <a href="{{ route('admin.pages.edit', $page) }}" wire:navigate
                                                    class="inline-flex rounded-lg border
                               border-zinc-300 bg-white
                               px-3 py-2 text-xs font-semibold
                               text-zinc-700 hover:bg-zinc-100">
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('pages.submit')
                                                <button type="button" wire:click="submitPage({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="submitPage({{ $page->id }})"
                                                    class="inline-flex rounded-lg bg-blue-700
                               px-3 py-2 text-xs font-semibold
                               text-white hover:bg-blue-800
                               disabled:opacity-60">
                                                    Submit
                                                </button>
                                            @endcan

                                            @can('pages.archive')
                                                <button type="button" wire:click="archivePage({{ $page->id }})"
                                                    wire:confirm="Archive this page?" wire:loading.attr="disabled"
                                                    wire:target="archivePage({{ $page->id }})"
                                                    class="inline-flex rounded-lg border
                               border-amber-200 bg-amber-50
                               px-3 py-2 text-xs font-semibold
                               text-amber-700 hover:bg-amber-100
                               disabled:opacity-60">
                                                    Archive
                                                </button>
                                            @endcan

                                            @can('pages.delete')
                                                <button type="button" wire:click="deletePage({{ $page->id }})"
                                                    wire:confirm="Move this draft page to Trash?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="deletePage({{ $page->id }})"
                                                    class="inline-flex rounded-lg border
                               border-red-200 bg-red-50
                               px-3 py-2 text-xs font-semibold
                               text-red-700 hover:bg-red-100
                               disabled:opacity-60">
                                                    Delete
                                                </button>
                                            @endcan
                                        @endif

                                        {{-- Submitted --}}
                                        @if ($page->status === \App\Enums\PageStatus::Submitted)
                                            @can('pages.approve')
                                                <button type="button"
                                                    wire:click="returnPageToDraft({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="returnPageToDraft({{ $page->id }})"
                                                    class="inline-flex rounded-lg border
                               border-zinc-300 bg-white
                               px-3 py-2 text-xs font-semibold
                               text-zinc-700 hover:bg-zinc-100">
                                                    Return
                                                </button>

                                                <button type="button" wire:click="approvePage({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approvePage({{ $page->id }})"
                                                    class="inline-flex rounded-lg bg-violet-700
                               px-3 py-2 text-xs font-semibold
                               text-white hover:bg-violet-800">
                                                    Approve
                                                </button>
                                            @endcan

                                            @can('pages.archive')
                                                <button type="button" wire:click="archivePage({{ $page->id }})"
                                                    wire:confirm="Archive this page?"
                                                    class="inline-flex rounded-lg border
                               border-amber-200 bg-amber-50
                               px-3 py-2 text-xs font-semibold
                               text-amber-700 hover:bg-amber-100">
                                                    Archive
                                                </button>
                                            @endcan
                                        @endif

                                        {{-- Approved --}}
                                        @if ($page->status === \App\Enums\PageStatus::Approved)
                                            @can('pages.approve')
                                                <button type="button"
                                                    wire:click="returnPageToDraft({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex rounded-lg border
                               border-zinc-300 bg-white
                               px-3 py-2 text-xs font-semibold
                               text-zinc-700 hover:bg-zinc-100">
                                                    Return
                                                </button>
                                            @endcan

                                            @can('pages.publish')
                                                <button type="button" wire:click="publishPage({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex rounded-lg
                               bg-emerald-700 px-3 py-2
                               text-xs font-semibold text-white
                               hover:bg-emerald-800">
                                                    Publish
                                                </button>
                                            @endcan

                                            @can('pages.archive')
                                                <button type="button" wire:click="archivePage({{ $page->id }})"
                                                    wire:confirm="Archive this page?"
                                                    class="inline-flex rounded-lg border
                               border-amber-200 bg-amber-50
                               px-3 py-2 text-xs font-semibold
                               text-amber-700 hover:bg-amber-100">
                                                    Archive
                                                </button>
                                            @endcan
                                        @endif

                                        {{-- Published --}}
                                        @if ($page->status === \App\Enums\PageStatus::Published)
                                            @can('pages.publish')
                                                <button type="button"
                                                    wire:click="returnPageToDraft({{ $page->id }})"
                                                    wire:confirm="Unpublish and return this page to Draft?"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex rounded-lg border
                               border-amber-300 bg-amber-50
                               px-3 py-2 text-xs font-semibold
                               text-amber-800 hover:bg-amber-100">
                                                    Unpublish
                                                </button>
                                            @endcan

                                            @can('pages.archive')
                                                <button type="button" wire:click="archivePage({{ $page->id }})"
                                                    wire:confirm="Archive this published page?"
                                                    class="inline-flex rounded-lg border
                               border-red-200 bg-red-50
                               px-3 py-2 text-xs font-semibold
                               text-red-700 hover:bg-red-100">
                                                    Archive
                                                </button>
                                            @endcan
                                        @endif

                                        {{-- Archived --}}
                                        @if ($page->status === \App\Enums\PageStatus::Archived)
                                            @can('pages.archive')
                                                <button type="button"
                                                    wire:click="returnPageToDraft({{ $page->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex rounded-lg border
                               border-zinc-300 bg-white
                               px-3 py-2 text-xs font-semibold
                               text-zinc-700 hover:bg-zinc-100">
                                                    Restore Draft
                                                </button>
                                            @endcan

                                            @can('pages.delete')
                                                <button type="button" wire:click="deletePage({{ $page->id }})"
                                                    wire:confirm="Move this archived page to Trash?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="deletePage({{ $page->id }})"
                                                    class="inline-flex rounded-lg border
                               border-red-200 bg-red-50
                               px-3 py-2 text-xs font-semibold
                               text-red-700 hover:bg-red-100">
                                                    Delete
                                                </button>
                                            @endcan
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
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

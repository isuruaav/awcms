<div class="space-y-6">
    {{-- Header --}}
    <x-admin.page-header
        title="News"
        description="Create, manage and review website news articles."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                @if ($canManageCategories)
                    <a
                        href="{{ route('admin.news.categories.index') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center
                               rounded-xl border border-zinc-300
                               bg-white px-4 py-2.5
                               text-sm font-semibold text-zinc-700
                               shadow-sm transition
                               hover:bg-zinc-50"
                    >
                        Categories
                    </a>
                @endif

                @if ($canCreate)
                    <a
                        href="{{ route('admin.news.create') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center
                               rounded-xl bg-emerald-700
                               px-4 py-2.5
                               text-sm font-semibold text-white
                               shadow-sm transition
                               hover:bg-emerald-800"
                    >
                        Create News
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Flash message --}}
    @if (session('status'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 px-4 py-3
                   text-sm font-medium text-emerald-800"
        >
            {{ session('status') }}
        </div>
    @endif

    {{-- Filters --}}
    <section
        class="rounded-2xl border border-zinc-200
               bg-white p-5 shadow-sm"
    >
        <div class="grid gap-4 lg:grid-cols-4">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <label
                    for="news-search"
                    class="mb-2 block text-sm font-medium text-zinc-700"
                >
                    Search news
                </label>

                <input
                    id="news-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search title, slug or summary..."
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-2.5
                           text-sm text-zinc-900
                           outline-none transition
                           placeholder:text-zinc-400
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >
            </div>

            {{-- Status --}}
            <div>
                <label
                    for="news-status"
                    class="mb-2 block text-sm font-medium text-zinc-700"
                >
                    Workflow Status
                </label>

                <select
                    id="news-status"
                    wire:model.live="status"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-2.5
                           text-sm text-zinc-900
                           outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >
                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Category --}}
            <div>
                <label
                    for="news-category"
                    class="mb-2 block text-sm font-medium text-zinc-700"
                >
                    Category
                </label>

                <select
                    id="news-category"
                    wire:model.live="category"
                    class="w-full rounded-xl
                           border border-zinc-300
                           bg-white px-4 py-2.5
                           text-sm text-zinc-900
                           outline-none
                           focus:border-emerald-500
                           focus:ring-4
                           focus:ring-emerald-500/10"
                >
                    <option value="">
                        All categories
                    </option>

                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">
                            {{ $categoryOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div
            class="mt-4 flex flex-col gap-3
                   border-t border-zinc-100 pt-4
                   sm:flex-row sm:items-center
                   sm:justify-between"
        >
            <button
                type="button"
                wire:click="clearFilters"
                class="text-left text-sm font-semibold
                       text-emerald-700
                       hover:text-emerald-800"
            >
                Reset filters
            </button>

            <p class="text-sm text-zinc-500">
                {{ $news->total() }}
                article{{ $news->total() === 1 ? '' : 's' }}
            </p>
        </div>
    </section>

    {{-- News table --}}
    <section
        class="overflow-hidden rounded-2xl
               border border-zinc-200
               bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th
                            class="px-5 py-3 text-left
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Article
                        </th>

                        <th
                            class="px-5 py-3 text-left
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Category
                        </th>

                        <th
                            class="px-5 py-3 text-left
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Status
                        </th>

                        <th
                            class="px-5 py-3 text-left
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Author
                        </th>

                        <th
                            class="px-5 py-3 text-left
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Updated
                        </th>

                        <th
                            class="px-5 py-3 text-right
                                   text-xs font-semibold
                                   uppercase tracking-wide
                                   text-zinc-600"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100">
                    @forelse ($news as $article)
                        @php
                            $articleStatus = $article->status;

                            $statusClasses = match ($articleStatus) {
                                \App\Enums\NewsStatus::Draft =>
                                    'bg-zinc-100 text-zinc-700',

                                \App\Enums\NewsStatus::Submitted =>
                                    'bg-blue-50 text-blue-700',

                                \App\Enums\NewsStatus::Approved =>
                                    'bg-violet-50 text-violet-700',

                                \App\Enums\NewsStatus::Published =>
                                    'bg-emerald-50 text-emerald-700',

                                \App\Enums\NewsStatus::Archived =>
                                    'bg-amber-50 text-amber-700',

                                default =>
                                    'bg-zinc-100 text-zinc-700',
                            };
                        @endphp

                        <tr
                            wire:key="news-{{ $article->id }}"
                            class="transition hover:bg-zinc-50"
                        >
                            {{-- Article --}}
                            <td class="px-5 py-4">
                                <div class="max-w-lg">
                                    <div
                                        class="flex flex-wrap
                                               items-center gap-2"
                                    >
                                        <p
                                            class="font-semibold
                                                   text-zinc-900"
                                        >
                                            {{ $article->title }}
                                        </p>

                                        @if ($article->is_featured)
                                            <span
                                                class="rounded-full
                                                       bg-amber-100
                                                       px-2 py-0.5
                                                       text-[11px]
                                                       font-bold
                                                       uppercase
                                                       tracking-wide
                                                       text-amber-700"
                                            >
                                                Featured
                                            </span>
                                        @endif
                                    </div>

                                    <p
                                        class="mt-1
                                               text-xs
                                               text-zinc-500"
                                    >
                                        /{{ $article->slug }}
                                    </p>

                                    @if ($article->summary)
                                        <p
                                            class="mt-2
                                                   line-clamp-2
                                                   max-w-xl
                                                   text-sm
                                                   leading-5
                                                   text-zinc-500"
                                        >
                                            {{ $article->summary }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-5 py-4">
                                @if ($article->category)
                                    <span
                                        class="inline-flex rounded-full
                                               bg-sky-50
                                               px-2.5 py-1
                                               text-xs font-semibold
                                               text-sky-700"
                                    >
                                        {{ $article->category->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400">
                                        Uncategorized
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if ($articleStatus instanceof \App\Enums\NewsStatus)
                                    <span
                                        class="inline-flex rounded-full
                                               px-2.5 py-1
                                               text-xs font-semibold
                                               {{ $statusClasses }}"
                                    >
                                        {{ $articleStatus->label() }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full
                                               bg-red-50
                                               px-2.5 py-1
                                               text-xs font-semibold
                                               text-red-700"
                                    >
                                        Invalid
                                    </span>
                                @endif
                            </td>

                            {{-- Author --}}
                            <td class="px-5 py-4">
                                @if ($article->creator)
                                    <p
                                        class="text-sm font-medium
                                               text-zinc-700"
                                    >
                                        {{ $article->creator->name }}
                                    </p>
                                @else
                                    <span class="text-sm text-zinc-400">
                                        —
                                    </span>
                                @endif
                            </td>

                            {{-- Updated --}}
                            <td class="px-5 py-4">
                                <p class="text-sm text-zinc-700">
                                    {{ $article->updated_at?->diffForHumans() }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-400">
                                    {{ $article->updated_at?->format('d M Y H:i') }}
                                </p>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                @if (
                                    $canUpdate
                                    && $articleStatus instanceof \App\Enums\NewsStatus
                                    && $articleStatus->isEditable()
                                )
                                    <a
                                        href="{{ route(
                                            'admin.news.edit',
                                            ['news' => $article->id]
                                        ) }}"
                                        wire:navigate
                                        class="inline-flex items-center
                                               justify-center rounded-lg
                                               border border-zinc-300
                                               bg-white px-3 py-2
                                               text-xs font-semibold
                                               text-zinc-700
                                               transition
                                               hover:bg-zinc-100"
                                    >
                                        Edit
                                    </a>
                                @else
                                    <span
                                        class="text-xs font-medium
                                               text-zinc-400"
                                    >
                                        Read only
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="px-6 py-16 text-center"
                            >
                                <p
                                    class="text-sm font-semibold
                                           text-zinc-600"
                                >
                                    No news articles found.
                                </p>

                                <p
                                    class="mt-1 text-xs
                                           text-zinc-400"
                                >
                                    Create an article or change
                                    the current filters.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($news->hasPages())
            <div
                class="border-t border-zinc-200
                       bg-zinc-50 px-5 py-4"
            >
                {{ $news->links() }}
            </div>
        @endif
    </section>
</div>
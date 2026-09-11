<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-950">News</h1>
            <p class="mt-1 text-sm text-zinc-600">
                Manage manual English, Sinhala and Tamil news articles.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($canManageCategories)
                <a href="{{ route('admin.news.categories.index') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Categories
                </a>
            @endif

            @can('news.create')
                <a href="{{ route('admin.news.create') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                    Create News
                </a>
            @endcan
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('workflow')
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ $message }}
        </div>
    @enderror

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-6">
            <div class="lg:col-span-2 2xl:col-span-2">
                <label for="news-search" class="mb-2 block text-sm font-medium text-zinc-700">Search news</label>
                <input id="news-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search title, slug or summary..."
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
            </div>

            <div>
                <label for="news-locale" class="mb-2 block text-sm font-medium text-zinc-700">Language</label>
                <select id="news-locale" wire:model.live="locale"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm">
                    <option value="all">All languages</option>
                    @foreach ($locales as $localeOption)
                        <option value="{{ $localeOption->value }}">{{ $localeOption->label() }} —
                            {{ $localeOption->nativeLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="news-category" class="mb-2 block text-sm font-medium text-zinc-700">Category</label>
                <select id="news-category" wire:model.live="category"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm">
                    <option value="all">All categories</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="news-status" class="mb-2 block text-sm font-medium text-zinc-700">Status</label>
                <select id="news-status" wire:model.live="status"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm">
                    <option value="all">All statuses</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="news-records" class="mb-2 block text-sm font-medium text-zinc-700">Records</label>
                <select id="news-records" wire:model.live="recordState"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm">
                    <option value="active">Active</option>
                    <option value="trashed">Trash</option>
                </select>
            </div>
        </div>

        <div
            class="mt-4 flex flex-col gap-3 border-t border-zinc-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <button type="button" wire:click="resetFilters"
                class="w-fit text-sm font-semibold text-emerald-700 hover:text-emerald-800">Reset filters</button>

            <div class="flex items-center gap-2">
                <label for="news-per-page" class="text-sm text-zinc-500">Rows</label>
                <select id="news-per-page" wire:model.live="perPage"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="space-y-3 p-4 2xl:hidden">
            @forelse ($news as $article)
                @php
                    $statusClasses = match ($article->status) {
                        \App\Enums\NewsStatus::Draft => 'bg-zinc-100 text-zinc-700',
                        \App\Enums\NewsStatus::Submitted => 'bg-blue-50 text-blue-700',
                        \App\Enums\NewsStatus::ChangesRequested => 'bg-red-50 text-red-700',
                        \App\Enums\NewsStatus::Approved => 'bg-violet-50 text-violet-700',
                        \App\Enums\NewsStatus::Published => 'bg-emerald-50 text-emerald-700',
                        \App\Enums\NewsStatus::Archived => 'bg-amber-50 text-amber-700',
                    };
                @endphp

                <article wire:key="mobile-news-{{ $article->id }}"
                    class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-sm font-bold text-zinc-900">{{ $article->title }}</h2>
                                @if ($article->is_featured)
                                    <span
                                        class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700">Featured</span>
                                @endif
                            </div>
                            <p class="mt-1 truncate text-xs text-zinc-500">
                                /{{ $article->locale->value }}/news/{{ $article->slug }}</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClasses }}">{{ $article->status->label() }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-y border-zinc-100 py-3 text-xs">
                        <div>
                            <p class="text-zinc-400">Language</p>
                            <p class="mt-1 font-semibold text-zinc-700">{{ $article->locale->nativeLabel() }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-400">Category</p>
                            <p class="mt-1 truncate font-semibold text-zinc-700">
                                {{ $article->category?->name ?? 'Uncategorised' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-400">Author</p>
                            <p class="mt-1 truncate font-semibold text-zinc-700">
                                {{ $article->creator?->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-400">Updated</p>
                            <p class="mt-1 font-semibold text-zinc-700">{{ $article->updated_at?->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-3">
                        @if ($recordState === 'trashed')
                            @can('news.delete')
                                <button type="button" wire:click="restoreArticle({{ $article->id }})"
                                    wire:confirm="Restore this news article from Trash?"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">Restore</button>
                            @endcan
                        @else
                            @can('news.view')
                                <a href="{{ route('admin.news.preview', $article) }}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex flex-1 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">Preview</a>
                            @endcan
                            @if ($article->status === \App\Enums\NewsStatus::Published)
                                @can('news.publish')
                                    <button type="button" wire:click="unpublishArticle({{ $article->id }})"
                                        wire:confirm="Unpublish this news article and return it to Draft?"
                                        class="inline-flex flex-1 items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Unpublish</button>
                                @endcan
                            @else
                                @can('news.publish')
                                    <button type="button" wire:click="publishArticle({{ $article->id }})"
                                        wire:confirm="Publish this news article?"
                                        class="inline-flex flex-1 items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">Publish</button>
                                @endcan
                            @endif
                            @if ($article->status === \App\Enums\NewsStatus::Draft)
                                @can('news.update')
                                    <a href="{{ route('admin.news.edit', $article) }}" wire:navigate
                                        class="inline-flex flex-1 items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700">Edit</a>
                                @endcan
                                @can('news.delete')
                                    <button type="button" wire:click="deleteArticle({{ $article->id }})"
                                        wire:confirm="Move this draft news article to Trash?"
                                        class="inline-flex flex-1 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Delete</button>
                                @endcan
                            @endif
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-5 py-14 text-center">
                    <p class="font-semibold text-zinc-700">No news articles found</p>
                    <p class="mt-1 text-sm text-zinc-500">Create an article or change the current filters.</p>
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto 2xl:block">
            <table class="min-w-280 divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('title')"
                                class="text-xs font-semibold uppercase tracking-wide text-zinc-600 hover:text-zinc-900">
                                Article
                                @if ($sortField === 'title')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('locale')"
                                class="text-xs font-semibold uppercase tracking-wide text-zinc-600 hover:text-zinc-900">
                                Language
                                @if ($sortField === 'locale')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Category</th>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('status')"
                                class="text-xs font-semibold uppercase tracking-wide text-zinc-600 hover:text-zinc-900">
                                Status
                                @if ($sortField === 'status')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Author</th>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('updated_at')"
                                class="text-xs font-semibold uppercase tracking-wide text-zinc-600 hover:text-zinc-900">
                                Updated
                                @if ($sortField === 'updated_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <button type="button" wire:click="sort('published_at')"
                                class="text-xs font-semibold uppercase tracking-wide text-zinc-600 hover:text-zinc-900">
                                Published
                                @if ($sortField === 'published_at')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @endif
                            </button>
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600">
                            Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100">
                    @forelse ($news as $article)
                        @php
                            $statusClasses = match ($article->status) {
                                \App\Enums\NewsStatus::Draft => 'bg-zinc-100 text-zinc-700',
                                \App\Enums\NewsStatus::Submitted => 'bg-blue-50 text-blue-700',
                                \App\Enums\NewsStatus::ChangesRequested => 'bg-red-50 text-red-700',
                                \App\Enums\NewsStatus::Approved => 'bg-violet-50 text-violet-700',
                                \App\Enums\NewsStatus::Published => 'bg-emerald-50 text-emerald-700',
                                \App\Enums\NewsStatus::Archived => 'bg-amber-50 text-amber-700',
                            };
                        @endphp

                        <tr wire:key="news-{{ $article->id }}" class="hover:bg-zinc-50/70">
                            <td class="px-5 py-4">
                                <div class="max-w-lg">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-zinc-900">{{ $article->title }}</p>
                                        @if ($article->is_featured)
                                            <span
                                                class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-amber-700">Featured</span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs text-zinc-500">
                                        /{{ $article->locale->value }}/news/{{ $article->slug }}</p>

                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($locales as $localeOption)
                                            @php
                                                $translation = $article->translationVersions->first(
                                                    fn(\App\Models\News $version): bool => $version->locale ===
                                                        $localeOption,
                                                );
                                            @endphp

                                            @if ($translation instanceof \App\Models\News)
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-[11px] font-bold',
                                                    'bg-emerald-100 text-emerald-800' => $localeOption === $article->locale,
                                                    'bg-zinc-100 text-zinc-600' => $localeOption !== $article->locale,
                                                ])>
                                                    {{ strtoupper($localeOption->value) }} ✓
                                                </span>
                                            @elseif ($recordState !== 'trashed')
                                                @can('news.create')
                                                    <a href="{{ route('admin.news.translations.create', ['newsId' => $article->id, 'locale' => $localeOption->value]) }}"
                                                        wire:navigate
                                                        class="rounded-full border border-dashed border-zinc-300 px-2 py-0.5 text-[11px] font-bold text-zinc-500 hover:border-emerald-300 hover:text-emerald-700">
                                                        + {{ strtoupper($localeOption->value) }}
                                                    </a>
                                                @endcan
                                            @endif
                                        @endforeach
                                    </div>

                                    @if ($article->summary)
                                        <p class="mt-2 max-w-md text-sm text-zinc-600">
                                            {{ \Illuminate\Support\Str::limit($article->summary, 100) }}</p>
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $article->locale->nativeLabel() }}</span>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-zinc-400">
                                    {{ $article->locale->value }}</p>
                            </td>

                            <td class="px-5 py-4">
                                @if ($article->category)
                                    <span
                                        class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $article->category->name }}</span>
                                @else
                                    <span class="text-sm text-zinc-400">Uncategorised</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $article->status->label() }}</span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm font-medium text-zinc-800">
                                    {{ $article->creator?->name ?? 'Unknown' }}</p>
                                @if ($article->updater && !$article->updater->is($article->creator))
                                    <p class="mt-1 text-xs text-zinc-500">Edited by {{ $article->updater->name }}</p>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-sm text-zinc-700">{{ $article->updated_at?->format('Y-m-d') }}</p>
                                <p class="text-xs text-zinc-500">{{ $article->updated_at?->format('H:i') }}</p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($article->published_at)
                                    <p class="text-sm text-zinc-700">{{ $article->published_at->format('Y-m-d') }}</p>
                                    <p class="text-xs text-zinc-500">{{ $article->published_at->format('H:i') }}</p>
                                @else
                                    <span class="text-sm text-zinc-400">Not published</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex min-w-48 flex-wrap justify-end gap-2">
                                    @if ($recordState === 'trashed')
                                        @can('news.delete')
                                            <button type="button" wire:click="restoreArticle({{ $article->id }})"
                                                wire:confirm="Restore this news article from Trash?"
                                                wire:loading.attr="disabled"
                                                wire:target="restoreArticle({{ $article->id }})"
                                                class="inline-flex rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800 disabled:opacity-60">
                                                Restore
                                            </button>
                                        @endcan
                                    @else
                                        @can('news.view')
                                            <a href="{{ route('admin.news.preview', $article) }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                                Preview
                                            </a>
                                        @endcan

                                        @if ($article->status === \App\Enums\NewsStatus::Published)
                                            @can('news.publish')
                                                <button type="button" wire:click="unpublishArticle({{ $article->id }})"
                                                    wire:confirm="Unpublish this news article and return it to Draft?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="unpublishArticle({{ $article->id }})"
                                                    class="inline-flex rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100 disabled:opacity-60">
                                                    Unpublish
                                                </button>
                                            @endcan
                                        @else
                                            @can('news.publish')
                                                <button type="button" wire:click="publishArticle({{ $article->id }})"
                                                    wire:confirm="Publish this news article?" wire:loading.attr="disabled"
                                                    wire:target="publishArticle({{ $article->id }})"
                                                    class="inline-flex rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800 disabled:opacity-60">
                                                    Publish
                                                </button>
                                            @endcan
                                        @endif

                                        @if ($article->status === \App\Enums\NewsStatus::Draft)
                                            @can('news.update')
                                                <a href="{{ route('admin.news.edit', $article) }}" wire:navigate
                                                    class="inline-flex rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100">
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('news.delete')
                                                <button type="button" wire:click="deleteArticle({{ $article->id }})"
                                                    wire:confirm="Move this draft news article to Trash?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="deleteArticle({{ $article->id }})"
                                                    class="inline-flex rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:opacity-60">
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
                            <td colspan="8" class="px-5 py-14 text-center">
                                <p class="font-semibold text-zinc-700">No news articles found</p>
                                <p class="mt-1 text-sm text-zinc-500">Create an article or change the current filters.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($news->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4">{{ $news->links() }}</div>
        @endif
    </section>
</div>

<div class="mx-auto max-w-7xl space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-start
               lg:justify-between"
    >
        <div>
            <a
                href="{{ route(
                    'admin.news.edit',
                    ['news' => $news->id]
                ) }}"
                wire:navigate
                class="text-sm font-semibold
                       text-emerald-700
                       hover:text-emerald-800"
            >
                ← Back to Article
            </a>

            <h1
                class="mt-2 text-2xl
                       font-bold
                       text-zinc-950"
            >
                Revision History
            </h1>

            <p
                class="mt-1 max-w-3xl
                       text-sm leading-6
                       text-zinc-600"
            >
                Previous saved versions of
                <span class="font-semibold text-zinc-800">
                    {{ $news->title }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route(
                    'admin.news.index'
                ) }}"
                wire:navigate
                class="inline-flex items-center
                       rounded-xl
                       border border-zinc-300
                       bg-white
                       px-4 py-2.5
                       text-sm font-semibold
                       text-zinc-700
                       shadow-sm
                       transition
                       hover:bg-zinc-50"
            >
                News List
            </a>

            @can('news.update')
                <a
                    href="{{ route(
                        'admin.news.edit',
                        ['news' => $news->id]
                    ) }}"
                    wire:navigate
                    class="inline-flex items-center
                           rounded-xl
                           bg-emerald-700
                           px-4 py-2.5
                           text-sm font-semibold
                           text-white
                           shadow-sm
                           transition
                           hover:bg-emerald-800"
                >
                    Edit Article
                </a>
            @endcan
        </div>
    </div>


    {{-- =====================================================
         ARTICLE SUMMARY
    ====================================================== --}}
    <section
        class="rounded-2xl
               border border-zinc-200
               bg-white
               shadow-sm"
    >
        <div
            class="grid gap-4 p-5
                   sm:grid-cols-2
                   xl:grid-cols-4"
        >
            <div>
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Current Article
                </p>

                <p
                    class="mt-1 text-sm
                           font-semibold
                           text-zinc-900"
                >
                    {{ $news->title }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Current Status
                </p>

                <p
                    class="mt-1 text-sm
                           font-semibold
                           text-zinc-900"
                >
                    {{ $news->status->label() }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Category
                </p>

                <p
                    class="mt-1 text-sm
                           font-semibold
                           text-zinc-900"
                >
                    {{ $news->category?->name ?? 'Uncategorised' }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold
                           uppercase tracking-wide
                           text-zinc-500"
                >
                    Stored Revisions
                </p>

                <p
                    class="mt-1 text-sm
                           font-semibold
                           text-zinc-900"
                >
                    {{ $revisions->total() }}
                </p>
            </div>
        </div>
    </section>


    {{-- =====================================================
         MAIN GRID
    ====================================================== --}}
    <div
        class="grid gap-6
               xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)]"
    >

        {{-- =================================================
             REVISION LIST
        ================================================== --}}
        <section
            class="overflow-hidden
                   rounded-2xl
                   border border-zinc-200
                   bg-white
                   shadow-sm"
        >
            <div
                class="border-b border-zinc-200
                       px-5 py-4"
            >
                <h2
                    class="font-bold
                           text-zinc-900"
                >
                    Saved Revisions
                </h2>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Newest revision appears first.
                </p>
            </div>

            @if ($revisions->isEmpty())
                <div
                    class="px-6 py-12
                           text-center"
                >
                    <div
                        class="mx-auto flex
                               h-12 w-12
                               items-center
                               justify-center
                               rounded-full
                               bg-zinc-100
                               text-xl"
                    >
                        ↻
                    </div>

                    <h3
                        class="mt-4
                               font-semibold
                               text-zinc-900"
                    >
                        No revisions yet
                    </h3>

                    <p
                        class="mx-auto mt-2
                               max-w-sm
                               text-sm leading-6
                               text-zinc-500"
                    >
                        A revision is created automatically
                        when an editable news article is
                        successfully updated.
                    </p>
                </div>
            @else
                <div
                    class="divide-y
                           divide-zinc-100"
                >
                    @foreach ($revisions as $revision)
                        <button
                            type="button"
                            wire:click="selectRevision({{ $revision->id }})"
                            @class([
                                'block w-full px-5 py-4 text-left transition',

                                'bg-emerald-50' =>
                                    $selectedRevisionId ===
                                    $revision->id,

                                'hover:bg-zinc-50' =>
                                    $selectedRevisionId !==
                                    $revision->id,
                            ])
                        >
                            <div
                                class="flex items-start
                                       justify-between
                                       gap-4"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex flex-wrap
                                               items-center gap-2"
                                    >
                                        <span
                                            class="text-sm
                                                   font-bold
                                                   text-zinc-900"
                                        >
                                            Revision
                                            {{ $revision->revision_number }}
                                        </span>

                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-zinc-100
                                                   px-2 py-0.5
                                                   text-[11px]
                                                   font-bold
                                                   uppercase
                                                   tracking-wide
                                                   text-zinc-600"
                                        >
                                            {{ $revision->status->label() }}
                                        </span>
                                    </div>

                                    <p
                                        class="mt-2 truncate
                                               text-sm
                                               font-medium
                                               text-zinc-700"
                                    >
                                        {{ $revision->title }}
                                    </p>

                                    <p
                                        class="mt-2 text-xs
                                               text-zinc-500"
                                    >
                                        {{ $revision->created_at?->format(
                                            'd M Y, H:i'
                                        ) }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs
                                               text-zinc-500"
                                    >
                                        By
                                        {{ $revision->creator?->name
                                            ?? 'Unknown user' }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0
                                           text-zinc-400"
                                >
                                    →
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div
                    class="border-t
                           border-zinc-200
                           px-5 py-4"
                >
                    {{ $revisions->links() }}
                </div>
            @endif
        </section>


        {{-- =================================================
             REVISION PREVIEW
        ================================================== --}}
        <section
            class="overflow-hidden
                   rounded-2xl
                   border border-zinc-200
                   bg-white
                   shadow-sm"
        >
            @if ($selectedRevision)
                <div
                    class="flex flex-col gap-3
                           border-b border-zinc-200
                           px-6 py-4
                           sm:flex-row
                           sm:items-start
                           sm:justify-between"
                >
                    <div>
                        <h2
                            class="font-bold
                                   text-zinc-900"
                        >
                            Revision
                            {{ $selectedRevision->revision_number }}
                        </h2>

                        <p
                            class="mt-1 text-xs
                                   text-zinc-500"
                        >
                            Saved
                            {{ $selectedRevision->created_at?->format(
                                'd M Y, H:i'
                            ) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="clearSelection"
                        class="text-sm
                               font-semibold
                               text-zinc-500
                               hover:text-zinc-800"
                    >
                        Close Preview
                    </button>
                </div>


                {{-- Metadata --}}
                <div
                    class="grid gap-4
                           border-b border-zinc-200
                           bg-zinc-50
                           p-5
                           sm:grid-cols-2
                           lg:grid-cols-3"
                >
                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Status
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->status->label() }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Category
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->category?->name
                                ?? 'Uncategorised' }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Saved By
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->creator?->name
                                ?? 'Unknown user' }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Featured
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->is_featured
                                ? 'Yes'
                                : 'No' }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Publication
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->published_at
                                ? $selectedRevision->published_at->format(
                                    'd M Y, H:i'
                                )
                                : 'Not set' }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Reason
                        </p>

                        <p
                            class="mt-1 text-sm
                                   font-semibold
                                   text-zinc-800"
                        >
                            {{ $selectedRevision->reason
                                ?? 'No reason recorded' }}
                        </p>
                    </div>
                </div>


                {{-- Article --}}
                <div class="space-y-6 p-6">
                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Title
                        </p>

                        <h3
                            class="mt-2 text-xl
                                   font-bold
                                   text-zinc-950"
                        >
                            {{ $selectedRevision->title }}
                        </h3>
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Slug
                        </p>

                        <code
                            class="mt-2 inline-block
                                   rounded-lg
                                   bg-zinc-100
                                   px-3 py-2
                                   text-sm
                                   text-zinc-700"
                        >
                            {{ $selectedRevision->slug }}
                        </code>
                    </div>

                    @if ($selectedRevision->summary)
                        <div>
                            <p
                                class="text-xs font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-500"
                            >
                                Summary
                            </p>

                            <p
                                class="mt-2
                                       whitespace-pre-line
                                       text-sm leading-7
                                       text-zinc-700"
                            >
                                {{ $selectedRevision->summary }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-wide
                                   text-zinc-500"
                        >
                            Article Content
                        </p>

                        <div
                            class="awcms-content
                                   mt-3
                                   rounded-xl
                                   border border-zinc-200
                                   bg-white
                                   p-5"
                        >
                            {!! $selectedRevision->content !!}
                        </div>
                    </div>


                    {{-- SEO --}}
                    @if (
                        $selectedRevision->seo_title
                        || $selectedRevision->seo_description
                    )
                        <div
                            class="rounded-xl
                                   border border-zinc-200
                                   bg-zinc-50
                                   p-5"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase
                                       tracking-wide
                                       text-zinc-500"
                            >
                                SEO Snapshot
                            </p>

                            @if ($selectedRevision->seo_title)
                                <p
                                    class="mt-3
                                           text-sm font-semibold
                                           text-zinc-900"
                                >
                                    {{ $selectedRevision->seo_title }}
                                </p>
                            @endif

                            @if ($selectedRevision->seo_description)
                                <p
                                    class="mt-2
                                           text-sm leading-6
                                           text-zinc-600"
                                >
                                    {{ $selectedRevision->seo_description }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <div
                    class="flex min-h-[28rem]
                           items-center
                           justify-center
                           p-8 text-center"
                >
                    <div class="max-w-md">
                        <div
                            class="mx-auto flex
                                   h-14 w-14
                                   items-center
                                   justify-center
                                   rounded-full
                                   bg-emerald-50
                                   text-2xl
                                   text-emerald-700"
                        >
                            ↻
                        </div>

                        <h2
                            class="mt-4 text-lg
                                   font-bold
                                   text-zinc-900"
                        >
                            Select a Revision
                        </h2>

                        <p
                            class="mt-2
                                   text-sm leading-6
                                   text-zinc-500"
                        >
                            Choose a revision from the history
                            list to inspect the article state
                            stored at that point in time.
                        </p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
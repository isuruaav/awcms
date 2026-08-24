<div class="mx-auto max-w-7xl space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-start
               lg:justify-between">
        <div>
            <a href="{{ route('admin.news.index') }}" wire:navigate
                class="text-sm font-semibold
                       text-emerald-700
                       transition
                       hover:text-emerald-800">
                ← Back to News
            </a>

            <h1 class="mt-2 text-2xl
                       font-bold
                       text-zinc-950">
                Edit News Article
            </h1>

            <p class="mt-1 max-w-3xl
                       text-sm leading-6
                       text-zinc-600">
                Update article content, review workflow,
                publication settings and SEO information.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            {{-- Current Status --}}
            <span @class([
                'inline-flex rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-wide',
            
                'bg-zinc-100 text-zinc-700' => $status === \App\Enums\NewsStatus::Draft,
            
                'bg-blue-50 text-blue-700' => $status === \App\Enums\NewsStatus::Submitted,
            
                'bg-orange-50 text-orange-700' =>
                    $status === \App\Enums\NewsStatus::ChangesRequested,
            
                'bg-violet-50 text-violet-700' =>
                    $status === \App\Enums\NewsStatus::Approved,
            
                'bg-emerald-50 text-emerald-700' =>
                    $status === \App\Enums\NewsStatus::Published,
            
                'bg-amber-50 text-amber-700' => $status === \App\Enums\NewsStatus::Archived,
            ])>
                {{ $status->label() }}
            </span>

            @if ($news->is_featured)
                <span
                    class="inline-flex rounded-full
                           bg-amber-100
                           px-3 py-1.5
                           text-xs font-bold
                           uppercase tracking-wide
                           text-amber-700">
                    Featured
                </span>
            @endif
        </div>
    </div>


    {{-- =====================================================
         FLASH MESSAGE
    ====================================================== --}}
    @if (session('status'))
        <div
            class="rounded-xl
                   border border-emerald-200
                   bg-emerald-50
                   px-5 py-4
                   text-sm font-medium
                   text-emerald-800">
            {{ session('status') }}
        </div>
    @endif


    {{-- =====================================================
         WORKFLOW ERROR
    ====================================================== --}}
    @error('workflow')
        <div
            class="rounded-xl
                   border border-red-200
                   bg-red-50
                   px-5 py-4">
            <p class="text-sm font-bold text-red-800">
                Workflow action failed
            </p>

            <p class="mt-1 text-sm text-red-700">
                {{ $message }}
            </p>
        </div>
    @enderror


    {{-- =====================================================
         CHANGES REQUESTED NOTICE
    ====================================================== --}}
    @if ($status === \App\Enums\NewsStatus::ChangesRequested && $news->change_request_note)
        <div
            class="rounded-xl
                   border border-orange-200
                   bg-orange-50
                   px-5 py-4">
            <div
                class="flex flex-col gap-2
                       sm:flex-row
                       sm:items-start
                       sm:justify-between">
                <div>
                    <p class="text-sm font-bold
                               text-orange-900">
                        Changes Requested
                    </p>

                    <p
                        class="mt-2 whitespace-pre-line
                               text-sm leading-6
                               text-orange-800">
                        {{ $news->change_request_note }}
                    </p>
                </div>

                @if ($news->changes_requested_at)
                    <span class="shrink-0 text-xs
                               text-orange-700">
                        {{ $news->changes_requested_at->format('d M Y, H:i') }}
                    </span>
                @endif
            </div>
        </div>
    @endif


    {{-- =====================================================
         READ ONLY WARNING
    ====================================================== --}}
    @if (!$editable)
        <div
            class="rounded-xl
                   border border-amber-200
                   bg-amber-50
                   px-5 py-4">
            <p class="text-sm font-bold
           text-amber-800">
                Editing locked
            </p>

            <p class="mt-1 text-sm
                       leading-6
                       text-amber-700">
                The article cannot be edited while its current
                workflow status is
                <span class="font-semibold">
                    {{ $status->label() }}
                </span>.

                Workflow actions may still be available below
                depending on your permissions.
            </p>
        </div>
    @endif


    {{-- =====================================================
         VALIDATION SUMMARY
    ====================================================== --}}
    @if ($errors->any())
        <div
            class="rounded-xl
                   border border-red-200
                   bg-red-50
                   px-5 py-4">
            <p class="text-sm font-bold
                       text-red-800">
                Please correct the highlighted fields.
            </p>

            <ul
                class="mt-2 list-inside list-disc
                       space-y-1
                       text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- =====================================================
         MAIN FORM
    ====================================================== --}}
    <form wire:submit="save" class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_380px]">

        {{-- =================================================
             LEFT COLUMN
        ================================================== --}}
        <div class="space-y-6">

            {{-- =============================================
                 ARTICLE DETAILS
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Article Details
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Main article information and public URL.
                    </p>
                </div>

                <div class="space-y-5 p-6">

                    {{-- Title --}}
                    <div>
                        <label for="news-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Title

                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <input id="news-title" type="text" maxlength="255" wire:model="title"
                            @disabled(!$editable) placeholder="Enter article title"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm text-zinc-900
                                   outline-none transition
                                   placeholder:text-zinc-400
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10
                                   disabled:cursor-not-allowed
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


                    {{-- Slug --}}
                    <div>
                        <label for="news-slug"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            URL Slug
                        </label>

                        <div
                            class="flex overflow-hidden
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   focus-within:border-emerald-500
                                   focus-within:ring-4
                                   focus-within:ring-emerald-500/10">
                            <span
                                class="flex items-center
                                       border-r border-zinc-200
                                       bg-zinc-50
                                       px-3
                                       text-sm
                                       text-zinc-500">
                                /news/
                            </span>

                            <input id="news-slug" type="text" maxlength="255" wire:model="slug"
                                @disabled(!$editable)
                                class="min-w-0 flex-1
                                       border-0
                                       bg-white
                                       px-4 py-3
                                       text-sm
                                       text-zinc-900
                                       outline-none
                                       focus:ring-0
                                       disabled:cursor-not-allowed
                                       disabled:bg-zinc-100
                                       disabled:text-zinc-500">
                        </div>

                        <p
                            class="mt-2 text-xs
                                   leading-5
                                   text-zinc-500">
                            Keep the existing slug unless the
                            public URL must intentionally change.
                        </p>

                        @error('slug')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Summary --}}
                    <div>
                        <div
                            class="mb-2 flex
                                   items-center
                                   justify-between
                                   gap-3">
                            <label for="news-summary"
                                class="text-sm font-semibold
                                       text-zinc-800">
                                Summary
                            </label>

                            <span class="text-xs
                                       text-zinc-400">
                                Max 2000 characters
                            </span>
                        </div>

                        <textarea id="news-summary" rows="4" maxlength="2000" wire:model="summary" @disabled(!$editable)
                            placeholder="Short article introduction..."
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm leading-6
                                   text-zinc-900
                                   outline-none transition
                                   placeholder:text-zinc-400
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500"></textarea>

                        @error('summary')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>


            {{-- =============================================
                 ARTICLE BODY
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Article Body
                    </h2>

                    <p class="mt-1 text-xs
                               text-zinc-500">
                        Rich-text article content.
                    </p>
                </div>

                <div class="p-6">
                    <label for="news-content-editor"
                        class="mb-2 block
                               text-sm font-semibold
                               text-zinc-800">
                        Content

                        <span class="text-red-600">
                            *
                        </span>
                    </label>

                    @if ($editable)
                        <div wire:ignore>
                            <input id="news-content-input" type="hidden" value="{{ $content }}">

                            <trix-editor id="news-content-editor" input="news-content-input" class="awcms-editor" x-data
                                x-on:trix-change="
                                    $wire.set(
                                        'content',
                                        $event.target.value
                                    )
                                "></trix-editor>
                        </div>
                    @else
                        <div
                            class="min-h-72
                                   rounded-xl
                                   border border-zinc-200
                                   bg-zinc-50
                                   p-5">
                            <div class="awcms-content">
                                {!! $content !!}
                            </div>
                        </div>
                    @endif

                    <p
                        class="mt-3 text-xs
                               leading-5
                               text-zinc-500">
                        File attachments are disabled.
                        Images and documents must be managed
                        through the Media Library.
                    </p>

                    @error('content')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
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
                <div class="border-b border-zinc-200
                           px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Search Engine Optimisation
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Optional search and sharing metadata.
                    </p>
                </div>

                <div class="space-y-5 p-6">

                    {{-- SEO Title --}}
                    <div>
                        <label for="news-seo-title"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            SEO Title
                        </label>

                        <input id="news-seo-title" type="text" maxlength="255" wire:model="seoTitle"
                            @disabled(!$editable) placeholder="Optional SEO title"
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm text-zinc-900
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">

                        @error('seoTitle')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- SEO Description --}}
                    <div>
                        <div
                            class="mb-2 flex
                                   items-center
                                   justify-between
                                   gap-3">
                            <label for="news-seo-description"
                                class="text-sm font-semibold
                                       text-zinc-800">
                                SEO Description
                            </label>

                            <span class="text-xs text-zinc-400">
                                Max 320
                            </span>
                        </div>

                        <textarea id="news-seo-description" rows="3" maxlength="320" wire:model="seoDescription"
                            @disabled(!$editable) placeholder="Optional search description..."
                            class="w-full resize-y
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm leading-6
                                   text-zinc-900
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-4
                                   focus:ring-emerald-500/10
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500"></textarea>

                        @error('seoDescription')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>
        </div>


        {{-- =================================================
             RIGHT COLUMN
        ================================================== --}}
        <aside class="space-y-6">

            {{-- =============================================
                 WORKFLOW STATUS
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-5 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Workflow Status
                    </h2>

                    <p class="mt-1 text-xs
                               text-zinc-500">
                        Review and publication progress.
                    </p>
                </div>

                <div class="space-y-5 p-5">

                    {{-- Current Status --}}
                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Current Status
                        </p>

                        <span @class([
                            'mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-wide',
                        
                            'bg-zinc-100 text-zinc-700' => $status === \App\Enums\NewsStatus::Draft,
                        
                            'bg-blue-50 text-blue-700' => $status === \App\Enums\NewsStatus::Submitted,
                        
                            'bg-orange-50 text-orange-700' =>
                                $status === \App\Enums\NewsStatus::ChangesRequested,
                        
                            'bg-violet-50 text-violet-700' =>
                                $status === \App\Enums\NewsStatus::Approved,
                        
                            'bg-emerald-50 text-emerald-700' =>
                                $status === \App\Enums\NewsStatus::Published,
                        
                            'bg-amber-50 text-amber-700' => $status === \App\Enums\NewsStatus::Archived,
                        ])>
                            {{ $status->label() }}
                        </span>
                    </div>


                    {{-- Workflow Pipeline --}}
                    <div
                        class="rounded-xl
                               border border-zinc-200
                               bg-zinc-50
                               p-4">
                        <p
                            class="text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Workflow
                        </p>

                        <div class="mt-3 space-y-2
                                   text-sm text-zinc-700">
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full
                                           bg-zinc-500"></span>

                                Draft
                            </div>

                            <div class="pl-1 text-zinc-300">
                                ↓
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full
                                           bg-blue-500"></span>

                                Submitted for Review
                            </div>

                            <div class="pl-1 text-zinc-300">
                                ↓
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full
                                           bg-violet-500"></span>

                                Approved
                            </div>

                            <div class="pl-1 text-zinc-300">
                                ↓
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full
                                           bg-emerald-500"></span>

                                Published
                            </div>

                            <div class="pl-1 text-zinc-300">
                                ↓
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full
                                           bg-amber-500"></span>

                                Archived
                            </div>
                        </div>
                    </div>


                    {{-- Submitted --}}
                    @if ($news->submitted_at)
                        <div class="border-t border-zinc-100
                                   pt-4">
                            <p
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500">
                                Submitted
                            </p>

                            <p
                                class="mt-1 text-sm
                                       font-medium
                                       text-zinc-700">
                                {{ $news->submitted_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif


                    {{-- Changes Requested --}}
                    @if ($news->changes_requested_at)
                        <div class="border-t border-zinc-100
                                   pt-4">
                            <p
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-orange-600">
                                Changes Requested
                            </p>

                            <p
                                class="mt-1 text-sm
                                       font-medium
                                       text-zinc-700">
                                {{ $news->changes_requested_at->format('d M Y, H:i') }}
                            </p>

                            @if ($news->change_request_note)
                                <p
                                    class="mt-2 whitespace-pre-line
                                           rounded-lg
                                           bg-orange-50
                                           p-3
                                           text-xs leading-5
                                           text-orange-800">
                                    {{ $news->change_request_note }}
                                </p>
                            @endif
                        </div>
                    @endif


                    {{-- Approved --}}
                    @if ($news->approved_at)
                        <div class="border-t border-zinc-100
                                   pt-4">
                            <p
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500">
                                Approved
                            </p>

                            <p
                                class="mt-1 text-sm
                                       font-medium
                                       text-zinc-700">
                                {{ $news->approved_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif


                    {{-- Published --}}
                    @if ($status === \App\Enums\NewsStatus::Published && $news->published_at)
                        <div class="border-t border-zinc-100
                                   pt-4">
                            <p
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-emerald-600">
                                Published
                            </p>

                            <p
                                class="mt-1 text-sm
                                       font-medium
                                       text-zinc-700">
                                {{ $news->published_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif


                    {{-- Archived --}}
                    @if ($news->archived_at)
                        <div class="border-t border-zinc-100
                                   pt-4">
                            <p
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500">
                                Archived
                            </p>

                            <p
                                class="mt-1 text-sm
                                       font-medium
                                       text-zinc-700">
                                {{ $news->archived_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>


            {{-- =============================================
                 WORKFLOW ACTIONS
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-5 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Workflow Actions
                    </h2>

                    <p class="mt-1 text-xs
                               text-zinc-500">
                        Available actions depend on status and
                        your assigned permissions.
                    </p>
                </div>

                <div class="space-y-3 p-5">

                    {{-- =========================================
                         SAVE
                    ========================================== --}}
                    @if ($editable)
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-5 py-3
                                   text-sm font-bold
                                   text-zinc-700
                                   transition
                                   hover:bg-zinc-50
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                Save Changes
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>
                    @endif


                    {{-- =========================================
                         SUBMIT FOR REVIEW
                    ========================================== --}}
                    @if ($canSubmit)
                        <button type="button" wire:click="submitForReview"
                            wire:confirm="Submit this news article for review? The latest editor changes will be saved first."
                            wire:loading.attr="disabled" wire:target="submitForReview"
                            class="inline-flex w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-blue-700
                                   px-5 py-3
                                   text-sm font-bold
                                   text-white
                                   transition
                                   hover:bg-blue-800
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="submitForReview">
                                Submit for Review
                            </span>

                            <span wire:loading wire:target="submitForReview">
                                Submitting...
                            </span>
                        </button>

                        <p class="text-xs leading-5
                                   text-zinc-500">
                            The latest form content will be saved
                            before submission.
                        </p>
                    @endif


                    {{-- =========================================
                         SUBMITTED REVIEW ACTIONS
                    ========================================== --}}
                    @if ($canRequestChanges || $canApprove)

                        <div
                            class="rounded-xl
                                   border border-blue-200
                                   bg-blue-50
                                   p-4">
                            <p class="text-sm font-bold
                                       text-blue-900">
                                Article awaiting review
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-blue-700">
                                Review the article content before
                                approving it or requesting changes.
                            </p>
                        </div>


                        {{-- Request Changes --}}
                        @if ($canRequestChanges)
                            <div
                                class="rounded-xl
                                       border border-orange-200
                                       bg-orange-50
                                       p-4">
                                <label for="change-request-note"
                                    class="block text-sm
                                           font-bold
                                           text-orange-900">
                                    Request Changes
                                </label>

                                <p
                                    class="mt-1 text-xs
                                           leading-5
                                           text-orange-700">
                                    Explain clearly what the
                                    Content Editor must correct.
                                </p>

                                <textarea id="change-request-note" rows="4" maxlength="1000" wire:model="changeRequestNote"
                                    placeholder="Describe the required changes..."
                                    class="mt-3 w-full
                                           resize-y
                                           rounded-xl
                                           border border-orange-300
                                           bg-white
                                           px-3 py-2.5
                                           text-sm
                                           text-zinc-900
                                           outline-none
                                           focus:border-orange-500
                                           focus:ring-4
                                           focus:ring-orange-500/10"></textarea>

                                @error('changeRequestNote')
                                    <p
                                        class="mt-2
                                               text-xs font-semibold
                                               text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <button type="button" wire:click="requestChanges"
                                    wire:confirm="Send this article back for changes?" wire:loading.attr="disabled"
                                    wire:target="requestChanges"
                                    class="mt-3 inline-flex
                                           w-full items-center
                                           justify-center
                                           rounded-xl
                                           bg-orange-600
                                           px-4 py-2.5
                                           text-sm font-bold
                                           text-white
                                           transition
                                           hover:bg-orange-700
                                           disabled:cursor-not-allowed
                                           disabled:opacity-60">
                                    <span wire:loading.remove wire:target="requestChanges">
                                        Request Changes
                                    </span>

                                    <span wire:loading wire:target="requestChanges">
                                        Sending...
                                    </span>
                                </button>
                            </div>
                        @endif


                        {{-- Approve --}}
                        @if ($canApprove)
                            <button type="button" wire:click="approve"
                                wire:confirm="Approve this news article for publication?" wire:loading.attr="disabled"
                                wire:target="approve"
                                class="inline-flex w-full
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-violet-700
                                       px-5 py-3
                                       text-sm font-bold
                                       text-white
                                       transition
                                       hover:bg-violet-800
                                       disabled:cursor-not-allowed
                                       disabled:opacity-60">
                                <span wire:loading.remove wire:target="approve">
                                    Approve Article
                                </span>

                                <span wire:loading wire:target="approve">
                                    Approving...
                                </span>
                            </button>
                        @endif
                    @endif


                    {{-- =========================================
                         PUBLISH
                    ========================================== --}}
                    @if ($canPublish)
                        <div
                            class="rounded-xl
                                   border border-violet-200
                                   bg-violet-50
                                   p-4">
                            <p class="text-sm font-bold
                                       text-violet-900">
                                Article approved
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-violet-700">
                                The article has passed review and
                                is ready for publication.
                            </p>
                        </div>

                        <button type="button" wire:click="publish" wire:confirm="Publish this news article?"
                            wire:loading.attr="disabled" wire:target="publish"
                            class="inline-flex w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-emerald-700
                                   px-5 py-3
                                   text-sm font-bold
                                   text-white
                                   transition
                                   hover:bg-emerald-800
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="publish">
                                Publish Article
                            </span>

                            <span wire:loading wire:target="publish">
                                Publishing...
                            </span>
                        </button>

                        @if ($news->published_at)
                            <p class="text-xs leading-5
                                       text-zinc-500">
                                Planned publication:
                                <span class="font-semibold">
                                    {{ $news->published_at->format('d M Y, H:i') }}
                                </span>
                            </p>
                        @else
                            <p class="text-xs leading-5
                                       text-zinc-500">
                                No planned publication time is set.
                                Publishing will use the current time.
                            </p>
                        @endif
                    @endif


                    {{-- =========================================
                         ARCHIVE
                    ========================================== --}}
                    @if ($canArchive)
                        <div
                            class="rounded-xl
                                   border border-emerald-200
                                   bg-emerald-50
                                   p-4">
                            <p class="text-sm font-bold
                                       text-emerald-900">
                                Article is published
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-emerald-700">
                                Archiving removes the article from
                                the active publishing workflow.
                            </p>
                        </div>

                        <button type="button" wire:click="archive"
                            wire:confirm="Archive this published news article?" wire:loading.attr="disabled"
                            wire:target="archive"
                            class="inline-flex w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-amber-600
                                   px-5 py-3
                                   text-sm font-bold
                                   text-white
                                   transition
                                   hover:bg-amber-700
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60">
                            <span wire:loading.remove wire:target="archive">
                                Archive Article
                            </span>

                            <span wire:loading wire:target="archive">
                                Archiving...
                            </span>
                        </button>
                    @endif


                    {{-- =========================================
                         FINAL / NO ACTION
                    ========================================== --}}
                    @if (!$editable && !$canRequestChanges && !$canApprove && !$canPublish && !$canArchive)
                        <div
                            class="rounded-xl
                                   border border-zinc-200
                                   bg-zinc-50
                                   p-4">
                            <p class="text-sm font-semibold
                                       text-zinc-800">
                                No workflow action available
                            </p>

                            <p
                                class="mt-1 text-xs
                                       leading-5
                                       text-zinc-500">
                                Your current permissions and this
                                article status do not provide
                                another workflow action.
                            </p>
                        </div>
                    @endif
                </div>
            </section>


            {{-- =============================================
                 ARTICLE SETTINGS
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-5 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Article Settings
                    </h2>
                </div>

                <div class="space-y-5 p-5">

                    {{-- Category --}}
                    <div>
                        <label for="news-category-id"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Category

                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <select id="news-category-id" wire:model="categoryId" @disabled(!$editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm text-zinc-900
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">
                            <option value="">
                                Select category
                            </option>

                            @foreach ($categories as $categoryOption)
                                <option value="{{ $categoryOption->id }}">
                                    {{ $categoryOption->name }}

                                    @if (!$categoryOption->is_active)
                                        (Inactive)
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('categoryId')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Publication --}}
                    <div>
                        <label for="news-published-at"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Planned Publication
                        </label>

                        <input id="news-published-at" type="datetime-local" wire:model="publishedAt"
                            @disabled(!$editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm text-zinc-900
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">

                        <p
                            class="mt-2 text-xs
                                   leading-5
                                   text-zinc-500">
                            Setting this value does not bypass
                            approval. Publication is controlled
                            through the workflow.
                        </p>

                        @error('publishedAt')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Featured --}}
                    <label
                        class="flex items-start
                               gap-3 rounded-xl
                               border border-zinc-200
                               bg-zinc-50 p-4
                               {{ $editable ? 'cursor-pointer' : 'cursor-not-allowed opacity-70' }}">
                        <input type="checkbox" wire:model="isFeatured" @disabled(!$editable)
                            class="mt-1 h-4 w-4
                                   rounded
                                   border-zinc-300">

                        <span>
                            <span
                                class="block text-sm
                                       font-semibold
                                       text-zinc-800">
                                Featured Article
                            </span>

                            <span
                                class="mt-1 block
                                       text-xs leading-5
                                       text-zinc-500">
                                Allow this article to appear in
                                featured news sections.
                            </span>
                        </span>
                    </label>
                </div>
            </section>


            {{-- =============================================
                 FEATURED IMAGE
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-5 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Main Image
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Public images from the Media Library.
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <label for="news-featured-image"
                            class="mb-2 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            Featured Image
                        </label>

                        <select id="news-featured-image" wire:model.live="featuredImageId"
                            @disabled(!$editable)
                            class="w-full rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-4 py-3
                                   text-sm text-zinc-900
                                   disabled:cursor-not-allowed
                                   disabled:bg-zinc-100
                                   disabled:text-zinc-500">
                            <option value="">
                                No featured image
                            </option>

                            @foreach ($images as $image)
                                <option value="{{ $image->id }}">
                                    #{{ $image->id }}
                                    —
                                    {{ $image->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('featuredImageId')
                            <p
                                class="mt-2
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    @if ($featuredImageId !== '')
                        @php
                            $selectedImage = $images->firstWhere('id', (int) $featuredImageId);
                        @endphp

                        @if ($selectedImage)
                            <div
                                class="rounded-xl
                                       border border-emerald-200
                                       bg-emerald-50
                                       p-4">
                                <p
                                    class="text-xs font-bold
                                           uppercase tracking-wide
                                           text-emerald-700">
                                    Selected Image
                                </p>

                                <p
                                    class="mt-2
                                           text-sm font-semibold
                                           text-zinc-900">
                                    {{ $selectedImage->title }}
                                </p>

                                <p
                                    class="mt-1 break-all
                                           text-xs text-zinc-500">
                                    {{ $selectedImage->original_name }}
                                </p>

                                @can('media.view')
                                    <a href="{{ route('admin.media.edit', [
                                        'media' => $selectedImage->id,
                                    ]) }}"
                                        target="_blank"
                                        class="mt-3 inline-flex
                                               text-xs font-semibold
                                               text-emerald-700
                                               hover:text-emerald-800">
                                        View Media →
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div
                                class="rounded-xl
                                       border border-amber-200
                                       bg-amber-50
                                       p-4">
                                <p
                                    class="text-sm font-medium
                                           text-amber-800">
                                    The currently selected media is
                                    no longer available in the
                                    selectable Public image list.
                                </p>
                            </div>
                        @endif
                    @endif

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
            </section>


            {{-- =============================================
                 ARTICLE INFORMATION
            ============================================== --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div class="border-b border-zinc-200
                           px-5 py-4">
                    <h2 class="font-bold text-zinc-900">
                        Article Information
                    </h2>
                </div>

                <div class="divide-y divide-zinc-100
                           px-5">
                    <div
                        class="flex items-start
                               justify-between
                               gap-4 py-4">
                        <span
                            class="text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            ID
                        </span>

                        <span class="text-sm font-semibold
                                   text-zinc-700">
                            #{{ $news->id }}
                        </span>
                    </div>

                    <div
                        class="flex items-start
                               justify-between
                               gap-4 py-4">
                        <span
                            class="text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Created
                        </span>

                        <span class="text-right text-sm
                                   text-zinc-700">
                            {{ $news->created_at?->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <div
                        class="flex items-start
                               justify-between
                               gap-4 py-4">
                        <span
                            class="text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Updated
                        </span>

                        <span class="text-right text-sm
                                   text-zinc-700">
                            {{ $news->updated_at?->format('d M Y, H:i') }}
                        </span>
                    </div>

                    @if ($news->published_at)
                        <div
                            class="flex items-start
                                   justify-between
                                   gap-4 py-4">
                            <span
                                class="text-xs font-bold
                                       uppercase tracking-wide
                                       text-zinc-500">
                                Publication
                            </span>

                            <span class="text-right text-sm
                                       text-zinc-700">
                                {{ $news->published_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @endif
                </div>
            </section>


            {{-- =============================================
                 NAVIGATION
            ============================================== --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm">
                <div class="space-y-3">
                    <a href="{{ route('admin.news.revisions', [
                        'news' => $news->id,
                    ]) }}"
                        wire:navigate
                        class="inline-flex w-full
                               items-center
                               justify-center
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-5 py-3
                               text-sm font-semibold
                               text-zinc-700
                               transition
                               hover:bg-zinc-50">
                        Revision History
                    </a>

                    <a href="{{ route('admin.news.index') }}" wire:navigate
                        class="inline-flex w-full
                               items-center
                               justify-center
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-5 py-3
                               text-sm font-semibold
                               text-zinc-700
                               transition
                               hover:bg-zinc-50">
                        Back to News
                    </a>
                </div>
            </section>
        </aside>
    </form>
</div>

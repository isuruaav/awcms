<div class="space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col gap-4
               sm:flex-row
               sm:items-center
               sm:justify-between">
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
                Create Document
            </h1>

            <p class="mt-1
                       text-sm leading-6
                       text-zinc-500">
                Create the document record first.
                The PDF file can be added as a version after saving.
            </p>
        </div>

        <a href="{{ route('admin.documents.index') }}" wire:navigate
            class="inline-flex items-center
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


    {{-- =====================================================
         VALIDATION ERRORS
    ====================================================== --}}

    @if ($errors->any())
        <div
            class="rounded-xl
                   border border-red-200
                   bg-red-50
                   px-4 py-3
                   text-sm text-red-700">
            <p class="font-bold">
                Please correct the following:
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


    <form wire:submit="save" class="grid gap-6
               xl:grid-cols-[minmax(0,1fr)_360px]">

        {{-- =================================================
             MAIN COLUMN
        ================================================== --}}

        <div class="space-y-6">

            {{-- DOCUMENT INFORMATION --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold
                               text-zinc-900">
                        Document Information
                    </h2>

                    <p class="mt-1
                               text-xs
                               text-zinc-500">
                        Main information used for the
                        document and public listing.
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

                            <span class="text-red-600">
                                *
                            </span>
                        </label>

                        <input id="document-title" type="text" wire:model="title" maxlength="255" autofocus
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
                                   focus:ring-emerald-100"
                            placeholder="Example: Annual Report 2026">

                        @error('title')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
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
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                            placeholder="Leave blank to generate automatically">

                        <p
                            class="mt-1.5
                                   text-xs
                                   text-zinc-500">
                            Leave blank and AWCMS will generate
                            a unique URL slug from the title.
                        </p>

                        @error('slug')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
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

                        <select id="document-category" wire:model="categoryId"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100">
                            <option value="">
                                No category
                            </option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($categories->isEmpty())
                            <p
                                class="mt-1.5
                                       text-xs
                                       text-amber-700">
                                No active document categories are available.
                                The document can still be created without a category.
                            </p>
                        @endif

                        @error('categoryId')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
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

                        <input id="document-date" type="date" wire:model="documentDate"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100">

                        @error('documentDate')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
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
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   leading-6
                                   text-zinc-900
                                   shadow-sm
                                   outline-none
                                   focus:border-emerald-500
                                   focus:ring-2
                                   focus:ring-emerald-100"
                            placeholder="Short description about this document..."></textarea>

                        @error('description')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </section>


            {{-- PDF VERSION INFORMATION --}}

            <section
                class="rounded-2xl
                       border border-emerald-200
                       bg-emerald-50
                       shadow-sm">
                <div class="p-6">
                    <div class="flex items-start gap-3">

                        <div class="flex h-10 w-10
                                   shrink-0 items-center
                                   justify-center
                                   rounded-xl
                                   bg-emerald-100
                                   text-lg
                                   font-bold
                                   text-emerald-700"
                            aria-hidden="true">
                            PDF
                        </div>

                        <div>
                            <h2 class="font-bold
                                       text-emerald-900">
                                PDF Version
                            </h2>

                            <p
                                class="mt-1
                                       text-sm leading-6
                                       text-emerald-800">
                                Create the document first.
                                After saving, upload the first PDF
                                from the Media Library as version 1.
                            </p>

                            <p
                                class="mt-2
                                       text-xs leading-5
                                       text-emerald-700">
                                Future PDF replacements will create
                                additional versions while keeping the
                                document URL stable.
                            </p>
                        </div>

                    </div>
                </div>
            </section>


            {{-- SEO --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm">
                <div
                    class="border-b border-zinc-200
                           bg-zinc-50
                           px-6 py-4">
                    <h2 class="font-bold
                               text-zinc-900">
                        Search Engine Information
                    </h2>
                </div>

                <div class="space-y-5 p-6">

                    {{-- SEO Title --}}

                    <div>
                        <label for="document-seo-title"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            SEO Title
                        </label>

                        <input id="document-seo-title" type="text" wire:model="seoTitle" maxlength="255"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   text-zinc-900">

                        @error('seoTitle')
                            <p
                                class="mt-1.5
                                       text-sm font-medium
                                       text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- SEO Description --}}

                    <div>
                        <label for="document-seo-description"
                            class="mb-1.5 block
                                   text-sm font-semibold
                                   text-zinc-800">
                            SEO Description
                        </label>

                        <textarea id="document-seo-description" wire:model="seoDescription" maxlength="320" rows="4"
                            class="w-full
                                   rounded-xl
                                   border border-zinc-300
                                   bg-white
                                   px-3 py-2.5
                                   text-sm
                                   leading-6
                                   text-zinc-900"></textarea>

                        <div
                            class="mt-1
                                   text-right
                                   text-xs
                                   text-zinc-400">
                            {{ mb_strlen($seoDescription) }}/320
                        </div>

                        @error('seoDescription')
                            <p
                                class="mt-1.5
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
                    Initial Status
                </p>

                <div
                    class="mt-3
                           inline-flex
                           rounded-full
                           bg-zinc-100
                           px-3 py-1.5
                           text-sm font-bold
                           text-zinc-700">
                    Draft
                </div>

                <p class="mt-3
                           text-sm leading-6
                           text-zinc-500">
                    New documents always start as Draft.
                    A valid public PDF version is required
                    before publication.
                </p>
            </section>


            {{-- PUBLICATION --}}

            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm">
                <h2 class="font-bold
                           text-zinc-900">
                    Publication
                </h2>

                <div class="mt-4">

                    <label for="document-published-at"
                        class="mb-1.5 block
                               text-sm font-semibold
                               text-zinc-800">
                        Planned Publish Date
                    </label>

                    <input id="document-published-at" type="datetime-local" wire:model="publishedAt"
                        class="w-full
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-3 py-2.5
                               text-sm
                               text-zinc-900">

                    <p
                        class="mt-2
                               text-xs leading-5
                               text-zinc-500">
                        Optional. The document still remains
                        Draft until an authorised user publishes it.
                    </p>

                    @error('publishedAt')
                        <p
                            class="mt-1.5
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>
            </section>


            {{-- SAVE --}}

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
                           shadow-sm
                           transition
                           hover:bg-emerald-800
                           disabled:cursor-not-allowed
                           disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">
                        Create Document
                    </span>

                    <span wire:loading wire:target="save">
                        Creating...
                    </span>
                </button>

                <a href="{{ route('admin.documents.index') }}" wire:navigate
                    class="mt-3
                           inline-flex
                           w-full
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
                    Cancel
                </a>
            </section>

        </aside>

    </form>

</div>

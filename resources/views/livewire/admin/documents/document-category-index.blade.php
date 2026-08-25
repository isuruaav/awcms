<div class="mx-auto max-w-7xl space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div
        class="flex flex-col gap-4
               lg:flex-row
               lg:items-center
               lg:justify-between">
        <div>
            <p
                class="text-xs font-bold
                       uppercase tracking-[0.18em]
                       text-emerald-700">
                Document Management
            </p>

            <h1 class="mt-1
                       text-2xl font-bold
                       text-zinc-950">
                Document Categories
            </h1>

            <p class="mt-1
                       text-sm
                       text-zinc-500">
                Manage categories used to organise
                documents, reports and PDF publications.
            </p>
        </div>

        <div class="flex flex-wrap
                   items-center
                   gap-3">
            <a href="{{ route('admin.documents.index') }}" wire:navigate
                class="inline-flex
                       items-center
                       justify-center
                       rounded-xl
                       border border-zinc-300
                       bg-white
                       px-5 py-3
                       text-sm font-semibold
                       text-zinc-700
                       shadow-sm
                       transition
                       hover:bg-zinc-50">
                Back to Documents
            </a>

            <button type="button" wire:click="openCreate"
                class="inline-flex
                       items-center
                       justify-center
                       rounded-xl
                       bg-emerald-700
                       px-5 py-3
                       text-sm font-bold
                       text-white
                       transition
                       hover:bg-emerald-800">
                + New Category
            </button>
        </div>
    </div>


    {{-- =====================================================
         STATUS
    ====================================================== --}}

    @if (session('status'))
        <div
            class="rounded-xl
                   border border-emerald-200
                   bg-emerald-50
                   px-4 py-3
                   text-sm font-semibold
                   text-emerald-800">
            {{ session('status') }}
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
         CREATE / EDIT FORM
    ====================================================== --}}

    @if ($showForm)
        <form wire:submit="save"
            class="overflow-hidden
                   rounded-2xl
                   border border-zinc-200
                   bg-white
                   shadow-sm">
            <div
                class="flex items-center
                       justify-between
                       gap-4
                       border-b
                       border-zinc-200
                       bg-zinc-50
                       px-6 py-4">
                <div>
                    <h2 class="font-bold
                               text-zinc-900">
                        @if ($editingCategoryId !== null)
                            Edit Document Category
                        @else
                            Create Document Category
                        @endif
                    </h2>

                    <p class="mt-1
                               text-xs
                               text-zinc-500">
                        The category slug is generated
                        automatically from the category name.
                    </p>
                </div>

                <button type="button" wire:click="closeForm"
                    class="rounded-lg
                           px-3 py-2
                           text-sm font-semibold
                           text-zinc-500
                           transition
                           hover:bg-zinc-200
                           hover:text-zinc-800">
                    Close
                </button>
            </div>


            <div class="grid gap-5
                       p-6
                       lg:grid-cols-2">

                {{-- NAME --}}

                <div>
                    <label for="document-category-name"
                        class="mb-2
                               block
                               text-sm font-semibold
                               text-zinc-800">
                        Category Name
                    </label>

                    <input id="document-category-name" type="text" maxlength="150" wire:model="name"
                        placeholder="Example: Annual Reports"
                        class="w-full
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-4 py-3
                               text-sm
                               text-zinc-900">

                    @error('name')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- SORT ORDER --}}

                <div>
                    <label for="document-category-sort-order"
                        class="mb-2
                               block
                               text-sm font-semibold
                               text-zinc-800">
                        Sort Order
                    </label>

                    <input id="document-category-sort-order" type="number" min="0" max="65535"
                        wire:model="sortOrder"
                        class="w-full
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-4 py-3
                               text-sm
                               text-zinc-900">

                    <p class="mt-2
                               text-xs
                               text-zinc-500">
                        Lower numbers appear first in
                        document category selections.
                    </p>

                    @error('sortOrder')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- DESCRIPTION --}}

                <div class="lg:col-span-2">
                    <label for="document-category-description"
                        class="mb-2
                               block
                               text-sm font-semibold
                               text-zinc-800">
                        Description
                    </label>

                    <textarea id="document-category-description" rows="4" maxlength="2000" wire:model="description"
                        placeholder="Optional short description..."
                        class="w-full
                               resize-y
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-4 py-3
                               text-sm leading-6
                               text-zinc-900"></textarea>

                    <div
                        class="mt-1
                               text-right
                               text-xs
                               text-zinc-400">
                        {{ mb_strlen($description) }}/2000
                    </div>

                    @error('description')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- ACTIVE --}}

                <div class="lg:col-span-2">
                    <label
                        class="flex
                               cursor-pointer
                               items-start
                               gap-3
                               rounded-xl
                               border border-zinc-200
                               bg-zinc-50
                               p-4">
                        <input type="checkbox" wire:model="isActive"
                            class="mt-1
                                   h-4 w-4
                                   rounded
                                   border-zinc-300">

                        <span>
                            <span
                                class="block
                                       text-sm font-bold
                                       text-zinc-800">
                                Active Category
                            </span>

                            <span
                                class="mt-1
                                       block
                                       text-xs leading-5
                                       text-zinc-500">
                                Active categories are available when
                                creating or editing documents.
                                Inactive categories remain stored but
                                cannot be selected for new assignments.
                            </span>
                        </span>
                    </label>

                    @error('isActive')
                        <p
                            class="mt-2
                                   text-sm font-medium
                                   text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>


            {{-- FORM ACTIONS --}}

            <div
                class="flex justify-end
                       gap-3
                       border-t
                       border-zinc-200
                       bg-zinc-50
                       px-6 py-4">
                <button type="button" wire:click="closeForm"
                    class="rounded-xl
                           border border-zinc-300
                           bg-white
                           px-5 py-3
                           text-sm font-bold
                           text-zinc-700
                           transition
                           hover:bg-zinc-100">
                    Cancel
                </button>

                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="rounded-xl
                           bg-emerald-700
                           px-5 py-3
                           text-sm font-bold
                           text-white
                           transition
                           hover:bg-emerald-800
                           disabled:cursor-not-allowed
                           disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">
                        @if ($editingCategoryId !== null)
                            Save Changes
                        @else
                            Create Category
                        @endif
                    </span>

                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    @endif


    {{-- =====================================================
         SEARCH
    ====================================================== --}}

    <section
        class="rounded-2xl
               border border-zinc-200
               bg-white
               p-5
               shadow-sm">
        <div class="flex flex-col
                   gap-3
                   sm:flex-row">
            <div class="flex-1">
                <label for="document-category-search" class="sr-only">
                    Search document categories
                </label>

                <input id="document-category-search" type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search name, slug or description..."
                    class="w-full
                           rounded-xl
                           border border-zinc-300
                           bg-white
                           px-4 py-3
                           text-sm
                           text-zinc-900">
            </div>

            @if (trim($search) !== '')
                <button type="button" wire:click="clearSearch"
                    class="rounded-xl
                           border border-zinc-300
                           bg-white
                           px-4 py-3
                           text-sm font-semibold
                           text-zinc-700
                           transition
                           hover:bg-zinc-100">
                    Clear
                </button>
            @endif
        </div>
    </section>


    {{-- =====================================================
         CATEGORY TABLE
    ====================================================== --}}

    <section
        class="overflow-hidden
               rounded-2xl
               border border-zinc-200
               bg-white
               shadow-sm">
        <div
            class="flex items-center
                   justify-between
                   border-b
                   border-zinc-200
                   bg-zinc-50
                   px-6 py-4">
            <div>
                <h2 class="font-bold
                           text-zinc-900">
                    Categories
                </h2>

                <p class="mt-1
                           text-xs
                           text-zinc-500">
                    {{ $categories->count() }}
                    {{ $categories->count() === 1 ? 'category' : 'categories' }}
                </p>
            </div>
        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full
                       divide-y
                       divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>

                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Category
                        </th>

                        <th
                            class="px-6 py-3
                                   text-left
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Slug
                        </th>

                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Documents
                        </th>

                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Order
                        </th>

                        <th
                            class="px-6 py-3
                                   text-center
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Status
                        </th>

                        <th
                            class="px-6 py-3
                                   text-right
                                   text-xs font-bold
                                   uppercase tracking-wide
                                   text-zinc-500">
                            Actions
                        </th>

                    </tr>
                </thead>


                <tbody class="divide-y
                           divide-zinc-100
                           bg-white">

                    @forelse ($categories as $category)
                        <tr wire:key="document-category-{{ $category->id }}"
                            class="transition
                                   hover:bg-zinc-50">

                            {{-- CATEGORY --}}

                            <td class="px-6 py-4">
                                <div class="font-semibold
                                           text-zinc-900">
                                    {{ $category->name }}
                                </div>

                                @if ($category->description)
                                    <p
                                        class="mt-1
                                               max-w-md
                                               truncate
                                               text-xs
                                               text-zinc-500">
                                        {{ $category->description }}
                                    </p>
                                @endif
                            </td>


                            {{-- SLUG --}}

                            <td class="px-6 py-4">
                                <code
                                    class="rounded-lg
                                           bg-zinc-100
                                           px-2 py-1
                                           text-xs
                                           text-zinc-700">
                                    {{ $category->slug }}
                                </code>
                            </td>


                            {{-- DOCUMENT COUNT --}}

                            <td
                                class="px-6 py-4
                                       text-center
                                       text-sm
                                       text-zinc-600">
                                {{ $category->documents_count ?? 0 }}
                            </td>


                            {{-- ORDER --}}

                            <td
                                class="px-6 py-4
                                       text-center
                                       text-sm font-semibold
                                       text-zinc-700">
                                {{ $category->sort_order }}
                            </td>


                            {{-- STATUS --}}

                            <td class="px-6 py-4
                                       text-center">
                                @if ($category->is_active)
                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-emerald-100
                                               px-2.5 py-1
                                               text-xs font-bold
                                               text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex
                                               rounded-full
                                               bg-zinc-200
                                               px-2.5 py-1
                                               text-xs font-bold
                                               text-zinc-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>


                            {{-- ACTIONS --}}

                            <td class="px-6 py-4
                                       text-right">
                                <div
                                    class="flex
                                           flex-wrap
                                           justify-end
                                           gap-2">
                                    <button type="button" wire:click="openEdit({{ $category->id }})"
                                        wire:loading.attr="disabled" wire:target="openEdit({{ $category->id }})"
                                        class="rounded-lg
                                               border border-zinc-300
                                               bg-white
                                               px-3 py-2
                                               text-xs font-bold
                                               text-zinc-700
                                               transition
                                               hover:bg-zinc-100
                                               disabled:opacity-50">
                                        Edit
                                    </button>

                                    <button type="button" wire:click="toggleActive({{ $category->id }})"
                                        wire:confirm="{{ $category->is_active
                                            ? 'Deactivate this document category? Existing documents will keep their category, but it will no longer be available for new selections.'
                                            : 'Activate this document category?' }}"
                                        wire:loading.attr="disabled" wire:target="toggleActive({{ $category->id }})"
                                        class="rounded-lg
                                               px-3 py-2
                                               text-xs font-bold
                                               transition
                                               disabled:cursor-not-allowed
                                               disabled:opacity-50
                                               {{ $category->is_active
                                                   ? 'bg-amber-100 text-amber-800 hover:bg-amber-200'
                                                   : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-6 py-16
                                       text-center">
                                <div
                                    class="text-sm font-semibold
                                           text-zinc-600">
                                    No document categories found.
                                </div>

                                <p
                                    class="mt-1
                                           text-xs
                                           text-zinc-400">
                                    Create a new category or
                                    change your search criteria.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </section>

</div>

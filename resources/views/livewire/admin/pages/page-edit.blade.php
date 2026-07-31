<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-zinc-950">
                    Edit Page
                </h1>

                <span
                    class="inline-flex rounded-full bg-zinc-100
                           px-2.5 py-1 text-xs font-semibold
                           text-zinc-700">
                    Draft
                </span>
            </div>

            <p class="mt-1 text-sm text-zinc-600">
                Update the draft page content and URL settings.
            </p>
        </div>

        <a href="{{ route('admin.pages.index') }}" wire:navigate
            class="inline-flex items-center justify-center
                   rounded-xl border border-zinc-300 bg-white
                   px-4 py-2.5 text-sm font-semibold
                   text-zinc-700 shadow-sm hover:bg-zinc-50">
            Back to Pages
        </a>
    </div>

    @if (session('status'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 px-4 py-3 text-sm
                   font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-zinc-200
                   bg-white p-6 shadow-sm">
            <div class="space-y-5">
                <div>
                    <label for="page-title"
                        class="mb-2 block text-sm font-semibold
                               text-zinc-800">
                        Page title
                    </label>

                    <input id="page-title" type="text" wire:model.live.debounce.300ms="title" autocomplete="off"
                        class="w-full rounded-xl border border-zinc-300
                               bg-white px-4 py-3 text-sm outline-none
                               focus:border-emerald-500
                               focus:ring-4 focus:ring-emerald-500/10">

                    @error('title')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <div
                        class="mb-2 flex flex-col gap-2
                               sm:flex-row sm:items-center
                               sm:justify-between">
                        <label for="page-slug"
                            class="block text-sm font-semibold
                                   text-zinc-800">
                            URL slug
                        </label>

                        <button type="button" wire:click="regenerateSlug"
                            class="text-left text-xs font-semibold
                                   text-emerald-700
                                   hover:text-emerald-800">
                            Regenerate from title
                        </button>
                    </div>

                    <div
                        class="flex overflow-hidden rounded-xl
                               border border-zinc-300
                               focus-within:border-emerald-500
                               focus-within:ring-4
                               focus-within:ring-emerald-500/10">
                        <span
                            class="flex items-center border-r
                                   border-zinc-300 bg-zinc-50
                                   px-3 text-sm text-zinc-500">
                            /
                        </span>

                        <input id="page-slug" type="text" wire:model.live.debounce.300ms="slug" autocomplete="off"
                            class="min-w-0 flex-1 border-0
                                   bg-white px-4 py-3 text-sm
                                   outline-none focus:ring-0">
                    </div>

                    <p class="mt-2 text-xs text-amber-700">
                        Changing the slug changes the page URL. Existing
                        external links may stop working unless a redirect
                        is configured later.
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="page-excerpt"
                        class="mb-2 block text-sm font-semibold
                               text-zinc-800">
                        Short description
                    </label>

                    <textarea id="page-excerpt" wire:model="excerpt" rows="3" maxlength="500"
                        class="w-full resize-y rounded-xl
                               border border-zinc-300 bg-white
                               px-4 py-3 text-sm outline-none
                               focus:border-emerald-500
                               focus:ring-4 focus:ring-emerald-500/10"></textarea>

                    <div class="mt-2 flex justify-between gap-3">
                        @error('excerpt')
                            <p class="text-sm font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @else
                            <span></span>
                        @enderror

                        <p class="text-xs text-zinc-500">
                            {{ mb_strlen($excerpt) }}/500
                        </p>
                    </div>
                </div>

                <div>
                    <label for="page-content"
                        class="mb-2 block text-sm font-semibold
                               text-zinc-800">
                        Page content
                    </label>

                    <x-forms.rich-text-editor id="page-edit-content" model="content" :value="$content"
                        label="Page content" />
                    <p class="mt-2 text-xs text-zinc-500">
                        Plain text only. HTML formatting is not allowed
                        in the short description.
                    </p>

                    @error('content')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-amber-200
                   bg-amber-50 p-5">
            <h2 class="text-sm font-semibold text-amber-950">
                Draft editing safeguard
            </h2>

            <p class="mt-1 text-sm leading-6 text-amber-800">
                Only Draft pages may be edited. Submitted, Approved,
                Published and Archived pages must first be returned to Draft
                through the content workflow.
            </p>
        </section>

        <div
            class="flex flex-col-reverse gap-3
                   border-t border-zinc-200 pt-6
                   sm:flex-row sm:items-center
                   sm:justify-end">
            <a href="{{ route('admin.pages.index') }}" wire:navigate
                class="inline-flex items-center justify-center
                       rounded-xl border border-zinc-300
                       bg-white px-5 py-3 text-sm font-semibold
                       text-zinc-700 hover:bg-zinc-50">
                Cancel
            </a>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center justify-center
                       rounded-xl bg-emerald-700 px-5 py-3
                       text-sm font-semibold text-white
                       shadow-sm hover:bg-emerald-800
                       disabled:cursor-not-allowed
                       disabled:opacity-60">
                <span wire:loading.remove wire:target="save">
                    Save Changes
                </span>

                <span wire:loading wire:target="save">
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>

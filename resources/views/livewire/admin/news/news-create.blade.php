<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.news.index') }}" wire:navigate
                class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                ← Back to News
            </a>

            <h1 class="mt-2 text-2xl font-bold text-zinc-950">
                Create News Article
            </h1>

            <p class="mt-1 text-sm text-zinc-600">
                Create English, Sinhala or Tamil news manually. No automatic translation is performed.
            </p>
        </div>

        @if ($canManageCategories ?? auth()->user()?->can('news.categories.manage'))
            <a href="{{ route('admin.news.categories.index') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                Manage Categories
            </a>
        @endif
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="text-sm font-bold text-red-800">
                Please correct the highlighted fields.
            </p>

            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-5">
                <h2 class="font-bold text-zinc-950">Article details</h2>
                <p class="mt-1 text-sm text-zinc-600">Language, title, category and public URL.</p>
            </div>

            <div class="grid gap-5 p-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <p class="text-sm font-semibold text-zinc-800">Language</p>

                    @if ($translationSourceNewsId !== null)
                        @php
                            $selectedLocale = collect($locales)->first(
                                fn(\App\Enums\NewsLocale $option): bool => $option->value === $locale,
                            );
                        @endphp

                        <div class="mt-2 rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-blue-800">
                                    {{ $selectedLocale?->nativeLabel() ?? strtoupper($locale) }}
                                </span>

                                <span class="text-sm font-semibold text-blue-950">
                                    Manual translation of “{{ $translationSourceTitle }}”
                                </span>
                            </div>

                            <p class="mt-2 text-xs leading-5 text-blue-800">
                                Source title, summary and body are not copied. Enter the approved translation manually.
                            </p>
                        </div>
                    @else
                        <select wire:model.live="locale"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            @foreach ($locales as $localeOption)
                                <option value="{{ $localeOption->value }}">
                                    {{ $localeOption->label() }} — {{ $localeOption->nativeLabel() }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    @error('locale')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="news-title" class="mb-2 block text-sm font-semibold text-zinc-800">
                        News title
                    </label>

                    <input id="news-title" type="text" wire:model.live.debounce.300ms="title"
                        placeholder="Example: Annual Training Programme Begins"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">

                    @error('title')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="news-category" class="mb-2 block text-sm font-semibold text-zinc-800">
                        Category
                    </label>

                    <select id="news-category" wire:model="categoryId"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        <option value="">Select category</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    @error('categoryId')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="news-slug" class="text-sm font-semibold text-zinc-800">URL slug</label>

                        <button type="button" wire:click="regenerateSlug"
                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                            Generate from title
                        </button>
                    </div>

                    <div
                        class="flex overflow-hidden rounded-xl border border-zinc-300 focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10">
                        <span class="flex items-center border-r border-zinc-300 bg-zinc-50 px-3 text-sm text-zinc-500">
                            /{{ $locale }}/news/
                        </span>

                        <input id="news-slug" type="text" wire:model.live.debounce.300ms="slug"
                            placeholder="annual-training-programme"
                            class="min-w-0 flex-1 border-0 bg-white px-4 py-3 text-sm outline-none focus:ring-0">
                    </div>

                    <p class="mt-2 text-xs text-zinc-500">
                        The same slug may be used in EN, SI and TA because uniqueness is enforced per language.
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="news-summary" class="mb-2 block text-sm font-semibold text-zinc-800">
                        Summary
                    </label>

                    <textarea id="news-summary" wire:model="summary" rows="3" maxlength="2000"
                        placeholder="Short summary shown in news listings."
                        class="w-full resize-y rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"></textarea>

                    @error('summary')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="font-bold text-zinc-950">News content</h2>
                        <p class="mt-1 text-sm text-zinc-600">
                            Use the Word-like Visual Editor or switch to HTML + Tailwind for advanced layouts.
                        </p>
                    </div>

                    <span class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                        Visual + Code
                    </span>
                </div>
            </div>

            <div class="p-6">
                <x-forms.page-content-editor id="news-content" model="content" mode-model="editorMode" :value="$content"
                    :editor-mode="$editorMode" />

                @error('content')
                    <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>


        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-5">
                <h2 class="font-bold text-zinc-950">Article images</h2>
                <p class="mt-1 text-sm text-zinc-600">
                    Upload multiple images for this article. They are shown after the Body in a four-column grid on
                    desktop.
                </p>
            </div>

            <div class="p-6">
                @if (auth()->user()?->can('media.upload') && auth()->user()?->can('news.update'))
                    <label for="news-gallery-uploads" class="mb-2 block text-sm font-semibold text-zinc-800">
                        Upload images
                    </label>

                    <input id="news-gallery-uploads" type="file" wire:model="galleryUploads" multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zinc-700 hover:file:bg-zinc-200">

                    <p class="mt-2 text-xs leading-5 text-zinc-500">
                        Select up to 20 images at once. Maximum 8 MB per image. Selected files are uploaded to the
                        Public Media Library when the draft is saved.
                    </p>

                    <div wire:loading wire:target="galleryUploads" class="mt-3 text-sm font-semibold text-blue-700">
                        Preparing selected images...
                    </div>

                    @if ($galleryUploads !== [])
                        <div class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-semibold text-zinc-900">
                                Selected images ({{ count($galleryUploads) }})
                            </p>

                            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($galleryUploads as $upload)
                                    <figure class="overflow-hidden rounded-lg border border-zinc-200 bg-white">
                                        <div class="aspect-[4/3] overflow-hidden bg-zinc-100">
                                            <img src="{{ $upload->temporaryUrl() }}" alt="Selected news image"
                                                class="h-full w-full object-cover">
                                        </div>

                                        <figcaption class="truncate px-3 py-2 text-xs font-medium text-zinc-600">
                                            {{ $upload->getClientOriginalName() }}
                                        </figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @error('galleryUploads')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror

                    @error('galleryUploads.*')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Multiple image upload requires both News Update and Media Upload permissions.
                    </div>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-5">
                <h2 class="font-bold text-zinc-950">Publication options</h2>
            </div>

            <div class="grid gap-5 p-6">
                <div>
                    <label for="published-at" class="mb-2 block text-sm font-semibold text-zinc-800">
                        Publication date/time
                    </label>

                    <input id="published-at" type="datetime-local" wire:model="publishedAt"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">

                    <p class="mt-2 text-xs text-zinc-500">
                        Optional. If blank, Publish uses the current time. A future value schedules public visibility.
                    </p>

                    @error('publishedAt')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label
                    class="lg:col-span-2 flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                    <input type="checkbox" wire:model="isFeatured"
                        class="mt-0.5 h-4 w-4 rounded border-zinc-300 text-emerald-700 focus:ring-emerald-500">

                    <span>
                        <span class="block text-sm font-semibold text-zinc-900">Featured news article</span>
                        <span class="mt-1 block text-xs leading-5 text-zinc-500">Featured articles may be prioritised
                            on the public news listing.</span>
                    </span>
                </label>
            </div>
        </section>

        <div
            class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('admin.news.index') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                Cancel
            </a>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save Draft</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>

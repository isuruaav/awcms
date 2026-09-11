<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.news.index') }}" wire:navigate
                class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                ← Back to News
            </a>

            <h1 class="mt-2 text-2xl font-bold text-zinc-950">
                Edit News Article
            </h1>

            <p class="mt-1 text-sm text-zinc-600">
                Edit the draft article. Published articles must be unpublished before editing.
            </p>
        </div>

        <a href="{{ route('admin.news.preview', $news) }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-100">
            Preview
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="text-sm font-bold text-red-800">Please correct the highlighted fields.</p>

            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-zinc-500">Language versions</p>

                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($locales as $localeOption)
                        @php
                            $translation = $news->translationVersions->first(
                                fn(\App\Models\News $version): bool => $version->locale === $localeOption,
                            );
                        @endphp

                        @if ($translation instanceof \App\Models\News)
                            <a href="{{ $translation->status === \App\Enums\NewsStatus::Draft
                                ? route('admin.news.edit', $translation)
                                : route('admin.news.preview', $translation) }}"
                                @if ($translation->status !== \App\Enums\NewsStatus::Draft) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                @class([
                                    'rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-100 text-emerald-800' => $translation->id === $news->id,
                                    'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' =>
                                        $translation->id !== $news->id,
                                ])>
                                {{ $localeOption->nativeLabel() }} ✓
                            </a>
                        @else
                            @can('news.create')
                                <a href="{{ route('admin.news.translations.create', ['newsId' => $news->id, 'locale' => $localeOption->value]) }}"
                                    wire:navigate
                                    class="rounded-full border border-dashed border-zinc-300 px-3 py-1.5 text-xs font-bold text-zinc-500 hover:border-emerald-300 hover:text-emerald-700">
                                    + {{ $localeOption->nativeLabel() }}
                                </a>
                            @endcan
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-800">
                Current: <span class="font-bold">{{ $news->locale->nativeLabel() }}</span>
            </div>
        </div>
    </section>

    @if (!$editable)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="text-sm font-bold text-amber-900">Editing locked</p>
            <p class="mt-1 text-sm leading-6 text-amber-800">
                This article is currently <span class="font-semibold">{{ $status->label() }}</span>.
                Use Unpublish from the News list to return it to Draft before editing.
            </p>
        </div>
    @else
        <form wire:submit="save" class="space-y-6">
            <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-200 px-6 py-5">
                    <h2 class="font-bold text-zinc-950">Article details</h2>
                </div>

                <div class="grid gap-5 p-6 lg:grid-cols-2">
                    <div>
                        <label for="news-title" class="mb-2 block text-sm font-semibold text-zinc-800">News
                            title</label>
                        <input id="news-title" type="text" wire:model.live.debounce.300ms="title"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        @error('title')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="news-category"
                            class="mb-2 block text-sm font-semibold text-zinc-800">Category</label>
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
                                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Generate from
                                title</button>
                        </div>

                        <div
                            class="flex overflow-hidden rounded-xl border border-zinc-300 focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10">
                            <span
                                class="flex items-center border-r border-zinc-300 bg-zinc-50 px-3 text-sm text-zinc-500">
                                /{{ $news->locale->value }}/news/
                            </span>
                            <input id="news-slug" type="text" wire:model.live.debounce.300ms="slug"
                                class="min-w-0 flex-1 border-0 bg-white px-4 py-3 text-sm outline-none focus:ring-0">
                        </div>
                        @error('slug')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="news-summary" class="mb-2 block text-sm font-semibold text-zinc-800">Summary</label>
                        <textarea id="news-summary" wire:model="summary" rows="3" maxlength="2000"
                            class="w-full resize-y rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"></textarea>
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
                            <p class="mt-1 text-sm text-zinc-600">Visual Editor and HTML + Tailwind use the same saved
                                HTML.</p>
                        </div>
                        <span
                            class="w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">Visual
                            + Code</span>
                    </div>
                </div>

                <div class="p-6">
                    <x-forms.page-content-editor id="news-content" model="content" mode-model="editorMode"
                        :value="$content" :editor-mode="$editorMode" />
                    @error('content')
                        <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>


            <section class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-200 px-6 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-bold text-zinc-950">Article images</h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                These images appear after the Body. Desktop layout shows four images per row.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700">
                            {{ $news->images->count() }} image{{ $news->images->count() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    @if ($news->images->isNotEmpty())
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($news->images as $newsImage)
                                @php
                                    $newsImageUrl = \App\Http\Controllers\PublicNewsController::imageUrl(
                                        $newsImage->media,
                                    );
                                @endphp

                                <article class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
                                    <div class="aspect-[4/3] overflow-hidden bg-zinc-100">
                                        @if ($newsImageUrl)
                                            <img src="{{ $newsImageUrl }}"
                                                alt="{{ $newsImage->media?->alt_text ?: $news->title }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <div
                                                class="flex h-full items-center justify-center px-4 text-center text-xs font-semibold text-zinc-400">
                                                Preview unavailable
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-3">
                                        <p class="truncate text-xs font-semibold text-zinc-700">
                                            {{ $newsImage->media?->title ?: $newsImage->media?->original_name ?: 'News image' }}
                                        </p>

                                        <label
                                            class="mt-3 flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold transition {{ (string) $news->featured_image_id === (string) $newsImage->media_asset_id ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-emerald-200 hover:bg-emerald-50' }}">
                                            <input type="radio" name="featured-news-image"
                                                value="{{ $newsImage->id }}" @checked((string) $news->featured_image_id === (string) $newsImage->media_asset_id)
                                                wire:click="selectFeaturedImage({{ $newsImage->id }})"
                                                class="h-4 w-4 border-zinc-300 text-emerald-700 focus:ring-emerald-500">
                                            <span>{{ (string) $news->featured_image_id === (string) $newsImage->media_asset_id ? 'Featured image' : 'Set as featured' }}</span>
                                        </label>

                                        <button type="button" wire:click="removeGalleryImage({{ $newsImage->id }})"
                                            wire:confirm="Remove this image from the news article? The Media Library asset will be kept."
                                            wire:loading.attr="disabled"
                                            wire:target="removeGalleryImage({{ $newsImage->id }})"
                                            class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:opacity-60">
                                            Remove
                                        </button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 px-5 py-8 text-center">
                            <p class="text-sm font-semibold text-zinc-700">No article images uploaded yet.</p>
                        </div>
                    @endif

                    @if ($news->featured_image_id !== null)
                        <div
                            class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-zinc-600">The selected image is used on news cards and social
                                previews.</p>
                            <button type="button" wire:click="clearFeaturedImage"
                                wire:confirm="Clear the featured image from this article?"
                                class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                                Clear featured image
                            </button>
                        </div>
                    @endif

                    @if (auth()->user()?->can('media.upload') && auth()->user()?->can('news.update'))
                        <div class="border-t border-zinc-200 pt-5">
                            <label for="news-gallery-uploads" class="mb-2 block text-sm font-semibold text-zinc-800">
                                Add multiple images
                            </label>

                            <input id="news-gallery-uploads" type="file" wire:model="galleryUploads" multiple
                                accept="image/jpeg,image/png,image/webp"
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zinc-700 hover:file:bg-zinc-200">

                            <p class="mt-2 text-xs leading-5 text-zinc-500">
                                Select up to 20 images at once, maximum 8 MB each. Uploaded files are stored through the
                                existing secure Media Library pipeline.
                            </p>

                            <div wire:loading wire:target="galleryUploads"
                                class="mt-3 text-sm font-semibold text-blue-700">
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
                                                    <img src="{{ $upload->temporaryUrl() }}"
                                                        alt="Selected news image" class="h-full w-full object-cover">
                                                </div>

                                                <figcaption
                                                    class="truncate px-3 py-2 text-xs font-medium text-zinc-600">
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

                            <button type="button" wire:click="uploadGalleryImages" wire:loading.attr="disabled"
                                wire:target="uploadGalleryImages"
                                class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                                <span wire:loading.remove wire:target="uploadGalleryImages">Upload Images</span>
                                <span wire:loading wire:target="uploadGalleryImages">Uploading...</span>
                            </button>
                        </div>
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
                        <label for="published-at" class="mb-2 block text-sm font-semibold text-zinc-800">Publication
                            date/time</label>
                        <input id="published-at" type="datetime-local" wire:model="publishedAt"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        <p class="mt-2 text-xs text-zinc-500">Blank uses the current time when Publish is clicked.
                            Future dates schedule public visibility.</p>
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
                            <span class="mt-1 block text-xs leading-5 text-zinc-500">Featured articles may be
                                prioritised in public news listings.</span>
                        </span>
                    </label>
                </div>
            </section>

            <div
                class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('admin.news.index') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Cancel</a>

                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save Draft</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    @endif
</div>

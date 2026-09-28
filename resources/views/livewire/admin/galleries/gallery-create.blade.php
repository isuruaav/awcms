<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-950">Create Gallery</h1>
            <p class="mt-1 text-sm text-zinc-500">Add a title and select your photographs.</p>
        </div>
        <a href="{{ route('admin.galleries.index') }}" wire:navigate class="text-sm font-semibold text-emerald-700">← All Galleries</a>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label for="gallery-title" class="mb-2 block text-sm font-semibold text-zinc-800">English title</label>
            <input id="gallery-title" type="text" wire:model="title" maxlength="255" required
                placeholder="Enter the event or gallery title"
                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        </div>


            <div>
                <label for="gallery-title-si" class="mb-2 block text-sm font-semibold">සිංහල මාතෘකාව</label>
                <input id="gallery-title-si" wire:model="titleSi" type="text" lang="si" maxlength="255" 
                    class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm disabled:bg-zinc-100">
                <p class="mt-1 text-xs text-zinc-500">Optional. Leave blank to display the English title on the Sinhala page.</p>
                @error('titleSi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        <div>
            <label for="gallery-images" class="mb-2 block text-sm font-semibold text-zinc-800">Photographs</label>
            <div class="rounded-xl border-2 border-dashed border-emerald-200 bg-emerald-50 p-6">
                <input id="gallery-images" type="file" wire:model="images" multiple accept="image/jpeg,image/png,image/webp"
                    wire:loading.attr="disabled" wire:target="save,images"
                    class="block w-full text-sm text-zinc-700">
                <p class="mt-3 text-xs leading-5 text-zinc-600">Select multiple images together. JPG, PNG or WebP; up to 20 images, 8 MB each. The first image becomes the cover.</p>
                <p class="mt-1 text-xs text-zinc-500">Selecting files again replaces this selection.</p>
            </div>
            <p wire:loading wire:target="images" role="status" class="mt-3 text-sm text-emerald-700">Uploading photographs…</p>
        </div>

        @if ($images !== [])
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($images as $index => $image)
                    <div wire:key="gallery-upload-{{ $image->getFilename() }}" class="overflow-hidden rounded-xl border border-zinc-200">
                        @if (in_array(strtolower($image->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true) && $image->isPreviewable())
                            <img src="{{ $image->temporaryUrl() }}" alt="Selected photograph {{ $index + 1 }}"
                                class="aspect-square w-full object-cover">
                        @endif
                        <div class="space-y-2 p-3">
                            <p class="truncate text-xs text-zinc-600">{{ $image->getClientOriginalName() }}</p>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-emerald-700">{{ $index === 0 ? 'Cover' : 'Photo '.($index + 1) }}</span>
                                <button type="button" wire:click="removeImage({{ $index }})" wire:loading.attr="disabled"
                                    class="text-xs font-semibold text-red-600" aria-label="Remove photograph {{ $index + 1 }}">Remove</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-zinc-200 pt-5">
            <p class="text-xs text-zinc-500">Saved as a draft. You can review and publish next.</p>
            <button type="submit" wire:loading.attr="disabled" wire:target="images,save,removeImage"
                class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white disabled:opacity-50">
                <span wire:loading.remove wire:target="save">Create Gallery</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>

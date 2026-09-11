<div class="space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            Site Management
        </p>

        <h1 class="mt-1 text-2xl font-black text-zinc-950">
            School Leadership
        </h1>

        <p class="mt-1 text-sm text-zinc-500">
            Manage the three fixed leadership cards shown on the homepage.
        </p>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-bold">Please correct the following errors:</p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <div>
                    <h2 class="font-bold text-zinc-900">Fixed leadership cards</h2>
                    <p class="mt-1 text-xs text-zinc-500">These roles cannot be added or deleted.</p>
                </div>

                <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-bold text-zinc-700">
                    {{ $schoolLeaders->count() }} roles
                </span>
            </div>

            <div class="divide-y divide-zinc-200">
                @forelse ($schoolLeaders as $leader)
                    <article wire:key="school-leader-{{ $leader->id }}" class="p-5 sm:p-6">
                        <div class="flex flex-col gap-5 sm:flex-row">
                            <div
                                class="flex h-28 w-full shrink-0 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 sm:w-44">
                                @if ($leader->image)
                                    <div class="px-4 text-center">
                                        <span
                                            class="mx-auto flex size-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                                <circle cx="8.5" cy="9" r="1.5" />
                                                <path d="m4 17 5-5 4 4 2-2 5 5" />
                                            </svg>
                                        </span>
                                        <p class="mt-2 line-clamp-2 text-xs font-semibold text-zinc-600">
                                            {{ $leader->image->title ?: $leader->image->original_name }}
                                        </p>
                                    </div>
                                @else
                                    <div class="text-center text-zinc-400">
                                        <svg class="mx-auto size-7" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path
                                                d="m3 3 18 18M10.5 6H19a2 2 0 0 1 2 2v9.5M6 6H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h13" />
                                            <path d="m3 17 4-4 3 3 1.5-1.5" />
                                        </svg>
                                        <p class="mt-1 text-xs font-semibold">No image</p>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[11px] font-bold',
                                        'bg-emerald-100 text-emerald-800' => $leader->is_active,
                                        'bg-zinc-200 text-zinc-600' => !$leader->is_active,
                                    ])>
                                        {{ $leader->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">
                                        Order {{ $leader->sort_order }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-base font-black text-zinc-950">
                                    {{ $leader->title_en }}
                                </h3>
                                <p class="mt-1 text-sm font-semibold text-zinc-600" lang="si">
                                    {{ $leader->title_si ?: 'Sinhala title not added' }}
                                </p>
                                <p class="mt-2 text-sm text-zinc-500">
                                    {{ $leader->name_en ?: 'English name not added' }}
                                    <span class="text-zinc-300">/</span>
                                    {{ $leader->name_si ?: 'Sinhala name not added' }}
                                </p>

                                <button type="button" wire:click="edit({{ $leader->id }})"
                                    class="mt-5 rounded-lg bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800">
                                    Edit card
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <p class="font-bold text-zinc-700">The fixed leadership roles are not seeded yet.</p>
                        <p class="mt-1 text-sm text-zinc-500">Run the database seeders to create the three roles.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside>
            <form wire:submit="save"
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm xl:sticky xl:top-6">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                    <h2 class="font-bold text-zinc-900">Edit leadership card</h2>
                    <p class="mt-1 text-xs text-zinc-500">The role itself is fixed. Update its content below.</p>
                </div>

                <div class="space-y-5 p-6">
                    @php($selectedLeader = $schoolLeaders->firstWhere('id', $editingId))
                    @if ($selectedLeader)
                        <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Fixed role</p>
                            <p class="mt-1 font-black text-blue-950">{{ $selectedLeader->title_en }}</p>
                            <p class="mt-1 text-sm font-semibold text-blue-800" lang="si">
                                {{ $selectedLeader->title_si }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-black text-zinc-900">Profile image</p>
                        <p class="mt-1 text-xs text-zinc-500">Select a public Media Library image or upload a new one.
                        </p>
                    </div>

                    <div class="rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                        <label for="school-leader-image" class="block cursor-pointer text-center">
                            @if ($newImage instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                <img src="{{ $newImage->temporaryUrl() }}" alt="New leadership image preview"
                                    class="mx-auto h-44 w-full rounded-lg object-cover">
                                <span class="mt-3 block text-sm font-bold text-emerald-800">Change selected image</span>
                            @else
                                <span
                                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" aria-hidden="true">
                                        <path d="M12 16V4" />
                                        <path d="m7 9 5-5 5 5" />
                                        <path d="M5 20h14a2 2 0 0 0 2-2v-3M3 15v3a2 2 0 0 0 2 2" />
                                    </svg>
                                </span>
                                <span class="mt-3 block text-sm font-black text-zinc-900">Upload New Image</span>
                                <span class="mt-1 block text-xs text-zinc-500">JPG, PNG or WebP — maximum 20 MB</span>
                            @endif
                            <input id="school-leader-image" wire:model="newImage" type="file"
                                accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>

                        @if ($newImage)
                            <button type="button" wire:click="clearNewImage"
                                class="mt-3 w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                                Remove New Image
                            </button>
                        @endif
                        @error('newImage')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="school-leader-library-image"
                            class="mb-1.5 block text-sm font-bold text-zinc-800">Media Library Image</label>
                        <select id="school-leader-library-image" wire:model="imageMediaId" @disabled($newImage)
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 disabled:cursor-not-allowed disabled:bg-zinc-100 disabled:text-zinc-400">
                            <option value="">No existing image selected</option>
                            @foreach ($mediaAssets as $asset)
                                <option value="{{ $asset->id }}">#{{ $asset->id }} —
                                    {{ $asset->title ?: $asset->original_name }}</option>
                            @endforeach
                        </select>
                        @error('imageMediaId')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div>
                            <label for="school-leader-name-en"
                                class="mb-1 block text-xs font-bold text-zinc-700">English name</label>
                            <input id="school-leader-name-en" wire:model="nameEn" type="text" maxlength="180"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                            @error('nameEn')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="school-leader-name-si"
                                class="mb-1 block text-xs font-bold text-zinc-700">සිංහල නම</label>
                            <input id="school-leader-name-si" wire:model="nameSi" type="text" maxlength="220"
                                lang="si"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                            @error('nameSi')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div>
                            <label for="school-leader-title-en"
                                class="mb-1 block text-xs font-bold text-zinc-700">English position</label>
                            <input id="school-leader-title-en" wire:model="titleEn" type="text" maxlength="120"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                            @error('titleEn')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="school-leader-title-si"
                                class="mb-1 block text-xs font-bold text-zinc-700">සිංහල තනතුර</label>
                            <input id="school-leader-title-si" wire:model="titleSi" type="text" maxlength="160"
                                lang="si"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                            @error('titleSi')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="school-leader-order" class="mb-1 block text-xs font-bold text-zinc-700">Display
                            order</label>
                        <input id="school-leader-order" wire:model="sortOrder" type="number" min="1"
                            max="255"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                        @error('sortOrder')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-4">
                        <input wire:model="isActive" type="checkbox"
                            class="size-4 rounded border-zinc-300 text-emerald-700">
                        <span>
                            <span class="block text-sm font-bold text-zinc-900">Active card</span>
                            <span class="block text-xs text-zinc-500">Show this leader on the public homepage.</span>
                        </span>
                    </label>

                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Save Changes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>
        </aside>
    </div>
</div>

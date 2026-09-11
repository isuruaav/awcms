<div class="space-y-6">
    @php($mediaUrls = app(\App\Services\MediaUrlService::class))

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">History</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950">Past Chief Instructors</h1>
            <p class="mt-1 text-sm text-zinc-500">Add and manage the former Chief Instructors shown on the public history
                page.</p>
        </div>
        <button type="button" wire:click="create"
            class="rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800">New Past Chief
            Instructor</button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}</div>
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

    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">Past Chief Instructors</h2>
                <p class="mt-1 text-xs text-zinc-500">Newest service period appears first.</p>
            </div>
            <div class="divide-y divide-zinc-200">
                @forelse ($pastChiefInstructors as $instructor)
                    <article wire:key="past-chief-instructor-{{ $instructor->id }}" class="min-w-0 p-4 sm:p-5">
                        <div class="flex min-w-0 items-start gap-4">
                            <div
                                class="flex h-32 w-24 flex-none items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 sm:w-28">
                                @if ($instructor->image)
                                    @php($imageUrl = $mediaUrls->thumbnailOrOriginal($instructor->image))
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $instructor->name_en }}"
                                            class="h-full w-full object-cover object-top">
                                    @else
                                        <span
                                            class="px-3 text-center text-xs font-semibold text-zinc-600">{{ $instructor->image->title ?: $instructor->image->original_name }}</span>
                                    @endif
                                @else
                                    <span class="text-xs font-semibold text-zinc-400">No image</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex min-w-0 items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-700">
                                            Chief Instructor</p>
                                        <h3 class="mt-1 break-words text-base font-black leading-6 text-zinc-950">
                                            {{ $instructor->name_en }}</h3>
                                    </div>
                                    <span
                                        class="flex-none rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-bold text-zinc-500">#{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <p class="mt-2 break-words text-sm font-semibold leading-6 text-zinc-600"
                                    lang="si">{{ $instructor->name_si ?: 'සිංහල නම ඇතුළත් කර නැත' }}</p>
                                <div class="mt-3 grid gap-1.5 text-xs text-zinc-500">
                                    <span class="inline-flex items-center gap-2"><i
                                            class="fa-regular fa-calendar w-4 text-center text-emerald-700"
                                            aria-hidden="true"></i><span>From: <strong
                                                class="font-bold text-zinc-700">{{ $instructor->from_date->format('d.m.Y') }}</strong></span></span>
                                    <span class="inline-flex items-center gap-2"><i
                                            class="fa-solid fa-arrow-right w-4 text-center text-emerald-700"
                                            aria-hidden="true"></i><span>To: <strong
                                                class="font-bold text-zinc-700">{{ $instructor->to_date->format('d.m.Y') }}</strong></span></span>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button type="button" wire:click="edit({{ $instructor->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"><i
                                            class="fa-solid fa-pen" aria-hidden="true"></i> Edit</button>
                                    <button type="button" wire:click="delete({{ $instructor->id }})"
                                        wire:confirm="Delete this past chief instructor?"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:border-red-300 hover:bg-red-100"><i
                                            class="fa-solid fa-trash" aria-hidden="true"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center text-sm text-zinc-500">No past chief instructors have been added.
                    </div>
                @endforelse
            </div>
        </section>

        <aside>
            <form wire:submit="save"
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm xl:sticky xl:top-6">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                    <h2 class="font-bold text-zinc-900">
                        {{ $editingId === null ? 'New Past Chief Instructor' : 'Edit Past Chief Instructor' }}</h2>
                </div>
                <div class="space-y-5 p-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div><label for="chief-name-en" class="mb-1 block text-xs font-bold text-zinc-700">English
                                name</label><input id="chief-name-en" wire:model="nameEn" type="text" maxlength="180"
                                class="w-full rounded-lg border border-zinc-300 px-3 py-2.5 text-sm">
                            @error('nameEn')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div><label for="chief-name-si" class="mb-1 block text-xs font-bold text-zinc-700">සිංහල
                                නම</label><input id="chief-name-si" wire:model="nameSi" type="text" maxlength="220"
                                lang="si" class="w-full rounded-lg border border-zinc-300 px-3 py-2.5 text-sm">
                            @error('nameSi')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label for="chief-from-date" class="mb-1 block text-xs font-bold text-zinc-700">From
                                date</label><input id="chief-from-date" wire:model="fromDate" type="date"
                                class="w-full rounded-lg border border-zinc-300 px-3 py-2.5 text-sm">
                            @error('fromDate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div><label for="chief-to-date" class="mb-1 block text-xs font-bold text-zinc-700">To
                                date</label><input id="chief-to-date" wire:model="toDate" type="date"
                                class="w-full rounded-lg border border-zinc-300 px-3 py-2.5 text-sm">
                            @error('toDate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-black text-zinc-900">Profile image</p>
                        <p class="mt-1 text-xs text-zinc-500">Select a public Media Library image or upload a new one.
                        </p>
                    </div>
                    <div class="rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                        <label for="past-chief-instructor-image" class="block cursor-pointer text-center">
                            @if ($newImage instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                <img src="{{ $newImage->temporaryUrl() }}" alt="New chief instructor image preview"
                                    class="mx-auto h-40 w-full rounded-lg object-cover"><span
                                    class="mt-3 block text-sm font-bold text-emerald-800">Change selected image</span>
                            @else
                                <span
                                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><i
                                        class="fa-solid fa-upload" aria-hidden="true"></i></span><span
                                    class="mt-3 block text-sm font-black text-zinc-900">Upload New Image</span><span
                                    class="mt-1 block text-xs text-zinc-500">JPG, PNG or WebP — maximum 20 MB</span>
                            @endif
                            <input id="past-chief-instructor-image" wire:model="newImage" type="file"
                                accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        @if ($newImage)
                            <button type="button" wire:click="clearNewImage"
                                class="mt-3 w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-600">Remove
                                New Image</button>
                        @endif
                        @error('newImage')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div><label for="chief-library-image" class="mb-1 block text-xs font-bold text-zinc-700">Media
                            Library Image</label><select id="chief-library-image" wire:model="imageMediaId"
                            @disabled($newImage)
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm">
                            <option value="">No existing image selected</option>
                            @foreach ($mediaAssets as $asset)
                                <option value="{{ $asset->id }}">#{{ $asset->id }} —
                                    {{ $asset->title ?: $asset->original_name }}</option>
                            @endforeach
                        </select>
                        @error('imageMediaId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-60"><span
                            wire:loading.remove wire:target="save">Save Chief Instructor</span><span wire:loading
                            wire:target="save">Saving...</span></button>
                </div>
            </form>
        </aside>
    </div>
</div>

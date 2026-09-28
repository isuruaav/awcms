<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-zinc-950">Galleries</h1><p class="mt-1 text-sm text-zinc-500">Create albums, manage photographs and control publication.</p></div>
        @can('galleries.create')<a href="{{ route('admin.galleries.create') }}" wire:navigate class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white">Create Gallery</a>@endcan
    </div>
    @if (session('status'))<div role="status" class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    @if ($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @can('news.view')<p class="rounded-xl border border-zinc-200 bg-white p-4 text-sm text-zinc-600">News albums use the news title and images. <a href="{{ route('admin.news.index') }}" wire:navigate class="font-semibold text-emerald-700">Manage them in News →</a></p>@endcan
    <div class="flex flex-wrap gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search galleries" aria-label="Search galleries" class="min-w-0 flex-1 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm">
        <select wire:model.live="statusFilter" aria-label="Publication status" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm">
            <option value="">All galleries</option>
            <option value="draft">Draft</option><option value="published">Published</option>
        </select>
    </div>
    <div class="divide-y divide-zinc-200 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
        @forelse ($galleries as $gallery)
            @php
                $published = $gallery->status === \App\Enums\GalleryStatus::Published;
                $draft = $gallery->status === \App\Enums\GalleryStatus::Draft;
                $cover = $gallery->coverMedia ? app(\App\Services\MediaUrlService::class)->thumbnailOrOriginal($gallery->coverMedia) : null;
            @endphp
            <article wire:key="gallery-{{ $gallery->id }}" class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                @if ($cover)<img src="{{ $cover }}" alt="{{ $gallery->title }}" loading="lazy" class="h-20 w-24 shrink-0 rounded-lg object-cover">@endif
                <div class="min-w-0 flex-1">
                    <h2 class="break-words font-bold text-zinc-900">{{ $gallery->title }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $gallery->images_count }} images · {{ $published ? 'Published' : ($draft ? 'Draft' : 'Unpublished') }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-3 text-sm font-semibold">
                    @can('galleries.update')<a href="{{ route('admin.galleries.edit', ['gallery' => $gallery->id]) }}" wire:navigate class="text-blue-700">Edit</a>@endcan
                    @can('galleries.publish')
                        @if ($draft)<button type="button" wire:click="publish({{ $gallery->id }})" wire:confirm="Publish this gallery?" wire:loading.attr="disabled" class="text-emerald-700">Publish</button>
                        @else<button type="button" wire:click="unpublish({{ $gallery->id }})" wire:confirm="Return this gallery to Draft and keep its images?" wire:loading.attr="disabled" class="text-amber-800">{{ $published ? 'Unpublish' : 'Return to Draft' }}</button>@endif
                    @endcan
                    @can('galleries.delete')
                        @if (! $published)<button type="button" wire:click="delete({{ $gallery->id }})" wire:confirm="Delete this gallery?" wire:loading.attr="disabled" class="text-red-700">Delete</button>@endif
                    @endcan
                </div>
            </article>
        @empty
            <p class="p-10 text-center text-sm text-zinc-500">No galleries found.</p>
        @endforelse
    </div>
    {{ $galleries->links() }}
</div>

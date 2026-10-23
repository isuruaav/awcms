<div class="space-y-6" x-data="{ preparing: false }" x-on:publication-upload-start="preparing = true" x-on:publication-upload-end="preparing = false">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-950">Publications</h1>
            <p class="mt-1 text-sm text-zinc-500">Upload a PDF and its cover image separately.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('publications.index') }}" target="_blank" rel="noopener" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-semibold">View public page ↗</a>
            @can('documents.create')
                @can('media.upload')
                    <button type="button" wire:click="create" x-bind:disabled="preparing" class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white disabled:opacity-50">+ Upload Publication</button>
                @endcan
            @endcan
        </div>
    </header>

    @if (session('publication-success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('publication-success') }}</div>
    @endif

    @if ($formOpen)
        <form wire:submit="save" wire:key="publication-form-{{ $formVersion }}" data-publication-upload class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold">{{ $editingId ? 'Edit publication' : 'Upload publication' }}</h2>
                <button type="button" wire:click="cancel" data-publication-control x-bind:disabled="preparing" class="text-sm font-semibold text-zinc-500">Cancel</button>
            </div>
            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_200px]">
                <div class="space-y-5">
                    <div>
                        <label for="publication-locale" class="mb-2 block text-sm font-semibold">Publication language</label>
                        <select id="publication-locale" wire:model="locale" required class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm">
                            <option value="en">English</option>
                            <option value="si">සිංහල</option>
                        </select>
                        <p class="mt-1 text-xs text-zinc-500">Upload each language as a separate publication with its own PDF.</p>
                    </div>
                    <div>
                        <label for="publication-title" class="mb-2 block text-sm font-semibold">{{ $locale === 'si' ? 'සිංහල මාතෘකාව' : 'English title' }}</label>
                        <input id="publication-title" wire:model="title" data-publication-title type="text" required maxlength="255" lang="{{ $locale }}" placeholder="{{ $locale === 'si' ? 'ප්‍රකාශන නම ඇතුළත් කරන්න' : 'e.g. Signals Journal - October 2026' }}" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm">
                    </div>
                    @can('media.upload')
                        <div wire:ignore class="rounded-xl border-2 border-dashed border-emerald-200 bg-emerald-50 p-5">
                            <label for="publication-pdf-{{ $formVersion }}" class="mb-3 block text-sm font-semibold">{{ $editingId ? 'Replace PDF (optional)' : 'PDF file' }}</label>
                            <input id="publication-pdf-{{ $formVersion }}" data-publication-pdf-input type="file" accept="application/pdf,.pdf" class="block w-full text-sm">
                            <p class="mt-3 text-xs leading-5 text-zinc-600">PDF only, up to 20 MB. Upload the PDF first, then select its cover image. Use a PDF without a password.</p>
                        </div>
                        <div wire:ignore class="rounded-xl border-2 border-dashed border-zinc-200 bg-white p-5">
                            <label for="publication-cover-{{ $formVersion }}" class="mb-3 block text-sm font-semibold">{{ $editingId ? 'Replace cover image (optional)' : 'First-page cover image' }}</label>
                            <input id="publication-cover-{{ $formVersion }}" data-publication-cover-input type="file" accept="image/jpeg,.jpg,.jpeg" class="block w-full text-sm">
                            <p class="mt-3 text-xs leading-5 text-zinc-600">JPG/JPEG image only, up to 2 MB. Select the image of the PDF's first page.</p>
                            <p data-publication-upload-status role="status" aria-live="polite" class="mt-3 text-sm text-emerald-800"></p>
                        </div>
                    @endcan
                    <label class="flex items-center gap-3 text-sm font-semibold">
                        <input type="checkbox" wire:model="isPublished" @disabled(!auth()->user()->can('documents.publish')) class="rounded border-zinc-300 text-emerald-700">
                        Publish on the website
                    </label>
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold">Cover image preview</p>
                    <div wire:ignore class="flex aspect-[3/4] items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100">
                        <img data-publication-cover-preview @if ($editing) src="{{ route('admin.publications.cover', ['publication' => $editing->uuid]) }}" @else hidden @endif alt="Publication first-page preview" class="h-full w-full object-contain">
                        <span data-publication-cover-placeholder @if ($editing) hidden @endif class="p-5 text-center text-sm text-zinc-400">Select a PDF to preview its first page</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end border-t border-zinc-200 pt-5">
                <button type="submit" data-publication-control x-bind:disabled="preparing" wire:loading.attr="disabled" wire:target="save,pdf,cover" class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Save Publication</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    @endif

    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="flex flex-wrap gap-3 border-b border-zinc-200 p-4">
            <input type="search" wire:model.live.debounce.300ms="search" aria-label="Search publications" placeholder="Search publications…" class="min-w-0 flex-1 rounded-xl border border-zinc-300 px-4 py-3 text-sm">
            <select wire:model.live="statusFilter" aria-label="Publication status" class="rounded-xl border border-zinc-300 px-4 py-3 text-sm">
                <option value="all">All publications</option><option value="published">Published</option><option value="draft">Drafts</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-4">Publication</th><th class="px-4 py-4">Status</th><th class="px-4 py-4">Uploaded</th><th class="px-5 py-4 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($publications as $publication)
                        <tr wire:key="publication-{{ $publication->id }}">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.publications.pdf', ['publication' => $publication->uuid]) }}" target="_blank" rel="noopener" class="flex min-w-48 items-center gap-4">
                                    <img src="{{ route('admin.publications.cover', ['publication' => $publication->uuid]) }}" alt="" width="48" height="64" loading="lazy" class="h-16 w-12 shrink-0 rounded border border-zinc-200 bg-zinc-50 object-contain">
                                    <span><strong class="block text-zinc-900">{{ $publication->title }}</strong><span class="mt-1 block text-xs text-zinc-500">{{ $publication->locale === 'si' ? 'සිංහල' : 'English' }} · PDF · {{ number_format($publication->size_bytes / 1048576, 1) }} MB</span></span>
                                </a>
                            </td>
                            <td class="px-4 py-4"><span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $publication->is_published, 'bg-zinc-100 text-zinc-600' => !$publication->is_published])>{{ $publication->is_published ? 'Published' : 'Draft' }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500">{{ $publication->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    @can('documents.update')
                                        @if (!$publication->is_published || auth()->user()->can('documents.publish'))
                                            <x-admin.icon-action label="Edit" icon="pencil-square" tone="info"
                                                wire:click="edit({{ $publication->id }})"
                                                x-bind:disabled="preparing" wire:loading.attr="disabled"
                                            />
                                        @endif
                                    @endcan
                                    @can('documents.publish')
                                        <x-admin.icon-action
                                            :label="$publication->is_published ? 'Unpublish' : 'Publish'"
                                            :icon="$publication->is_published ? 'eye-slash' : 'arrow-up-tray'"
                                            :tone="$publication->is_published ? 'warning' : 'success'"
                                            wire:click="{{ $publication->is_published ? 'unpublish' : 'publish' }}({{ $publication->id }})"
                                            x-bind:disabled="preparing" wire:loading.attr="disabled"
                                        />
                                    @endcan
                                    @can('documents.delete')
                                        <x-admin.icon-action label="Delete" icon="trash" tone="danger"
                                            wire:click="delete({{ $publication->id }})"
                                            wire:confirm="Remove this publication from the website?"
                                            x-bind:disabled="preparing" wire:loading.attr="disabled"
                                        />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-14 text-center text-zinc-500">{{ $search !== '' || $statusFilter !== 'all' ? 'No publications match your filters.' : 'No publications yet. Upload your first PDF to get started.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($publications->hasPages())<div class="border-t border-zinc-200 p-4">{{ $publications->links() }}</div>@endif
    </section>
</div>

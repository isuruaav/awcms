<div class="space-y-6">
    @php
        $canCreate = auth()->user()?->can('documents.create') ?? false;
        $canUpdate = auth()->user()?->can('documents.update') ?? false;
        $canDelete = auth()->user()?->can('documents.delete') ?? false;
        $canPublish = auth()->user()?->can('documents.publish') ?? false;
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">
                Content Management
            </p>

            <h1 class="mt-2 text-2xl font-bold text-zinc-950">
                Document Uploads
            </h1>

            <p class="mt-2 text-sm text-zinc-600">
                Upload and manage English and Sinhala documents from one place.
            </p>
        </div>

        @if ($canCreate)
            <button
                type="button"
                wire:click="create"
                class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800"
            >
                + Add Document
            </button>
        @endif
    </div>

    @if (session('status'))
        <div
            role="status"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900"
        >
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            role="alert"
            class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"
        >
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        @class([
            'grid min-w-0 items-start gap-6',
            'min-[1800px]:grid-cols-[minmax(0,1fr)_420px]' =>
                $canCreate || $canUpdate,
        ])
    >
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-zinc-900">
                            Document directory
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            Search, filter, sort and manage published document uploads.
                        </p>
                    </div>

                    <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-bold text-zinc-700">
                        {{ $documents->total() }} documents
                    </span>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(260px,1fr)_190px_120px_auto]">
                    <div class="relative">
                        <svg
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            id="document-search"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search English or Sinhala title..."
                            class="w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-9 pr-3 text-sm"
                        >
                    </div>

                    <select
                        wire:model.live="statusFilter"
                        class="rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="all">
                            All statuses
                        </option>
                        <option value="published">
                            Published
                        </option>
                        <option value="not_public">
                            Not public
                        </option>
                    </select>

                    <select
                        wire:model.live="perPage"
                        class="rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="10">10 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                    </select>

                    <button
                        type="button"
                        wire:click="clearTableFilters"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 hover:bg-zinc-100"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full text-left">
                    <thead class="border-b border-zinc-200 bg-white">
                        <tr class="text-[11px] font-black uppercase tracking-wider text-zinc-500">
                            <th class="px-4 py-3">
                                <button
                                    type="button"
                                    wire:click="sortBy('title')"
                                    class="inline-flex items-center gap-1.5 hover:text-zinc-900"
                                >
                                    Document
                                    @if ($sortField === 'title')
                                        <span>
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </button>
                            </th>

                            <th class="px-4 py-3">
                                <button
                                    type="button"
                                    wire:click="sortBy('status')"
                                    class="inline-flex items-center gap-1.5 hover:text-zinc-900"
                                >
                                    Status
                                    @if ($sortField === 'status')
                                        <span>
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </button>
                            </th>

                            <th class="px-4 py-3">
                                English
                            </th>

                            <th class="px-4 py-3">
                                සිංහල
                            </th>

                            <th class="px-4 py-3">
                                <button
                                    type="button"
                                    wire:click="sortBy('updated_at')"
                                    class="inline-flex items-center gap-1.5 hover:text-zinc-900"
                                >
                                    Updated
                                    @if ($sortField === 'updated_at')
                                        <span>
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </button>
                            </th>

                            @if ($canUpdate || $canDelete)
                                <th class="px-4 py-3 text-right">
                                    Action
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($documents as $document)
                            @php
                                $englishViewUrl = route('document-files.view', [
                                    'locale' => 'en',
                                    'slug' => $document->slug,
                                ]);

                                $englishDownloadUrl = route('document-files.download', [
                                    'locale' => 'en',
                                    'slug' => $document->slug,
                                ]);

                                $sinhalaViewUrl = route('document-files.view', [
                                    'locale' => 'si',
                                    'slug' => $document->slug,
                                ]);

                                $sinhalaDownloadUrl = route('document-files.download', [
                                    'locale' => 'si',
                                    'slug' => $document->slug,
                                ]);
                            @endphp

                            <tr
                                wire:key="document-upload-{{ $document->id }}"
                                @class([
                                    'transition hover:bg-zinc-50',
                                    'bg-blue-50/60' => $editingId === $document->id,
                                ])
                            >
                                <td class="px-4 py-4 align-top">
                                    <div class="min-w-[250px] max-w-sm">
                                        @if ($canUpdate)
                                            <button
                                                type="button"
                                                wire:click="edit({{ $document->id }})"
                                                class="break-words text-left font-black text-zinc-950 hover:text-blue-700 hover:underline"
                                            >
                                                {{ $document->title }}
                                            </button>
                                        @else
                                            <p class="break-words font-black text-zinc-950">
                                                {{ $document->title }}
                                            </p>
                                        @endif

                                        @if ($document->title_si)
                                            <p
                                                lang="si"
                                                class="mt-1 break-words text-sm leading-6 text-zinc-500"
                                            >
                                                {{ $document->title_si }}
                                            </p>
                                        @endif

                                        <p class="mt-1 font-mono text-[10px] text-zinc-400">
                                            {{ $document->slug }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <span
                                        @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wide',
                                            'bg-emerald-100 text-emerald-700' => $document->isPublished(),
                                            'bg-zinc-200 text-zinc-600' => ! $document->isPublished(),
                                        ])
                                    >
                                        {{ $document->isPublished()
                                            ? 'Published'
                                            : 'Not public' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 align-top">
                                    @if ($document->isPublished())
                                        <div
                                            x-data="{ copied: false }"
                                            class="flex min-w-[180px] flex-wrap items-center gap-2"
                                        >
                                            <a
                                                href="{{ $englishViewUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-bold text-sky-700 hover:bg-sky-100"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="{{ $englishDownloadUrl }}"
                                                class="rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100"
                                            >
                                                Download
                                            </a>

                                            <button
                                                type="button"
                                                class="rounded-lg bg-zinc-100 px-2.5 py-1.5 text-xs font-bold text-zinc-700 hover:bg-zinc-200"
                                                x-on:click="
                                                    if (navigator.clipboard) {
                                                        navigator.clipboard.writeText(@js($englishViewUrl))
                                                            .then(() => { copied = true; })
                                                            .catch(() => { copied = false; });
                                                    }
                                                "
                                                x-text="copied ? 'Copied' : 'Copy'"
                                            >
                                                Copy
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs font-semibold text-zinc-400">
                                            Not available
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    @if ($document->isPublished())
                                        <div
                                            x-data="{ copied: false }"
                                            class="flex min-w-[180px] flex-wrap items-center gap-2"
                                        >
                                            <a
                                                href="{{ $sinhalaViewUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs font-bold text-sky-700 hover:bg-sky-100"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="{{ $sinhalaDownloadUrl }}"
                                                class="rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100"
                                            >
                                                Download
                                            </a>

                                            <button
                                                type="button"
                                                class="rounded-lg bg-zinc-100 px-2.5 py-1.5 text-xs font-bold text-zinc-700 hover:bg-zinc-200"
                                                x-on:click="
                                                    if (navigator.clipboard) {
                                                        navigator.clipboard.writeText(@js($sinhalaViewUrl))
                                                            .then(() => { copied = true; })
                                                            .catch(() => { copied = false; });
                                                    }
                                                "
                                                x-text="copied ? 'Copied' : 'Copy'"
                                            >
                                                Copy
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs font-semibold text-zinc-400">
                                            Not available
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    <p class="whitespace-nowrap text-sm font-semibold text-zinc-700">
                                        {{ $document->updated_at?->format('d M Y') ?? '—' }}
                                    </p>

                                    <p class="mt-1 whitespace-nowrap text-[11px] text-zinc-400">
                                        {{ $document->updated_at?->format('h:i A') ?? '' }}
                                    </p>
                                </td>

                                @if ($canUpdate || $canDelete)
                                    <td class="px-4 py-4 text-right align-top">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($canUpdate)
                                                <button
                                                    type="button"
                                                    wire:click="edit({{ $document->id }})"
                                                    class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800"
                                                >
                                                    Edit
                                                </button>
                                            @endif

                                            @if ($canDelete)
                                                <button
                                                    type="button"
                                                    wire:click="delete({{ $document->id }})"
                                                    wire:confirm="Delete this document? Its document links will no longer be available."
                                                    wire:loading.attr="disabled"
                                                    wire:target="delete({{ $document->id }})"
                                                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 disabled:opacity-50"
                                                >
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ ($canUpdate || $canDelete) ? 6 : 5 }}"
                                    class="px-6 py-14 text-center"
                                >
                                    <p class="font-black text-zinc-700">
                                        No documents found.
                                    </p>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        Add a document or change the current filters.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-xs font-semibold text-zinc-500">
                        Showing
                        <span class="font-black text-zinc-700">
                            {{ $documents->firstItem() ?? 0 }}
                        </span>
                        to
                        <span class="font-black text-zinc-700">
                            {{ $documents->lastItem() ?? 0 }}
                        </span>
                        of
                        <span class="font-black text-zinc-700">
                            {{ $documents->total() }}
                        </span>
                        documents
                    </p>

                    <div>
                        {{ $documents->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </section>

        @if (
            ($editingId === null && $canCreate)
            || ($editingId !== null && $canUpdate)
        )
            <form
                wire:submit="save"
                class="mx-auto w-full max-w-2xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm min-[1800px]:sticky min-[1800px]:top-6 min-[1800px]:mx-0 min-[1800px]:max-w-none"
            >
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                    <h2 class="text-lg font-bold text-zinc-950">
                        {{ $editingId
                            ? 'Edit Document'
                            : 'Add Document' }}
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        PDF, Word or Excel. Maximum 10 MB per file.
                    </p>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label
                            for="upload-title"
                            class="mb-2 block text-sm font-semibold"
                        >
                            English title
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="upload-title"
                            type="text"
                            wire:model="title"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm"
                        >

                        @error('title')
                            <p class="mt-1 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="upload-title-si"
                            class="mb-2 block text-sm font-semibold"
                        >
                            සිංහල මාතෘකාව
                        </label>

                        <input
                            id="upload-title-si"
                            type="text"
                            lang="si"
                            wire:model="titleSi"
                            maxlength="255"
                            class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm"
                        >

                        @error('titleSi')
                            <p class="mt-1 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-dashed border-emerald-300 bg-emerald-50 p-4">
                        <label
                            for="upload-english"
                            class="mb-3 block text-sm font-bold text-emerald-900"
                        >
                            English / shared file
                        </label>

                        <input
                            id="upload-english"
                            type="file"
                            wire:model="englishFile"
                            accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm"
                        >

                        @error('englishFile')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-dashed border-sky-300 bg-sky-50 p-4">
                        <label
                            for="upload-sinhala"
                            class="mb-3 block text-sm font-bold text-sky-900"
                        >
                            Sinhala file — optional
                        </label>

                        <input
                            id="upload-sinhala"
                            type="file"
                            wire:model="sinhalaFile"
                            accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm"
                        >

                        @error('sinhalaFile')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <p class="text-xs leading-6 text-zinc-500">
                        Upload at least one file. When only one language file exists,
                        both language links use that file.

                        @if ($editingId)
                            Leave an upload field empty to keep its existing file.
                        @endif
                    </p>

                    <p
                        wire:loading
                        wire:target="englishFile,sinhalaFile"
                        class="text-sm font-semibold text-sky-700"
                    >
                        Uploading file...
                    </p>

                    @if ($canPublish)
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save,englishFile,sinhalaFile"
                            class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId
                                    ? 'Update & Publish'
                                    : 'Save & Publish' }}
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>
                    @else
                        <p class="text-sm text-amber-800">
                            Publishing permission is required to save public document uploads.
                        </p>
                    @endif

                    @if ($editingId)
                        <button
                            type="button"
                            wire:click="cancel"
                            class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm font-bold text-zinc-600 hover:bg-zinc-50"
                        >
                            Cancel Editing
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>

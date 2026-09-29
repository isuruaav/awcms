<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">
                Content Management
            </p>

            <h1 class="mt-2 text-2xl font-bold text-zinc-950">
                Document Uploads
            </h1>

            <p class="mt-2 text-sm text-zinc-600">
                Upload English and Sinhala documents and copy their public links.
            </p>
        </div>

        @can('documents.create')
            <button
                type="button"
                wire:click="create"
                class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"
            >
                + Add Document
            </button>
        @endcan
    </div>

    @if (session('status'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-3">
        <section class="min-w-0 space-y-4 xl:col-span-2">
            <label for="document-search" class="sr-only">
                Search documents
            </label>

            <input
                id="document-search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search English or Sinhala title..."
                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm"
            >

            @forelse ($documents as $document)
                <article
                    wire:key="document-upload-{{ $document->id }}"
                    class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h2 class="break-words text-lg font-bold text-zinc-950">
                                {{ $document->title }}
                            </h2>

                            @if ($document->title_si)
                                <p lang="si" class="mt-2 break-words text-sm leading-7 text-zinc-600">
                                    {{ $document->title_si }}
                                </p>
                            @endif

                            <p class="mt-3 text-xs text-zinc-500">
                                Updated {{ $document->updated_at?->format('Y.m.d H:i') }}
                            </p>
                        </div>

                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-bold',
                            'bg-emerald-50 text-emerald-800' => $document->isPublished(),
                            'bg-zinc-100 text-zinc-600' => ! $document->isPublished(),
                        ])>
                            {{ $document->isPublished() ? 'Published' : 'Not public' }}
                        </span>
                    </div>

                    @if ($document->isPublished())
                        <div class="mt-5 grid gap-4">
                            @foreach (['en' => 'English', 'si' => 'සිංහල'] as $code => $label)
                                @php
                                    $viewUrl = route('document-files.view', [
                                        'locale' => $code,
                                    'slug' => $document->slug,
                                    ]);

                                    $downloadUrl = route('document-files.download', [
                                        'locale' => $code,
                                      'slug' => $document->slug,
                                    ]);
                                @endphp

                                <div
                                    wire:key="document-link-{{ $document->id }}-{{ $code }}"
                                    x-data="{ copied: false }"
                                    class="rounded-xl bg-zinc-50 p-3"
                                >
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <strong class="text-sm text-zinc-800">
                                            {{ $label }}
                                        </strong>

                                        <div class="flex flex-wrap gap-3 text-xs font-bold">
                                            <a
                                                href="{{ $viewUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-sky-700"
                                            >
                                                View
                                            </a>

                                            <a href="{{ $downloadUrl }}" class="text-emerald-700">
                                                Download
                                            </a>

                                            <button
                                                type="button"
                                                class="text-zinc-700"
                                                x-on:click="
                                                    $refs.link.select();
                                                    if (navigator.clipboard) {
                                                        navigator.clipboard.writeText($refs.link.value)
                                                            .then(() => { copied = true; })
                                                            .catch(() => { copied = false; });
                                                    }
                                                "
                                                x-text="copied ? 'Copied' : 'Copy Link'"
                                            >
                                                Copy Link
                                            </button>
                                        </div>
                                    </div>

                                    <input
                                        x-ref="link"
                                        type="text"
                                        readonly
                                        value="{{ $viewUrl }}"
                                        aria-label="{{ $label }} document link"
                                        class="mt-3 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-600"
                                        x-on:click="$el.select()"
                                    >
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 flex gap-3 border-t border-zinc-100 pt-4">
                        @can('documents.update')
                            <button
                                type="button"
                                wire:click="edit({{ $document->id }})"
                                class="rounded-lg bg-sky-50 px-4 py-2 text-sm font-bold text-sky-800"
                            >
                                Edit
                            </button>
                        @endcan

                        @can('documents.delete')
                            <button
                                type="button"
                                wire:click="delete({{ $document->id }})"
                                wire:confirm="Delete this document? Its document links will no longer be available."
                                wire:loading.attr="disabled"
                                class="rounded-lg bg-red-50 px-4 py-2 text-sm font-bold text-red-700"
                            >
                                Delete
                            </button>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-14 text-center">
                    <h2 class="font-bold text-zinc-800">No documents found</h2>
                    <p class="mt-2 text-sm text-zinc-500">
                        Add a document or change your search.
                    </p>
                </div>
            @endforelse

            <div>{{ $documents->links() }}</div>
        </section>

        @if (
            ($editingId === null && auth()->user()?->can('documents.create'))
            || ($editingId !== null && auth()->user()?->can('documents.update'))
        )
            <form
                wire:submit="save"
                class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-bold text-zinc-950">
                    {{ $editingId ? 'Edit Document' : 'Add Document' }}
                </h2>

                <p class="mt-2 text-sm leading-6 text-zinc-500">
                    PDF, Word or Excel. Maximum 10 MB per file.
                </p>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="upload-title" class="mb-2 block text-sm font-semibold">
                            English title *
                        </label>

                        <input
                            id="upload-title"
                            type="text"
                            wire:model="title"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm"
                        >
                    </div>

                    <div>
                        <label for="upload-title-si" class="mb-2 block text-sm font-semibold">
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
                    </div>

                    <div class="rounded-xl border border-dashed border-emerald-300 bg-emerald-50 p-4">
                        <label for="upload-english" class="mb-3 block text-sm font-bold text-emerald-900">
                            English / shared file
                        </label>

                        <input
                            id="upload-english"
                            type="file"
                            wire:model="englishFile"
                            accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm"
                        >
                    </div>

                    <div class="rounded-xl border border-dashed border-sky-300 bg-sky-50 p-4">
                        <label for="upload-sinhala" class="mb-3 block text-sm font-bold text-sky-900">
                            Sinhala file — optional
                        </label>

                        <input
                            id="upload-sinhala"
                            type="file"
                            wire:model="sinhalaFile"
                            accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm"
                        >
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

                    @can('documents.publish')
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save,englishFile,sinhalaFile"
                            class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white disabled:opacity-50"
                        >
                            Save &amp; Publish
                        </button>
                    @else
                        <p class="text-sm text-amber-800">
                            Publishing permission is required to save public document uploads.
                        </p>
                    @endcan

                    @if ($editingId)
                        <button
                            type="button"
                            wire:click="cancel"
                            class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm font-bold text-zinc-600"
                        >
                            Cancel Editing
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>
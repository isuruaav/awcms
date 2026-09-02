@props([
    'id',
    'model' => 'content',
    'value' => '',
])

<div>
    <div
        wire:ignore
        class="overflow-hidden rounded-xl"
    >
        <input
            id="{{ $id }}-input"
            type="hidden"
            value="{{ $value }}"
        >

        <trix-editor
            id="{{ $id }}"
            input="{{ $id }}-input"
            aria-label="Page content visual editor"
            placeholder="Start typing your page content here..."
            class="trix-content awcms-editor"
            x-data
            x-on:trix-change.debounce.250ms="
                $wire.set(
                    @js($model),
                    $event.target.value,
                    false
                )
            "
            x-on:trix-file-accept="
                $event.preventDefault()
            "
        ></trix-editor>
    </div>

    <div class="mt-3 grid gap-3 lg:grid-cols-2">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-semibold text-emerald-950">
                Word-like editing
            </p>

            <p class="mt-1 text-sm leading-6 text-emerald-800">
                Use headings, bold, italic, links, quotations, bullet lists,
                numbered lists, indentation, undo and redo without writing HTML.
                Rich text pasted from Word is cleaned into safe web content.
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-950">
                Safe content only
            </p>

            <p class="mt-1 text-sm leading-6 text-amber-800">
                File attachments are disabled in the visual editor. Scripts,
                unsafe markup and JavaScript event attributes are removed before
                the page is stored.
            </p>
        </div>
    </div>

    @error($model)
        <p class="mt-2 text-sm font-medium text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>

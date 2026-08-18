@props([
    'blocks' => [],
])

<section
    class="overflow-hidden rounded-2xl
           border border-zinc-200
           bg-white shadow-sm"
>
    {{-- Header --}}
    <div
        class="border-b border-zinc-200
               bg-zinc-50 px-6 py-5"
    >
        <div
            class="flex flex-col gap-3
                   lg:flex-row lg:items-start
                   lg:justify-between"
        >
            <div>
                <div
                    class="flex flex-wrap
                           items-center gap-3"
                >
                    <h2
                        class="text-lg font-bold
                               text-zinc-950"
                    >
                        Page Builder
                    </h2>

                    <span
                        class="rounded-full
                               bg-violet-100
                               px-3 py-1
                               text-xs font-semibold
                               text-violet-700"
                    >
                        {{ count($blocks) }} block(s)
                    </span>
                </div>

                <p
                    class="mt-1 max-w-2xl
                           text-sm leading-6
                           text-zinc-600"
                >
                    Build the page using safe,
                    predefined content blocks.
                    No HTML or programming knowledge
                    is required.
                </p>
            </div>

            <div
                class="rounded-xl border
                       border-emerald-200
                       bg-emerald-50
                       px-4 py-2.5
                       text-xs font-medium
                       text-emerald-800"
            >
                Maximum 100 blocks
            </div>
        </div>
    </div>

    {{-- Block Picker --}}
    <div
        class="border-b border-zinc-200
               bg-white p-6"
    >
        <div class="mb-4">
            <h3
                class="text-sm font-bold
                       text-zinc-900"
            >
                Add a Block
            </h3>

            <p
                class="mt-1 text-xs
                       text-zinc-500"
            >
                Select the type of content you
                want to add to this page.
            </p>
        </div>

        <div
            class="grid gap-3
                   sm:grid-cols-2
                   lg:grid-cols-4"
        >
            {{-- Heading --}}
            <button
                type="button"
                wire:click="addBlock('heading')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-violet-300
                       hover:bg-violet-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-violet-100
                           text-sm font-black
                           text-violet-700"
                >
                    H
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Heading
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Section heading H2–H4
                </p>
            </button>

            {{-- Text --}}
            <button
                type="button"
                wire:click="addBlock('text')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-blue-300
                       hover:bg-blue-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-blue-100
                           text-sm font-black
                           text-blue-700"
                >
                    T
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Text
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Paragraph or text content
                </p>
            </button>

            {{-- Image --}}
            <button
                type="button"
                wire:click="addBlock('image')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-emerald-300
                       hover:bg-emerald-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-emerald-100
                           text-sm font-black
                           text-emerald-700"
                >
                    IMG
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Image
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Image with caption
                </p>
            </button>

            {{-- Button --}}
            <button
                type="button"
                wire:click="addBlock('button')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-amber-300
                       hover:bg-amber-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-amber-100
                           text-xs font-black
                           text-amber-700"
                >
                    BTN
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Button
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Call-to-action link
                </p>
            </button>

            {{-- Two columns --}}
            <button
                type="button"
                wire:click="addBlock('two_columns')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-cyan-300
                       hover:bg-cyan-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-cyan-100
                           text-sm font-black
                           text-cyan-700"
                >
                    2C
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Two Columns
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Side-by-side content
                </p>
            </button>

            {{-- Callout --}}
            <button
                type="button"
                wire:click="addBlock('callout')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-orange-300
                       hover:bg-orange-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-orange-100
                           text-sm font-black
                           text-orange-700"
                >
                    !
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Callout
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Highlight information
                </p>
            </button>

            {{-- Divider --}}
            <button
                type="button"
                wire:click="addBlock('divider')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-zinc-400
                       hover:bg-zinc-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-zinc-100
                           text-sm font-black
                           text-zinc-700"
                >
                    —
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Divider
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Horizontal separator
                </p>
            </button>

            {{-- Spacer --}}
            <button
                type="button"
                wire:click="addBlock('spacer')"
                wire:loading.attr="disabled"
                wire:target="addBlock"
                class="group rounded-xl
                       border border-zinc-200
                       bg-white p-4
                       text-left transition
                       hover:border-pink-300
                       hover:bg-pink-50
                       disabled:opacity-50"
            >
                <div
                    class="flex h-9 w-9
                           items-center justify-center
                           rounded-lg
                           bg-pink-100
                           text-sm font-black
                           text-pink-700"
                >
                    ↕
                </div>

                <p
                    class="mt-3 text-sm
                           font-bold text-zinc-900"
                >
                    Spacer
                </p>

                <p
                    class="mt-1 text-xs
                           text-zinc-500"
                >
                    Vertical spacing
                </p>
            </button>
        </div>

        @error('blocks')
            <p
                class="mt-4 rounded-lg
                       bg-red-50 px-4 py-3
                       text-sm font-medium
                       text-red-700"
            >
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Builder Canvas --}}
    <div class="bg-zinc-50/70 p-6">
        @if (count($blocks) === 0)
            <div
                class="rounded-2xl border-2
                       border-dashed border-zinc-300
                       bg-white px-6 py-14
                       text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14
                           items-center justify-center
                           rounded-2xl
                           bg-zinc-100
                           text-2xl text-zinc-500"
                >
                    +
                </div>

                <h3
                    class="mt-4 text-base
                           font-bold text-zinc-800"
                >
                    Start building this page
                </h3>

                <p
                    class="mx-auto mt-2 max-w-md
                           text-sm leading-6
                           text-zinc-500"
                >
                    Select a block from above.
                    You can add multiple blocks and
                    rearrange them at any time.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($blocks as $index => $block)
                    @php
                        $type = is_string(
                            $block['type'] ?? null
                        )
                            ? $block['type']
                            : '';

                        $blockId = is_string(
                            $block['id'] ?? null
                        )
                            ? $block['id']
                            : 'block-' . $index;

                        $label = match ($type) {
                            'heading' => 'Heading',
                            'text' => 'Text',
                            'image' => 'Image',
                            'button' => 'Button',
                            'two_columns' => 'Two Columns',
                            'callout' => 'Callout',
                            'divider' => 'Divider',
                            'spacer' => 'Spacer',
                            default => 'Unknown Block',
                        };
                    @endphp

                    <article
                        wire:key="page-builder-{{ $blockId }}"
                        class="overflow-hidden
                               rounded-2xl border
                               border-zinc-200
                               bg-white shadow-sm"
                    >
                        {{-- Block Toolbar --}}
                        <div
                            class="flex flex-col gap-3
                                   border-b border-zinc-200
                                   bg-white px-5 py-4
                                   md:flex-row
                                   md:items-center
                                   md:justify-between"
                        >
                            <div
                                class="flex items-center
                                       gap-3"
                            >
                                <div
                                    class="flex h-9 w-9
                                           shrink-0
                                           items-center
                                           justify-center
                                           rounded-lg
                                           bg-zinc-900
                                           text-xs font-bold
                                           text-white"
                                >
                                    {{ $index + 1 }}
                                </div>

                                <div>
                                    <h3
                                        class="text-sm
                                               font-bold
                                               text-zinc-900"
                                    >
                                        {{ $label }}
                                    </h3>

                                    <p
                                        class="text-xs
                                               text-zinc-500"
                                    >
                                        Block
                                        #{{ $index + 1 }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex flex-wrap
                                       items-center gap-2"
                            >
                                <button
                                    type="button"
                                    wire:click="moveBlockUp({{ $index }})"
                                    @disabled($index === 0)
                                    title="Move block up"
                                    class="rounded-lg border
                                           border-zinc-300
                                           bg-white px-3 py-2
                                           text-xs font-semibold
                                           text-zinc-700
                                           hover:bg-zinc-50
                                           disabled:cursor-not-allowed
                                           disabled:opacity-40"
                                >
                                    ↑ Up
                                </button>

                                <button
                                    type="button"
                                    wire:click="moveBlockDown({{ $index }})"
                                    @disabled(
                                        $index === count($blocks) - 1
                                    )
                                    title="Move block down"
                                    class="rounded-lg border
                                           border-zinc-300
                                           bg-white px-3 py-2
                                           text-xs font-semibold
                                           text-zinc-700
                                           hover:bg-zinc-50
                                           disabled:cursor-not-allowed
                                           disabled:opacity-40"
                                >
                                    ↓ Down
                                </button>

                                <button
                                    type="button"
                                    wire:click="removeBlock({{ $index }})"
                                    wire:confirm="Remove this {{ $label }} block?"
                                    class="rounded-lg border
                                           border-red-200
                                           bg-red-50 px-3 py-2
                                           text-xs font-semibold
                                           text-red-700
                                           hover:bg-red-100"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        {{-- Block Configuration --}}
                        <div class="p-5 md:p-6">
                            @error("blocks.$index")
                                <div
                                    class="mb-5 rounded-xl
                                           border border-red-200
                                           bg-red-50
                                           px-4 py-3
                                           text-sm font-medium
                                           text-red-700"
                                >
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Heading --}}
                            @if ($type === 'heading')
                                <div
                                    class="grid gap-5
                                           md:grid-cols-[160px_minmax(0,1fr)]"
                                >
                                    <div>
                                        <label
                                            for="block-{{ $index }}-level"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Heading Level
                                        </label>

                                        <select
                                            id="block-{{ $index }}-level"
                                            wire:model="blocks.{{ $index }}.data.level"
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm
                                                   focus:border-violet-500
                                                   focus:ring-violet-500"
                                        >
                                            <option value="h2">
                                                H2 — Main Section
                                            </option>

                                            <option value="h3">
                                                H3 — Sub Section
                                            </option>

                                            <option value="h4">
                                                H4 — Small Heading
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            for="block-{{ $index }}-heading"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Heading Text
                                        </label>

                                        <input
                                            id="block-{{ $index }}-heading"
                                            type="text"
                                            maxlength="255"
                                            wire:model="blocks.{{ $index }}.data.text"
                                            placeholder="Enter section heading..."
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm
                                                   focus:border-violet-500
                                                   focus:ring-violet-500"
                                        >
                                    </div>
                                </div>

                            {{-- Text --}}
                            @elseif ($type === 'text')
                                <div>
                                    <label
                                        for="block-{{ $index }}-text"
                                        class="mb-2 block
                                               text-sm font-semibold
                                               text-zinc-800"
                                    >
                                        Text Content
                                    </label>

                                    <textarea
                                        id="block-{{ $index }}-text"
                                        rows="7"
                                        wire:model="blocks.{{ $index }}.data.content"
                                        placeholder="Write the content for this section..."
                                        class="w-full resize-y
                                               rounded-xl border
                                               border-zinc-300
                                               bg-white px-4 py-3
                                               text-sm leading-6
                                               focus:border-blue-500
                                               focus:ring-blue-500"
                                    ></textarea>

                                    <p
                                        class="mt-2 text-xs
                                               text-zinc-500"
                                    >
                                        Content is sanitized
                                        automatically before saving.
                                    </p>
                                </div>

                            {{-- Image --}}
                            @elseif ($type === 'image')
                                <div class="space-y-5">
                                    <div>
                                        <label
                                            for="block-{{ $index }}-image"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Image URL
                                        </label>

                                        <input
                                            id="block-{{ $index }}-image"
                                            type="text"
                                            wire:model.live.debounce.500ms="blocks.{{ $index }}.data.src"
                                            placeholder="/images/example.jpg or https://..."
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm
                                                   focus:border-emerald-500
                                                   focus:ring-emerald-500"
                                        >

                                        <p
                                            class="mt-2 text-xs
                                                   text-zinc-500"
                                        >
                                            Media Library selection
                                            will be integrated later.
                                        </p>
                                    </div>

                                    @if (
                                        ! empty(
                                            $block['data']['src']
                                            ?? null
                                        )
                                    )
                                        <div
                                            class="overflow-hidden
                                                   rounded-xl
                                                   border
                                                   border-zinc-200
                                                   bg-zinc-100"
                                        >
                                            <img
                                                src="{{ $block['data']['src'] }}"
                                                alt=""
                                                class="max-h-72
                                                       w-full
                                                       object-contain"
                                            >
                                        </div>
                                    @endif

                                    <div
                                        class="grid gap-5
                                               md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="block-{{ $index }}-alt"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Alternative Text
                                            </label>

                                            <input
                                                id="block-{{ $index }}-alt"
                                                type="text"
                                                maxlength="160"
                                                wire:model="blocks.{{ $index }}.data.alt"
                                                placeholder="Describe the image"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >

                                            <p
                                                class="mt-2 text-xs
                                                       text-zinc-500"
                                            >
                                                Important for
                                                accessibility and SEO.
                                            </p>
                                        </div>

                                        <div>
                                            <label
                                                for="block-{{ $index }}-alignment"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Alignment
                                            </label>

                                            <select
                                                id="block-{{ $index }}-alignment"
                                                wire:model="blocks.{{ $index }}.data.alignment"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                                <option value="left">
                                                    Left
                                                </option>
                                                <option value="center">
                                                    Center
                                                </option>
                                                <option value="right">
                                                    Right
                                                </option>
                                                <option value="full">
                                                    Full Width
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="block-{{ $index }}-caption"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Caption
                                        </label>

                                        <input
                                            id="block-{{ $index }}-caption"
                                            type="text"
                                            maxlength="300"
                                            wire:model="blocks.{{ $index }}.data.caption"
                                            placeholder="Optional image caption..."
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm"
                                        >
                                    </div>
                                </div>

                            {{-- Button --}}
                            @elseif ($type === 'button')
                                <div class="space-y-5">
                                    <div
                                        class="grid gap-5
                                               md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="block-{{ $index }}-button-label"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Button Label
                                            </label>

                                            <input
                                                id="block-{{ $index }}-button-label"
                                                type="text"
                                                maxlength="80"
                                                wire:model="blocks.{{ $index }}.data.label"
                                                placeholder="Learn More"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                for="block-{{ $index }}-button-url"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Destination URL
                                            </label>

                                            <input
                                                id="block-{{ $index }}-button-url"
                                                type="text"
                                                wire:model="blocks.{{ $index }}.data.url"
                                                placeholder="/contact or https://..."
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="grid gap-5
                                               md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="block-{{ $index }}-button-style"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Button Style
                                            </label>

                                            <select
                                                id="block-{{ $index }}-button-style"
                                                wire:model="blocks.{{ $index }}.data.style"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                                <option value="primary">
                                                    Primary
                                                </option>
                                                <option value="secondary">
                                                    Secondary
                                                </option>
                                                <option value="outline">
                                                    Outline
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                for="block-{{ $index }}-target"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Open Link
                                            </label>

                                            <select
                                                id="block-{{ $index }}-target"
                                                wire:model="blocks.{{ $index }}.data.target"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                                <option value="_self">
                                                    Same Window
                                                </option>

                                                <option value="_blank">
                                                    New Window
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            {{-- Two Columns --}}
                            @elseif ($type === 'two_columns')
                                <div class="space-y-5">
                                    <div
                                        class="max-w-xs"
                                    >
                                        <label
                                            for="block-{{ $index }}-ratio"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Column Width
                                        </label>

                                        <select
                                            id="block-{{ $index }}-ratio"
                                            wire:model="blocks.{{ $index }}.data.ratio"
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm"
                                        >
                                            <option value="50-50">
                                                50% / 50%
                                            </option>

                                            <option value="40-60">
                                                40% / 60%
                                            </option>

                                            <option value="60-40">
                                                60% / 40%
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="grid gap-5
                                               lg:grid-cols-2"
                                    >
                                        <div
                                            class="rounded-xl
                                                   border
                                                   border-zinc-200
                                                   bg-zinc-50
                                                   p-4"
                                        >
                                            <label
                                                for="block-{{ $index }}-left"
                                                class="mb-2 block
                                                       text-sm font-bold
                                                       text-zinc-800"
                                            >
                                                Left Column
                                            </label>

                                            <textarea
                                                id="block-{{ $index }}-left"
                                                rows="7"
                                                wire:model="blocks.{{ $index }}.data.left"
                                                placeholder="Left column content..."
                                                class="w-full resize-y
                                                       rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm leading-6"
                                            ></textarea>
                                        </div>

                                        <div
                                            class="rounded-xl
                                                   border
                                                   border-zinc-200
                                                   bg-zinc-50
                                                   p-4"
                                        >
                                            <label
                                                for="block-{{ $index }}-right"
                                                class="mb-2 block
                                                       text-sm font-bold
                                                       text-zinc-800"
                                            >
                                                Right Column
                                            </label>

                                            <textarea
                                                id="block-{{ $index }}-right"
                                                rows="7"
                                                wire:model="blocks.{{ $index }}.data.right"
                                                placeholder="Right column content..."
                                                class="w-full resize-y
                                                       rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm leading-6"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>

                            {{-- Callout --}}
                            @elseif ($type === 'callout')
                                <div class="space-y-5">
                                    <div
                                        class="grid gap-5
                                               md:grid-cols-[minmax(0,1fr)_220px]"
                                    >
                                        <div>
                                            <label
                                                for="block-{{ $index }}-callout-title"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Callout Title
                                            </label>

                                            <input
                                                id="block-{{ $index }}-callout-title"
                                                type="text"
                                                maxlength="120"
                                                wire:model="blocks.{{ $index }}.data.title"
                                                placeholder="Important Information"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                for="block-{{ $index }}-callout-style"
                                                class="mb-2 block
                                                       text-sm font-semibold
                                                       text-zinc-800"
                                            >
                                                Style
                                            </label>

                                            <select
                                                id="block-{{ $index }}-callout-style"
                                                wire:model="blocks.{{ $index }}.data.style"
                                                class="w-full rounded-xl
                                                       border border-zinc-300
                                                       bg-white px-4 py-3
                                                       text-sm"
                                            >
                                                <option value="info">
                                                    Information
                                                </option>

                                                <option value="success">
                                                    Success
                                                </option>

                                                <option value="warning">
                                                    Warning
                                                </option>

                                                <option value="neutral">
                                                    Neutral
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="block-{{ $index }}-callout-content"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Callout Content
                                        </label>

                                        <textarea
                                            id="block-{{ $index }}-callout-content"
                                            rows="5"
                                            wire:model="blocks.{{ $index }}.data.content"
                                            placeholder="Enter highlighted information..."
                                            class="w-full resize-y
                                                   rounded-xl border
                                                   border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm leading-6"
                                        ></textarea>
                                    </div>
                                </div>

                            {{-- Divider --}}
                            @elseif ($type === 'divider')
                                <div
                                    class="rounded-xl
                                           border border-zinc-200
                                           bg-zinc-50 p-5"
                                >
                                    <p
                                        class="text-sm font-semibold
                                               text-zinc-700"
                                    >
                                        Divider Preview
                                    </p>

                                    <hr
                                        class="my-5
                                               border-zinc-300"
                                    >

                                    <p
                                        class="text-xs
                                               text-zinc-500"
                                    >
                                        A horizontal line will
                                        separate the surrounding
                                        content sections.
                                    </p>
                                </div>

                            {{-- Spacer --}}
                            @elseif ($type === 'spacer')
                                <div
                                    class="grid gap-5
                                           md:grid-cols-[240px_minmax(0,1fr)]"
                                >
                                    <div>
                                        <label
                                            for="block-{{ $index }}-spacer"
                                            class="mb-2 block
                                                   text-sm font-semibold
                                                   text-zinc-800"
                                        >
                                            Space Size
                                        </label>

                                        <select
                                            id="block-{{ $index }}-spacer"
                                            wire:model="blocks.{{ $index }}.data.size"
                                            class="w-full rounded-xl
                                                   border border-zinc-300
                                                   bg-white px-4 py-3
                                                   text-sm"
                                        >
                                            <option value="small">
                                                Small
                                            </option>

                                            <option value="medium">
                                                Medium
                                            </option>

                                            <option value="large">
                                                Large
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="rounded-xl
                                               border border-dashed
                                               border-zinc-300
                                               bg-zinc-50 p-5"
                                    >
                                        <p
                                            class="text-sm font-semibold
                                                   text-zinc-700"
                                        >
                                            Spacer
                                        </p>

                                        <p
                                            class="mt-1 text-xs
                                                   text-zinc-500"
                                        >
                                            Adds controlled vertical
                                            space between page sections.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Builder Footer --}}
    @if (count($blocks) > 0)
        <div
            class="flex flex-col gap-3
                   border-t border-zinc-200
                   bg-white px-6 py-4
                   sm:flex-row
                   sm:items-center
                   sm:justify-between"
        >
            <p
                class="text-xs
                       text-zinc-500"
            >
                {{ count($blocks) }} of 100
                available blocks used.
            </p>

            <p
                class="text-xs font-medium
                       text-zinc-600"
            >
                Use ↑ Up and ↓ Down to
                control page order.
            </p>
        </div>
    @endif
</section>
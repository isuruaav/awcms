@props([
    'id',
    'model' => 'content',
    'modeModel' => 'editorMode',
    'value' => '',
    'editorMode' => 'visual',
])

<div
    x-data="awcmsPageContentEditor(@js($value), @js($editorMode))"
    x-on:keydown.escape.window="if (fullscreen) toggleFullscreen()"
    x-bind:class="fullscreen ? 'awcms-page-editor-shell--fullscreen' : ''"
    class="awcms-page-editor-shell"
>
    <input
        x-ref="contentInput"
        type="hidden"
        wire:model="{{ $model }}"
    >

    <input
        x-ref="modeInput"
        type="hidden"
        wire:model="{{ $modeModel }}"
    >

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="inline-flex w-fit rounded-xl border border-zinc-200 bg-zinc-100 p-1">
            <button
                type="button"
                x-on:click="switchMode('visual')"
                x-bind:class="mode === 'visual' ? 'bg-white text-emerald-800 shadow-sm' : 'text-zinc-600 hover:text-zinc-900'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition"
            >
                Visual Editor
            </button>

            <button
                type="button"
                x-on:click="switchMode('html')"
                x-bind:class="mode === 'html' ? 'bg-white text-emerald-800 shadow-sm' : 'text-zinc-600 hover:text-zinc-900'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition"
            >
                HTML + Tailwind
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
            <span class="rounded-full bg-zinc-100 px-3 py-1.5 font-semibold text-zinc-700">
                One content source
            </span>

            <span>
                Both editors work with the same saved HTML.
            </span>
        </div>
    </div>

    <div
        x-cloak
        x-show="advancedWarning && mode === 'visual'"
        class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-4"
    >
        <p class="text-sm font-semibold text-amber-950">
            Advanced HTML/Tailwind is open in the Visual Editor
        </p>

        <p class="mt-1 text-sm leading-6 text-amber-800">
            You can view and make simple text changes here, but complex custom layouts are safest in
            HTML + Tailwind. Switching tabs does not duplicate the page content.
        </p>
    </div>

    <div x-show="mode === 'visual'" class="space-y-3">
        <div
            class="awcms-page-editor-toolbar"
            role="toolbar"
            aria-label="Visual editor formatting toolbar"
            x-on:pointerdown.capture="captureToolbarSelection($event)"
        >
            <div class="awcms-page-editor-toolbar-group">
                <select
                    aria-label="Heading level"
                    title="Heading level"
                    class="awcms-page-editor-select"
                    x-on:mousedown="rememberSelection()"
                    x-on:change="formatBlock($event.target.value); $event.target.value = 'P'"
                >
                    <option value="P">Paragraph</option>
                    <option value="H1">Heading 1</option>
                    <option value="H2">Heading 2</option>
                    <option value="H3">Heading 3</option>
                </select>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <button type="button" class="awcms-page-editor-button font-bold" title="Bold" aria-label="Bold" x-on:mousedown.prevent="runCommand('bold')">B</button>
                <button type="button" class="awcms-page-editor-button italic" title="Italic" aria-label="Italic" x-on:mousedown.prevent="runCommand('italic')">I</button>
                <button type="button" class="awcms-page-editor-button underline" title="Underline" aria-label="Underline" x-on:mousedown.prevent="runCommand('underline')">U</button>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <label class="awcms-page-editor-colour" title="Text colour">
                    <span>A</span>
                    <input
                        type="color"
                        value="#18181b"
                        aria-label="Text colour"
                        x-on:pointerdown="rememberSelection()"
                        x-on:change="setTextColor($event.target.value)"
                    >
                </label>

                <label class="awcms-page-editor-colour" title="Background colour">
                    <span class="rounded bg-amber-100 px-1">A</span>
                    <input
                        type="color"
                        value="#fef3c7"
                        aria-label="Background colour"
                        x-on:pointerdown="rememberSelection()"
                        x-on:change="setBackgroundColor($event.target.value)"
                    >
                </label>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <button type="button" class="awcms-page-editor-button" title="Align left" aria-label="Align left" x-on:mousedown.prevent="runCommand('justifyLeft')">L</button>
                <button type="button" class="awcms-page-editor-button" title="Align centre" aria-label="Align centre" x-on:mousedown.prevent="runCommand('justifyCenter')">C</button>
                <button type="button" class="awcms-page-editor-button" title="Align right" aria-label="Align right" x-on:mousedown.prevent="runCommand('justifyRight')">R</button>
                <button type="button" class="awcms-page-editor-button" title="Justify" aria-label="Justify" x-on:mousedown.prevent="runCommand('justifyFull')">J</button>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <button type="button" class="awcms-page-editor-button" title="Bullet list" aria-label="Bullet list" x-on:mousedown.prevent="runCommand('insertUnorderedList')">• List</button>
                <button type="button" class="awcms-page-editor-button" title="Numbered list" aria-label="Numbered list" x-on:mousedown.prevent="runCommand('insertOrderedList')">1. List</button>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <button type="button" class="awcms-page-editor-button" title="Insert link" aria-label="Insert link" x-on:mousedown.prevent="rememberSelection()" x-on:click="createLink()">Link</button>
                <button type="button" class="awcms-page-editor-button" title="Insert image" aria-label="Insert image" x-on:mousedown.prevent="rememberSelection()" x-on:click="insertImage()">Image</button>
                <button type="button" class="awcms-page-editor-button" title="Insert table" aria-label="Insert table" x-on:mousedown.prevent="rememberSelection()" x-on:click="insertTable()">Table</button>
                <button type="button" class="awcms-page-editor-button" title="Quote" aria-label="Quote" x-on:mousedown.prevent="formatBlock('BLOCKQUOTE')">Quote</button>
            </div>

            <div class="awcms-page-editor-toolbar-group">
                <button type="button" class="awcms-page-editor-button" title="Undo" aria-label="Undo" x-on:mousedown.prevent="runCommand('undo')">Undo</button>
                <button type="button" class="awcms-page-editor-button" title="Redo" aria-label="Redo" x-on:mousedown.prevent="runCommand('redo')">Redo</button>
                <button type="button" class="awcms-page-editor-button" title="Clear formatting" aria-label="Clear formatting" x-on:mousedown.prevent="clearFormatting()">Clear</button>
            </div>

            <div class="awcms-page-editor-toolbar-group ml-auto">
                <button
                    type="button"
                    class="awcms-page-editor-button"
                    x-on:click="toggleFullscreen()"
                    x-text="fullscreen ? 'Exit Full Screen' : 'Full Screen'"
                ></button>
            </div>
        </div>

        <div
            id="{{ $id }}-visual"
            x-ref="visual"
            wire:ignore
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            aria-label="Page content visual editor"
            data-placeholder="Start typing your page content here..."
            class="awcms-page-visual-editor"
            x-on:input.debounce.120ms="syncFromVisual()"
            x-on:mouseup="rememberSelection()"
            x-on:keyup="rememberSelection()"
            x-on:focus="rememberSelection()"
            x-on:blur="rememberSelection(); syncFromVisual()"
        ></div>

        <div class="grid gap-3 lg:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-950">
                    Word-like editing
                </p>

                <p class="mt-1 text-sm leading-6 text-emerald-800">
                    Headings, bold, italic, underline, text/background colour, alignment, lists,
                    links, images, tables, quotes, undo/redo, clear formatting and full screen are available.
                </p>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4">
                <p class="text-sm font-semibold text-sky-950">
                    Images
                </p>

                <p class="mt-1 text-sm leading-6 text-sky-800">
                    Use the Image button and paste a public image URL. For managed website images,
                    upload/select the image in AWCMS Media Library and use its public URL.
                </p>
            </div>
        </div>
    </div>

    <div x-cloak x-show="mode === 'html'" class="space-y-3">
        <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-zinc-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-zinc-900">
                    HTML + Tailwind source
                </p>

                <p class="mt-1 text-xs leading-5 text-zinc-500">
                    Edit the same page HTML directly. Tailwind classes are preserved on allowed elements.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex w-fit items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100"
                    x-on:click="formatCode()"
                >
                    Format Code
                </button>

                <button
                    type="button"
                    class="inline-flex w-fit items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100"
                    x-on:click="toggleFullscreen()"
                    x-text="fullscreen ? 'Exit Full Screen' : 'Full Screen'"
                ></button>
            </div>
        </div>

        <textarea
            id="{{ $id }}-html"
            x-ref="code"
            wire:ignore
            rows="24"
            spellcheck="false"
            wrap="off"
            placeholder='<section class="bg-slate-900 px-6 py-20 text-white">&#10;    <div class="mx-auto max-w-6xl">&#10;        <h1 class="text-4xl font-bold">Page heading</h1>&#10;    </div>&#10;</section>'
            class="awcms-page-code-editor"
            x-on:input.debounce.120ms="syncFromCode()"
            x-on:blur="syncFromCode()"
        ></textarea>

        <div class="grid gap-3 lg:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-950">
                    Developer mode
                </p>

                <p class="mt-1 text-sm leading-6 text-emerald-800">
                    Build responsive hero sections, grids, cards and custom layouts with semantic HTML and Tailwind CSS.
                </p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-950">
                    Security
                </p>

                <p class="mt-1 text-sm leading-6 text-amber-800">
                    Scripts, iframes, forms, JavaScript event attributes and arbitrary inline CSS are removed before storage.
                    Visual Editor inline styling is limited to colour, background colour and text alignment.
                </p>
            </div>
        </div>
    </div>

    @error($modeModel)
        <p class="mt-3 text-sm font-medium text-red-600">
            {{ $message }}
        </p>
    @enderror

    @error($model)
        <p class="mt-3 text-sm font-medium text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>

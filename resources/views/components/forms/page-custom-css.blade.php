<section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
    <label for="page-custom-css" class="block text-base font-bold text-zinc-950">
        Custom CSS <span class="text-sm font-normal text-zinc-500">(optional)</span>
    </label>

    <p id="page-custom-css-help" class="mt-2 text-sm leading-6 text-zinc-600">
        Paste or write CSS for this page here, without &lt;style&gt; tags. Use the same class names as your HTML.
        Styles apply only inside this page content. Save the draft, then use Preview to check the result.
    </p>
    <p class="mt-1 text-xs leading-5 text-zinc-500">
        Each language version has its own CSS. New translations start with the source page's CSS.
        Responsive @media rules, hover states, pseudo-elements, @supports and @container are supported.
    </p>

    <textarea
        id="page-custom-css"
        wire:model="customCss"
        rows="14"
        maxlength="50000"
        spellcheck="false"
        autocapitalize="off"
        autocomplete="off"
        aria-describedby="page-custom-css-help"
        placeholder=".sos-courses-grid {&#10;    display: grid;&#10;    grid-template-columns: repeat(2, minmax(0, 1fr));&#10;    gap: 20px;&#10;}&#10;&#10;@media (max-width: 767px) {&#10;    .sos-courses-grid { grid-template-columns: 1fr; }&#10;}"
        class="mt-4 w-full resize-y rounded-xl border border-zinc-300 bg-zinc-950 p-4 font-mono text-sm leading-6 text-zinc-100 outline-none placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
    ></textarea>

    @error('customCss')
        <p class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
    @enderror
</section>

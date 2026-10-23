<dialog id="admin-confirmation-dialog" aria-labelledby="admin-confirmation-title"
    aria-describedby="admin-confirmation-message" aria-modal="true"
    class="fixed inset-0 m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-md overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl backdrop:bg-zinc-950/60">
    <div class="p-6 sm:p-7">
        <div class="mb-4 flex size-12 items-center justify-center rounded-full bg-blue-50 text-blue-700" aria-hidden="true">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" />
                <path d="M9.5 9a2.5 2.5 0 0 1 5 .5c0 1.5-2.5 2-2.5 4M12 16h.01" />
            </svg>
        </div>
        <h2 id="admin-confirmation-title" data-confirm-title class="text-xl font-bold text-zinc-950">Confirm action</h2>
        <p id="admin-confirmation-message" data-confirm-message class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-600">
            Are you sure you want to continue?
        </p>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="button" data-confirm-no autofocus
                class="min-w-24 rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-bold text-zinc-700 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-700">
                No, cancel
            </button>
            <button type="button" data-confirm-yes data-tone="primary"
                class="min-w-24 rounded-xl border border-transparent bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 data-[tone=danger]:bg-red-600 data-[tone=danger]:hover:bg-red-700 data-[tone=danger]:focus-visible:outline-red-600">
                Yes, continue
            </button>
        </div>
    </div>
</dialog>

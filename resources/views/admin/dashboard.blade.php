<x-layouts.admin :title="__('AWCMS Dashboard')">
    <div class="flex w-full flex-col gap-6">
        <section
            class="rounded-2xl border border-zinc-200
                   bg-white p-6 shadow-sm"
        >
            <p class="text-sm font-medium text-zinc-500">
                Army Website Content Management System
            </p>

            <h2 class="mt-2 text-2xl font-bold text-zinc-950">
                Welcome, {{ auth()->user()->name }}
            </h2>

            <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                Manage website pages, news, galleries, documents,
                menus, media and system settings from this secure area.
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach (['Pages', 'News', 'Galleries', 'Documents'] as $module)
                <article
                    class="rounded-2xl border border-zinc-200
                           bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-zinc-500">
                        {{ $module }}
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-950">
                        0
                    </p>
                </article>
            @endforeach
        </section>

        <section
            class="rounded-2xl border border-dashed
                   border-zinc-300 bg-white p-8"
        >
            <h2 class="text-lg font-semibold text-zinc-950">
                AWCMS Foundation Ready
            </h2>

            <p class="mt-2 text-sm text-zinc-600">
                Content management modules will be connected
                step by step.
            </p>
        </section>
    </div>
</x-layouts.admin>
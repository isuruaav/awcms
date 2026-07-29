<x-layouts.admin :title="__('Dashboard')">
    <div class="space-y-6">
        <x-admin.page-header
            title="AWCMS Dashboard"
            description="Manage website content, users, documents and publishing activities from this secure administration area."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card
                title="Pages"
                value="0"
                description="Published and draft pages"
            />

            <x-admin.stat-card
                title="News"
                value="0"
                description="Latest news articles"
            />

            <x-admin.stat-card
                title="Galleries"
                value="0"
                description="Photo albums and images"
            />

            <x-admin.stat-card
                title="Documents"
                value="0"
                description="PDFs and downloadable files"
            />
        </section>

        <section
            class="rounded-2xl border border-zinc-200
                   bg-white p-6 shadow-sm"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row
                       sm:items-start sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold text-zinc-950">
                        AWCMS Foundation Ready
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                        The secure administration foundation is operational.
                        CMS modules will now be connected one by one.
                    </p>
                </div>

                <x-admin.status-badge status="active" />
            </div>
        </section>

        <section
            class="rounded-2xl border border-dashed
                   border-zinc-300 bg-white p-6"
        >
            <h2 class="font-semibold text-zinc-950">
                Next Module
            </h2>

            <p class="mt-1 text-sm text-zinc-600">
                Users, roles and permissions management interface.
            </p>
        </section>
    </div>
</x-layouts.admin>
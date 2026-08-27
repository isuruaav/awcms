<x-layouts.admin :title="__('Dashboard')">
    <div class="space-y-6">
        <x-admin.page-header
            title="AWCMS Dashboard"
            description="Monitor content, publishing activity and website administration from one place."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-admin.stat-card title="Pages" :value="$pageCount" description="All page records" />
            <x-admin.stat-card title="News" :value="$newsCount" description="News articles" />
            <x-admin.stat-card title="Galleries" :value="$galleryCount" description="Photo galleries" />
            <x-admin.stat-card title="Documents" :value="$documentCount" description="PDF documents" />
            <x-admin.stat-card title="New Messages" :value="$newContactCount" description="Unread enquiries" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                    <h2 class="font-bold text-zinc-900">Recent Activity</h2>
                    <p class="mt-1 text-xs text-zinc-500">Latest security and content audit events.</p>
                </div>
                <div class="divide-y divide-zinc-100">
                    @forelse($recentActivity as $activity)
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-bold text-zinc-900">{{ $activity->event }}</p>
                                    <p class="mt-1 text-sm text-zinc-600">{{ $activity->description }}</p>
                                    <p class="mt-1 text-xs text-zinc-400">{{ $activity->actor_name ?: 'System' }}</p>
                                </div>
                                <span class="shrink-0 text-xs text-zinc-400">{{ $activity->created_at?->format('d M H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-10 text-center text-sm text-zinc-500">No audit activity yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-5">
                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-zinc-900">Quick Actions</h2>
                    <div class="mt-4 grid gap-2">
                        @can('pages.create')<a href="{{ route('admin.pages.create') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Create Page</a>@endcan
                        @can('news.create')<a href="{{ route('admin.news.create') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Create News Article</a>@endcan
                        @can('media.upload')<a href="{{ route('admin.media.upload') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Upload Media</a>@endcan
                        @can('documents.create')<a href="{{ route('admin.documents.create') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Create Document</a>@endcan
                        @can('menus.manage')<a href="{{ route('admin.menus.index') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Manage Navigation</a>@endcan
                        @can('settings.manage')<a href="{{ route('admin.site-settings.index') }}" wire:navigate class="rounded-xl border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-50">Site Settings</a>@endcan
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                    <h2 class="font-bold text-emerald-900">AWCMS Foundation Ready</h2>
                    <p class="mt-2 text-sm leading-6 text-emerald-800">Core content, media, navigation, settings, roles, redirects, contact messages and audit tools are connected.</p>
                </section>
            </div>
        </section>
    </div>
</x-layouts.admin>

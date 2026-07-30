<nav class="space-y-1 px-4 py-6">
    @can('dashboard.view')
        <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
            Dashboard
        </x-admin.nav-link>
    @endcan

    <div class="px-4 pb-2 pt-6 text-xs font-semibold
               uppercase tracking-wider text-zinc-500">
        Content Management
    </div>

    @can('pages.view')
        <x-admin.nav-link disabled>
            Pages
        </x-admin.nav-link>
    @endcan

    @can('news.view')
        <x-admin.nav-link disabled>
            News
        </x-admin.nav-link>
    @endcan

    @can('galleries.view')
        <x-admin.nav-link disabled>
            Galleries
        </x-admin.nav-link>
    @endcan

    @can('documents.view')
        <x-admin.nav-link disabled>
            Documents
        </x-admin.nav-link>
    @endcan

    @can('media.view')
        <x-admin.nav-link disabled>
            Media Library
        </x-admin.nav-link>
    @endcan

    <div class="px-4 pb-2 pt-6 text-xs font-semibold
               uppercase tracking-wider text-zinc-500">
        Administration
    </div>

    @can('users.view')
        <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
            Users
        </x-admin.nav-link>
    @endcan

    @can('roles.manage')
        <x-admin.nav-link disabled>
            Roles & Permissions
        </x-admin.nav-link>
    @endcan

    @can('menus.manage')
        <x-admin.nav-link disabled>
            Menus
        </x-admin.nav-link>
    @endcan

    @can('settings.manage')
        <x-admin.nav-link disabled>
            Site Settings
        </x-admin.nav-link>
    @endcan

    @can('audit.view')
        <x-admin.nav-link disabled>
            Activity Logs
        </x-admin.nav-link>
    @endcan
</nav>

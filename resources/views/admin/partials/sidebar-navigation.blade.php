<nav class="space-y-1 px-4 py-6">

    {{-- =====================================================
         DASHBOARD
    ====================================================== --}}
    @can('dashboard.view')
        <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
            Dashboard
        </x-admin.nav-link>
    @endcan


    {{-- =====================================================
         CONTENT MANAGEMENT
    ====================================================== --}}
    <div
        class="px-4 pb-2 pt-6
               text-xs font-semibold
               uppercase tracking-wider
               text-zinc-500">
        Content Management
    </div>


    {{-- Pages --}}
    @can('pages.view')
        <x-admin.nav-link :href="route('admin.pages.index')" :active="request()->routeIs('admin.pages.*')">
            Pages
        </x-admin.nav-link>
    @endcan


    {{-- Media Library --}}
    @can('media.view')
        <x-admin.nav-link :href="route('admin.media.index')" :active="request()->routeIs('admin.media.*')">
            Media Library
        </x-admin.nav-link>
    @endcan


    {{-- News --}}
    @can('news.view')
        <x-admin.nav-link :href="route('admin.news.index')" :active="request()->routeIs('admin.news.*')">
            News
        </x-admin.nav-link>
    @endcan


    {{-- Galleries --}}
    @can('galleries.view')
        <x-admin.nav-link :href="route('admin.galleries.index')" :active="request()->routeIs('admin.galleries.*')">
            Galleries
        </x-admin.nav-link>
    @endcan


    {{-- Documents --}}
    @can('documents.view')
        <x-admin.nav-link :href="route('admin.documents.index')" :active="request()->routeIs('admin.documents.index', 'admin.documents.create', 'admin.documents.edit')">
            Documents
        </x-admin.nav-link>
    @endcan


    {{-- Document Categories --}}
    @can('documents.categories.manage')
        <x-admin.nav-link :href="route('admin.documents.categories.index')" :active="request()->routeIs('admin.documents.categories.*')">
            Document Categories
        </x-admin.nav-link>
    @endcan


    {{-- =====================================================
         ADMINISTRATION
    ====================================================== --}}
    <div
        class="px-4 pb-2 pt-6
               text-xs font-semibold
               uppercase tracking-wider
               text-zinc-500">
        Administration
    </div>


    {{-- Users --}}
    @can('users.view')
        <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
            Users
        </x-admin.nav-link>
    @endcan


    {{-- Roles & Permissions --}}
    @can('roles.manage')
        <x-admin.nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
            Roles & Permissions
        </x-admin.nav-link>
    @endcan


    {{-- Menus --}}
    @can('menus.manage')
        <x-admin.nav-link :href="route('admin.menus.index')" :active="request()->routeIs('admin.menus.*')">
            Menus
        </x-admin.nav-link>
    @endcan


    {{-- Header & Footer --}}
    @can('theme-layouts.manage')
        <x-admin.nav-link :href="route('admin.theme-layouts.index')" :active="request()->routeIs('admin.theme-layouts.*')">
            Header &amp; Footer
        </x-admin.nav-link>
    @endcan

    {{-- Hero Slider --}}
    @can('settings.manage')
        <x-admin.nav-link :href="route('admin.hero-slides.index')" :active="request()->routeIs('admin.hero-slides.*')">
            Hero Slider
        </x-admin.nav-link>
    @endcan

    {{-- School Leadership --}}
    @can('settings.manage')
        <x-admin.nav-link :href="route('admin.school-leaders.index')" :active="request()->routeIs('admin.school-leaders.*')">
            School Leadership
        </x-admin.nav-link>
    @endcan

    {{-- Past Commandants --}}
    @can('settings.manage')
        <x-admin.nav-link :href="route('admin.past-commandants.index')" :active="request()->routeIs('admin.past-commandants.*')">
            Past Commandants
        </x-admin.nav-link>
    @endcan



    {{-- Site Settings --}}
    @can('settings.manage')
        <x-admin.nav-link :href="route('admin.site-settings.index')" :active="request()->routeIs('admin.site-settings.*')">
            Site Settings
        </x-admin.nav-link>
    @endcan


    {{-- Contact Messages --}}
    @can('contacts.manage')
        <x-admin.nav-link :href="route('admin.contact-messages.index')" :active="request()->routeIs('admin.contact-messages.*')">
            Contact Messages
        </x-admin.nav-link>
    @endcan


    {{-- Redirects --}}
    @can('redirects.manage')
        <x-admin.nav-link :href="route('admin.redirects.index')" :active="request()->routeIs('admin.redirects.*')">
            Redirects
        </x-admin.nav-link>
    @endcan


    {{-- Activity Logs --}}
    @can('audit.view')
        <x-admin.nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
            Activity Logs
        </x-admin.nav-link>
    @endcan

</nav>

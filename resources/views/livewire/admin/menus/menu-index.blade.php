<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Site Management</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950">Menu Builder</h1>
            <p class="mt-1 text-sm text-zinc-500">Build primary, footer and custom navigation with nested content links.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="space-y-5">
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="font-bold text-zinc-900">Create Menu</h2>
                <div class="mt-4 space-y-3">
                    <input wire:model="menuName" type="text" maxlength="150" placeholder="Menu name" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                    <input wire:model="menuLocation" type="text" maxlength="80" placeholder="Location: primary" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                    <label class="flex items-center gap-2 text-sm text-zinc-700"><input type="checkbox" wire:model="menuIsActive"> Active</label>
                    <button type="button" wire:click="createMenu" class="w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800">Create Menu</button>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4"><h2 class="font-bold text-zinc-900">Menus</h2></div>
                <div class="divide-y divide-zinc-100">
                    @forelse($menus as $menu)
                        <button type="button" wire:click="selectMenu({{ $menu->id }})" class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left {{ $selectedMenuId === $menu->id ? 'bg-emerald-50' : 'hover:bg-zinc-50' }}">
                            <span><span class="block text-sm font-bold text-zinc-900">{{ $menu->name }}</span><span class="mt-1 block text-xs text-zinc-500">{{ $menu->location }}</span></span>
                            <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-bold text-zinc-600">{{ $menu->items_count }}</span>
                        </button>
                    @empty
                        <p class="px-5 py-8 text-sm text-zinc-500">No menus yet.</p>
                    @endforelse
                </div>
            </section>
        </aside>

        <main class="space-y-6">
            @if($selectedMenu)
                <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1"><p class="text-xs text-zinc-500">Location: {{ $selectedMenu->location }}</p><div class="mt-2 flex flex-col gap-2 sm:flex-row"><input wire:model="selectedMenuName" type="text" maxlength="150" class="min-w-0 flex-1 rounded-xl border border-zinc-300 px-3 py-2 text-sm font-bold text-zinc-900"><button type="button" wire:click="renameMenu" class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm font-bold text-zinc-700">Rename</button></div></div>
                        <div class="flex gap-2"><button type="button" wire:click="toggleMenu" class="rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm font-bold text-zinc-700 hover:bg-zinc-50">{{ $selectedMenu->is_active ? 'Deactivate' : 'Activate' }}</button><button type="button" wire:click="deleteMenu" wire:confirm="Delete this menu? Existing menu items will no longer be used." class="rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-700">Delete</button></div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4"><h2 class="font-bold text-zinc-900">{{ $editingItemId ? 'Edit Menu Item' : 'Add Menu Item' }}</h2></div>
                    <div class="grid gap-4 p-6 md:grid-cols-2">
                        <div class="md:col-span-2 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-bold text-zinc-900">Menu labels</p>
                            <p class="mt-1 text-xs text-zinc-500">English is required. Empty Sinhala or Tamil labels automatically fall back to English.</p>
                            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">English</label><input wire:model="label" type="text" maxlength="150" lang="en" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"></div>
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">සිංහල</label><input wire:model="sinhalaLabel" type="text" maxlength="150" lang="si" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"></div>
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">தமிழ்</label><input wire:model="tamilLabel" type="text" maxlength="150" lang="ta" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"></div>
                            </div>
                        </div>
                        <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">Link Type</label><select wire:model.live="type" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="url">Custom URL</option><option value="route">System Route</option><option value="page">Page</option><option value="news">News</option><option value="gallery">Gallery</option><option value="document">Document</option></select></div>

                        @if($type === 'url')
                            <div class="md:col-span-2 grid gap-4 lg:grid-cols-3">
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">English URL / Path</label><input wire:model="url" type="text" placeholder="/about or https://example.com" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">Sinhala URL / Path</label><input wire:model="sinhalaUrl" type="text" placeholder="/si/pages/about-us" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                                <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">Tamil URL / Path</label><input wire:model="tamilUrl" type="text" placeholder="/ta/pages/about-us" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                            </div>
                        @elseif($type === 'route')
                            <div class="md:col-span-2"><label class="mb-1.5 block text-sm font-semibold text-zinc-700">Route</label><select wire:model="routeName" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Select route</option><option value="home">Home</option><option value="news.index">News</option><option value="galleries.index">Galleries</option><option value="documents.index">Documents</option><option value="contact.create">Contact</option></select></div>
                        @elseif($type === 'page')
                            <div class="md:col-span-2"><select wire:model="referenceId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Select page</option>@foreach($pages as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach</select></div>
                        @elseif($type === 'news')
                            <div class="md:col-span-2"><select wire:model="referenceId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Select news</option>@foreach($newsItems as $news)<option value="{{ $news->id }}">{{ $news->title }}</option>@endforeach</select></div>
                        @elseif($type === 'gallery')
                            <div class="md:col-span-2"><select wire:model="referenceId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Select gallery</option>@foreach($galleries as $gallery)<option value="{{ $gallery->id }}">{{ $gallery->title }}</option>@endforeach</select></div>
                        @elseif($type === 'document')
                            <div class="md:col-span-2"><select wire:model="referenceId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Select document</option>@foreach($documents as $document)<option value="{{ $document->id }}">{{ $document->title }}</option>@endforeach</select></div>
                        @endif

                        <div><label class="mb-1.5 block text-sm font-semibold text-zinc-700">Parent Item</label><select wire:model="parentId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><option value="">Top level</option>@foreach($selectedMenu->rootItems as $root)<option value="{{ $root->id }}">{{ $root->labelForLocale('en') }}</option>@endforeach</select></div>
                        <div class="flex items-center gap-5 pt-7 text-sm text-zinc-700"><label class="flex items-center gap-2"><input type="checkbox" wire:model="openInNewTab"> New tab</label><label class="flex items-center gap-2"><input type="checkbox" wire:model="itemIsActive"> Active</label></div>
                        <div class="md:col-span-2"><button type="button" wire:click="saveItem" class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-800">{{ $editingItemId ? 'Save Item' : 'Add Item' }}</button>@if($editingItemId)<button type="button" wire:click="cancelItemEdit" class="ml-2 rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-bold text-zinc-700">Cancel</button>@endif</div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4"><h2 class="font-bold text-zinc-900">Menu Structure</h2><p class="mt-1 text-xs text-zinc-500">Use arrows to change order. Child items remain grouped under their parent.</p></div>
                    <div class="divide-y divide-zinc-100">
                        @forelse($selectedMenu->rootItems as $item)
                            <div wire:key="menu-item-{{ $item->id }}" draggable="true" x-data="{ dragging: false }" x-on:dragstart="dragging = true; event.dataTransfer.setData('text/plain', '{{ $item->id }}')" x-on:dragend="dragging = false" x-on:dragover.prevent x-on:drop.prevent="const source = Number(event.dataTransfer.getData('text/plain')); if (source) { $wire.placeBefore(source, {{ $item->id }}); }" class="p-5 transition" :class="dragging ? 'opacity-50' : 'opacity-100'">
                                <div class="flex items-center justify-between gap-4">
                                    <div><p class="font-bold text-zinc-900">{{ $item->labelForLocale('en') }}</p><p class="text-xs text-zinc-500"><span lang="si">{{ $item->labelForLocale('si') }}</span> · <span lang="ta">{{ $item->labelForLocale('ta') }}</span></p><p class="mt-1 text-xs text-zinc-400">{{ $item->type }} · {{ $item->is_active ? 'Active' : 'Inactive' }}</p></div>
                                    <div class="flex gap-2"><button type="button" wire:click="editItem({{ $item->id }})" class="rounded-lg border px-2 py-1 text-xs font-bold">Edit</button><button type="button" wire:click="moveItem({{ $item->id }}, 'up')" class="rounded-lg border px-2 py-1 text-xs">↑</button><button type="button" wire:click="moveItem({{ $item->id }}, 'down')" class="rounded-lg border px-2 py-1 text-xs">↓</button><button type="button" wire:click="deleteItem({{ $item->id }})" wire:confirm="Delete this menu item and its children?" class="rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Delete</button></div>
                                </div>
                                @if($item->children->isNotEmpty())
                                    <div class="mt-3 space-y-2 border-l-2 border-emerald-200 pl-5">
                                        @foreach($item->children as $child)
                                            <div wire:key="menu-child-{{ $child->id }}" class="flex items-center justify-between rounded-xl bg-zinc-50 px-4 py-3"><span><span class="text-sm font-semibold text-zinc-800">{{ $child->labelForLocale('en') }}</span><span class="ml-2 text-xs text-zinc-500" lang="si">{{ $child->labelForLocale('si') }}</span><span class="ml-2 text-xs text-zinc-500" lang="ta">{{ $child->labelForLocale('ta') }}</span><span class="ml-2 text-xs text-zinc-400">{{ $child->type }}</span></span><div class="flex gap-2"><button type="button" wire:click="editItem({{ $child->id }})" class="rounded border px-2 text-xs font-bold">Edit</button><button type="button" wire:click="moveItem({{ $child->id }}, 'up')" class="rounded border px-2 text-xs">↑</button><button type="button" wire:click="moveItem({{ $child->id }}, 'down')" class="rounded border px-2 text-xs">↓</button><button type="button" wire:click="deleteItem({{ $child->id }})" wire:confirm="Delete this menu item?" class="text-xs font-bold text-red-600">Delete</button></div></div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="px-6 py-12 text-center text-sm text-zinc-500">No items in this menu.</p>
                        @endforelse
                    </div>
                </section>
            @else
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center text-zinc-500">Create a menu to begin.</div>
            @endif
        </main>
    </div>
</div>

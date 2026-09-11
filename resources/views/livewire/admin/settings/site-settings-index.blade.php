<div>
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Site Management</p>
        <h1 class="mt-1 text-2xl font-black text-zinc-950">Site Settings</h1>
        <p class="mt-1 text-sm text-zinc-500">Manage identity, contact details, theme family, social links and
            maintenance mode.</p>
    </div>

    @if (session('status'))
        <div
            class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="mt-6 space-y-6">
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">Identity & Theme</h2>
            </div>
            <div class="grid gap-5 p-6 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-semibold">Site Name</label><input wire:model="siteName"
                        type="text" maxlength="180"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Tagline</label><input wire:model="siteTagline"
                        type="text" maxlength="255"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Theme Family</label><select
                        wire:model="themeFamily" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                        <option value="army-unit">Army Unit</option>
                        <option value="training-school">Training School</option>
                        <option value="sfhq">SFHQ</option>
                        <option value="establishment">Establishment</option>
                    </select></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="mb-1.5 block text-sm font-semibold">Primary Colour</label><input
                            wire:model="primaryColor" type="color"
                            class="h-11 w-full rounded-xl border border-zinc-300 bg-white p-1"></div>
                    <div><label class="mb-1.5 block text-sm font-semibold">Accent Colour</label><input
                            wire:model="accentColor" type="color"
                            class="h-11 w-full rounded-xl border border-zinc-300 bg-white p-1"></div>
                </div>
                <div><label class="mb-1.5 block text-sm font-semibold">Logo Media</label><select
                        wire:model="logoMediaId" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                        <option value="">No logo</option>
                        @foreach ($mediaAssets as $asset)
                            <option value="{{ $asset->id }}">#{{ $asset->id }} —
                                {{ $asset->title ?: $asset->original_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="mb-1.5 block text-sm font-semibold">Favicon Media</label><select
                        wire:model="faviconMediaId"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                        <option value="">No favicon</option>
                        @foreach ($mediaAssets as $asset)
                            <option value="{{ $asset->id }}">#{{ $asset->id }} —
                                {{ $asset->title ?: $asset->original_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">Contact & Footer</h2>
            </div>
            <div class="grid gap-5 p-6 lg:grid-cols-2">
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Address</label>
                    <textarea wire:model="address" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></textarea>
                </div>
                <div><label class="mb-1.5 block text-sm font-semibold">Primary Phone</label><input
                        wire:model="phonePrimary" type="text"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Secondary Phone</label><input
                        wire:model="phoneSecondary" type="text"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Public Email</label><input wire:model="email"
                        type="email" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Map URL</label><input wire:model="mapUrl"
                        type="url" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Footer Text</label>
                    <textarea wire:model="footerText" rows="3" maxlength="2000"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">Commander / Head of Establishment</h2>
            </div>
            <div class="grid gap-5 p-6 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-semibold">Name</label><input wire:model="commanderName"
                        type="text" maxlength="180"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">Title / Appointment</label><input
                        wire:model="commanderTitle" type="text" maxlength="180"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Profile
                        Image</label><select wire:model="commanderImageMediaId"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
                        <option value="">No image</option>
                        @foreach ($mediaAssets as $asset)
                            <option value="{{ $asset->id }}">#{{ $asset->id }} —
                                {{ $asset->title ?: $asset->original_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2"><label class="mb-1.5 block text-sm font-semibold">Message</label>
                    <textarea wire:model="commanderMessage" rows="6" maxlength="5000"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <h2 class="font-bold text-zinc-900">Default SEO</h2>
            </div>
            <div class="grid gap-5 p-6 lg:grid-cols-2">
                <div><label class="mb-1.5 block text-sm font-semibold">SEO Title</label><input
                        wire:model="defaultSeoTitle" type="text" maxlength="255"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></div>
                <div><label class="mb-1.5 block text-sm font-semibold">SEO Description</label>
                    <textarea wire:model="defaultSeoDescription" rows="3" maxlength="320"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-3"><input wire:model="maintenanceMode" type="checkbox" class="mt-1">
                <div class="flex-1">
                    <h2 class="font-bold text-amber-900">Maintenance Mode</h2>
                    <p class="mt-1 text-sm text-amber-800">When enabled, public visitors receive a 503 maintenance
                        page. Authenticated administrators can still preview the site.</p>
                    <textarea wire:model="maintenanceMessage" rows="3" maxlength="2000" placeholder="Optional maintenance message"
                        class="mt-4 w-full rounded-xl border border-amber-300 bg-white px-3 py-2.5 text-sm"></textarea>
                </div>
            </div>
        </section>

        <div class="flex justify-end"><button type="submit"
                class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800">Save Site
                Settings</button></div>
    </form>

    <section class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
            <h2 class="font-bold text-zinc-900">Social Links</h2>
        </div>
        <div class="grid gap-3 p-6 sm:grid-cols-3"><input wire:model="socialPlatform" type="text"
                placeholder="Facebook" class="rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><input
                wire:model="socialLabel" type="text" placeholder="Label (optional)"
                class="rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"><input wire:model="socialUrl"
                type="url" placeholder="https://..."
                class="rounded-xl border border-zinc-300 px-3 py-2.5 text-sm">
            <div class="sm:col-span-3"><button type="button" wire:click="addSocialLink"
                    class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white">Add Social Link</button>
            </div>
        </div>
        <div class="divide-y border-t">
            @forelse ($socialLinks as $link)
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div>
                        <p class="text-sm font-bold text-zinc-900">{{ $link->platform }}</p>
                        <p class="max-w-sm truncate text-xs text-zinc-500">{{ $link->url }}</p>
                    </div><button type="button" wire:click="deleteSocialLink({{ $link->id }})"
                        wire:confirm="Delete this social link?" class="text-xs font-bold text-red-600">Delete</button>
            </div>@empty<p class="px-6 py-8 text-sm text-zinc-500">No social links.</p>
            @endforelse
        </div>
    </section>
</div>

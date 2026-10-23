<div class="space-y-6">
    @php($canUpdate = \Illuminate\Support\Facades\Gate::allows('school-leaders.update'))

    <header class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">School Leadership</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950">Leadership Profiles</h1>
            <p class="mt-2 max-w-3xl text-sm text-zinc-500">Manage English and Sinhala profiles for present and past appointment holders. Saved profiles stay with each officer's appointment record when their service period ends.</p>
        </div>
        <a href="{{ route('admin.school-leaders.index') }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700">Manage Appointments</a>
    </header>

    @if (session('status'))
        <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        <label for="profile-leader" class="block text-sm font-bold text-zinc-900">Select a present or past appointment holder</label>
        <select id="profile-leader" wire:model.live="selectedLeaderId" class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm" @disabled($leaders->isEmpty())>
            @forelse ($leaders as $leader)
                <option value="{{ $leader->id }}">{{ $leader->appointmentLabel() }} — {{ $leader->name_en }} ({{ $leader->isCurrentAppointment() ? 'Present' : 'Past' }}{{ $leader->end_date ? ' · '.$leader->end_date->format('d M Y') : '' }})</option>
            @empty
                <option value="">Create a leadership appointment first</option>
            @endforelse
        </select>
        <p class="mt-2 text-xs text-zinc-500">Portraits, names, ranks and appointment dates are edited under Manage Appointments. Enabled profiles remain viewable after an appointment becomes past. Use Update Preview to review content before enabling.</p>
    </section>

    @if ($selectedLeader)
        <div wire:key="leader-profile-editor-{{ $editingId }}" x-data="{ language: 'en' }" class="space-y-6">
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-6">
                <div>
                    <h2 class="font-bold text-zinc-950">{{ $selectedLeader->name_en }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $selectedLeader->appointmentLabel() }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span @class(['rounded-full px-3 py-1.5 text-xs font-bold', 'bg-emerald-100 text-emerald-800' => $selectedLeader->profile_enabled, 'bg-zinc-100 text-zinc-600' => ! $selectedLeader->profile_enabled])>{{ $selectedLeader->profile_enabled ? 'Profile enabled' : 'Profile disabled' }}</span>
                    @if ($selectedLeader->hasPublicProfile())
                        <x-admin.icon-action label="View English" icon="eye" tone="info"
                            href="{{ $selectedLeader->profileUrlForLocale('en') }}" target="_blank" rel="noopener"
                        />
                        <x-admin.icon-action label="සිංහල බලන්න" icon="eye" tone="info"
                            href="{{ $selectedLeader->profileUrlForLocale('si') }}" target="_blank" rel="noopener"
                        />
                    @endif
                </div>
            </section>

            @if ($canUpdate)
                <form wire:submit="save" class="space-y-6">
                    <label class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <input type="checkbox" wire:model="profileEnabled" class="mt-1 rounded border-emerald-300 text-emerald-700 focus:ring-emerald-600">
                        <span><strong class="block text-sm text-emerald-950">Enable public profile / පැතිකඩ සක්‍රිය කරන්න</strong><span class="mt-1 block text-xs text-emerald-800">When enabled, Read More links appear on the public website. Add content in both languages before enabling. Disabling keeps your saved content.</span></span>
                    </label>

                    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 p-4">
                            <div class="flex gap-2" role="tablist" aria-label="Profile language">
                                <button type="button" role="tab" :aria-selected="language === 'en'" @click="language = 'en'" :class="language === 'en' ? 'bg-blue-700 text-white' : 'bg-white text-zinc-700'" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-bold">English</button>
                                <button type="button" role="tab" :aria-selected="language === 'si'" @click="language = 'si'" :class="language === 'si' ? 'bg-blue-700 text-white' : 'bg-white text-zinc-700'" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-bold">සිංහල</button>
                            </div>
                            <span class="text-xs font-semibold text-zinc-500">HTML + Tailwind CSS</span>
                        </div>

                        @foreach (['en' => 'English', 'si' => 'සිංහල'] as $locale => $label)
                            <div x-show="language === '{{ $locale }}'" @if ($locale === 'si') x-cloak @endif class="space-y-3 p-5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <label for="profile-html-{{ $locale }}" class="text-sm font-bold text-zinc-900">{{ $label }} profile HTML</label>
                                    <button type="button" wire:click="insertTemplate('{{ $locale }}')" class="text-xs font-bold text-blue-700 underline">Insert starter template</button>
                                </div>
                                <textarea id="profile-html-{{ $locale }}" wire:model="{{ $locale === 'en' ? 'profileHtmlEn' : 'profileHtmlSi' }}" rows="18" spellcheck="false" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 p-4 font-mono text-sm leading-6 text-zinc-100" placeholder="<section class=&quot;rounded-2xl bg-blue-50 p-6 md:p-10&quot;>...</section>"></textarea>
                            </div>
                        @endforeach
                        <p class="border-t border-zinc-200 px-5 py-4 text-xs leading-6 text-zinc-500">Use responsive Tailwind classes, headings, paragraphs, images, lists and tables. Tailwind styles are generated when you preview or save, including new classes. Paste image URLs from the Media Library.</p>
                    </section>

                    <section class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                        <label for="profile-css" class="block text-sm font-bold text-zinc-900">Custom CSS — shared by both languages</label>
                        <p class="text-xs text-zinc-500">Style classes used in your HTML. The site's colour variables, such as var(--blue-700), are available.</p>
                        <textarea id="profile-css" wire:model="profileCss" rows="8" spellcheck="false" class="w-full rounded-xl border border-zinc-700 bg-zinc-950 p-4 font-mono text-sm leading-6 text-zinc-100" placeholder=".profile-heading { color: var(--blue-700); }"></textarea>
                    </section>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <span wire:loading wire:target="save,preview" class="text-sm font-semibold text-emerald-700">Generating profile styles…</span>
                        <x-admin.icon-action label="Update Preview" icon="arrow-path" tone="info"
                            type="button" wire:click="preview" wire:loading.attr="disabled"
                        />
                        <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-50">Save Profile</button>
                    </div>
                </form>
            @else
                <p class="rounded-xl border border-zinc-200 bg-white p-5 text-sm text-zinc-600">Read-only access. Profile editing requires School Leadership update permission.</p>
            @endif

            @if ($previewEn !== '' || $previewSi !== '')
                <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <h2 class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 font-bold text-zinc-900">Profile Preview</h2>
                    <iframe x-show="language === 'en'" title="English profile preview" sandbox="allow-same-origin" srcdoc="{{ $previewEn }}" class="h-[36rem] w-full border-0"></iframe>
                    <iframe x-show="language === 'si'" x-cloak title="Sinhala profile preview" sandbox="allow-same-origin" srcdoc="{{ $previewSi }}" class="h-[36rem] w-full border-0"></iframe>
                </section>
            @endif
        </div>
    @endif
</div>

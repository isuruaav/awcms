<div class="space-y-6">
    @php
        $canUpdate = \Illuminate\Support\Facades\Gate::allows(
            'school-leaders.update'
        );
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                School Leadership
            </p>

            <h1 class="mt-1 text-2xl font-black text-zinc-950">
                Appointment Types
            </h1>

            <p class="mt-1 max-w-3xl text-sm text-zinc-500">
                Create and manage leadership appointment categories.
                Active appointment types become available when adding leadership records.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.school-leaders.index') }}"
                class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-bold text-zinc-700 hover:bg-zinc-50"
            >
                ← School Leadership
            </a>

            @if ($canUpdate)
                <button
                    type="button"
                    wire:click="startCreate"
                    class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800"
                >
                    + New Appointment Type
                </button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-bold">
                Please correct the following errors:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div @class([
        'grid min-w-0 gap-6',
        'xl:grid-cols-[minmax(0,1fr)_420px]' => $canUpdate,
    ])>
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                <div>
                    <h2 class="font-bold text-zinc-900">
                        Appointment categories
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Existing and future School Leadership appointments.
                    </p>
                </div>

                <span class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-bold text-zinc-700">
                    {{ $positions->count() }} types
                </span>
            </div>

            <div class="divide-y divide-zinc-200">
                @forelse ($positions as $position)
                    <article
                        wire:key="position-{{ $position->id }}"
                        class="p-5"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-zinc-950">
                                        {{ $position->name_en }}
                                    </h3>

                                    @if ($position->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="rounded-full bg-zinc-200 px-2 py-0.5 text-[10px] font-black uppercase text-zinc-600">
                                            Inactive
                                        </span>
                                    @endif

                                    @if ($position->show_on_home)
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase text-blue-700">
                                            Homepage
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-zinc-500" lang="si">
                                    {{ $position->name_si }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-zinc-600">
                                    <span class="rounded-lg bg-zinc-100 px-2.5 py-1">
                                        {{ $position->rank_group->label() }}
                                    </span>

                                    <span class="rounded-lg bg-zinc-100 px-2.5 py-1">
                                        Sort: {{ $position->sort_order }}
                                    </span>

                                    <span class="rounded-lg bg-zinc-100 px-2.5 py-1">
                                        {{ $position->leaders_count }}
                                        {{ $position->leaders_count === 1 ? 'record' : 'records' }}
                                    </span>
                                </div>

                                <p class="mt-2 text-[11px] font-mono text-zinc-400">
                                    {{ $position->key }}
                                </p>
                            </div>

                            @if ($canUpdate)
                                <button
                                    type="button"
                                    wire:click="edit({{ $position->id }})"
                                    class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800"
                                >
                                    Edit
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <p class="font-bold text-zinc-700">
                            No appointment types have been created.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($canUpdate)
            <aside>
                <form
                    wire:submit="save"
                    class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm xl:sticky xl:top-6"
                >
                    <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4">
                        <h2 class="font-bold text-zinc-900">
                            {{ $editingId === null
                                ? 'New appointment type'
                                : 'Edit appointment type' }}
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            Configure the appointment and allowed rank group.
                        </p>
                    </div>

                    <div class="space-y-5 p-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-zinc-800">
                                English name
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                wire:model="nameEn"
                                type="text"
                                class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"
                                placeholder="e.g. Training Officer"
                            >

                            @error('nameEn')
                                <p class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-zinc-800">
                                Sinhala name
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                wire:model="nameSi"
                                type="text"
                                class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"
                            >

                            @error('nameSi')
                                <p class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-zinc-800">
                                Rank group
                                <span class="text-red-600">*</span>
                            </label>

                            <select
                                wire:model="rankGroup"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Select rank group
                                </option>

                                @foreach ($rankGroups as $group)
                                    <option value="{{ $group->value }}">
                                        {{ $group->label() }}
                                    </option>
                                @endforeach
                            </select>

                            @error('rankGroup')
                                <p class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-bold text-zinc-800">
                                Sort order
                            </label>

                            <input
                                wire:model="sortOrder"
                                type="number"
                                min="1"
                                max="9999"
                                class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm"
                            >

                            @error('sortOrder')
                                <p class="mt-1 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                            <input
                                wire:model="showOnHome"
                                type="checkbox"
                                class="mt-0.5 size-4 rounded border-zinc-300 text-blue-700"
                            >

                            <span>
                                <span class="block text-sm font-black text-zinc-900">
                                    Show present holder on homepage
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-zinc-500">
                                    Only the current holder of this appointment will appear on the homepage.
                                </span>
                            </span>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <input
                                wire:model="isActive"
                                type="checkbox"
                                class="mt-0.5 size-4 rounded border-zinc-300 text-emerald-700"
                            >

                            <span>
                                <span class="block text-sm font-black text-zinc-900">
                                    Active appointment type
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-zinc-500">
                                    Inactive types will not be available when creating new leadership appointments.
                                </span>
                            </span>
                        </label>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white hover:bg-emerald-800"
                        >
                            {{ $editingId === null
                                ? 'Create Appointment Type'
                                : 'Update Appointment Type' }}
                        </button>
                    </div>
                </form>
            </aside>
        @endif
    </div>
</div>
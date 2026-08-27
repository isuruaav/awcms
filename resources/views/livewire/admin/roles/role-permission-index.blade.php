<div class="space-y-6">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div>
        <p
            class="text-xs font-bold
                   uppercase tracking-[0.18em]
                   text-emerald-700"
        >
            Administration
        </p>

        <h1
            class="mt-1
                   text-2xl font-black
                   text-zinc-950"
        >
           Roles &amp; Permissions
        </h1>

        <p
            class="mt-1
                   max-w-3xl
                   text-sm leading-6
                   text-zinc-500"
        >
            Manage role capabilities while keeping the
            Super Administrator system role protected.
        </p>
    </div>


    {{-- =====================================================
         FLASH MESSAGE
    ====================================================== --}}
    @if (session('status'))
        <div
            class="rounded-xl
                   border border-emerald-200
                   bg-emerald-50
                   px-4 py-3
                   text-sm font-semibold
                   text-emerald-800"
        >
            {{ session('status') }}
        </div>
    @endif


    {{-- =====================================================
         ERRORS
    ====================================================== --}}
    @if ($errors->any())
        <div
            class="rounded-xl
                   border border-red-200
                   bg-red-50
                   px-4 py-3
                   text-sm
                   text-red-700"
        >
            <p class="font-bold">
                The action could not be completed.
            </p>

            <ul
                class="mt-2
                       list-disc
                       space-y-1
                       pl-5"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- =====================================================
         MAIN LAYOUT
    ====================================================== --}}
    <div
        class="grid
               min-w-0
               gap-6
               lg:grid-cols-[280px_minmax(0,1fr)]"
    >

        {{-- =================================================
             LEFT SIDEBAR
        ================================================== --}}
        <aside
            class="min-w-0
                   space-y-5"
        >

            {{-- CREATE ROLE --}}
            <section
                class="rounded-2xl
                       border border-zinc-200
                       bg-white
                       p-5
                       shadow-sm"
            >
                <h2
                    class="font-bold
                           text-zinc-900"
                >
                    Create Role
                </h2>

                <p
                    class="mt-1
                           text-xs leading-5
                           text-zinc-500"
                >
                    Create an additional CMS role and then
                    assign only the permissions it requires.
                </p>

                <div
                    class="mt-4
                           flex flex-col
                           gap-2
                           sm:flex-row
                           lg:flex-col
                           2xl:flex-row"
                >
                    <input
                        type="text"
                        wire:model="newRoleName"
                        maxlength="100"
                        placeholder="Role name"
                        class="min-w-0
                               w-full
                               flex-1
                               rounded-xl
                               border border-zinc-300
                               bg-white
                               px-3 py-2.5
                               text-sm
                               text-zinc-900
                               shadow-sm
                               outline-none
                               transition
                               focus:border-emerald-500
                               focus:ring-2
                               focus:ring-emerald-100"
                    >

                    <button
                        type="button"
                        wire:click="createRole"
                        wire:loading.attr="disabled"
                        wire:target="createRole"
                        class="inline-flex
                               shrink-0
                               items-center
                               justify-center
                               rounded-xl
                               bg-emerald-700
                               px-4 py-2.5
                               text-sm font-bold
                               text-white
                               transition
                               hover:bg-emerald-800
                               disabled:cursor-not-allowed
                               disabled:opacity-60"
                    >
                        <span
                            wire:loading.remove
                            wire:target="createRole"
                        >
                            Add
                        </span>

                        <span
                            wire:loading
                            wire:target="createRole"
                        >
                            Adding...
                        </span>
                    </button>
                </div>

                @error('newRoleName')
                    <p
                        class="mt-2
                               text-sm font-medium
                               text-red-600"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </section>


            {{-- ROLE LIST --}}
            <section
                class="overflow-hidden
                       rounded-2xl
                       border border-zinc-200
                       bg-white
                       shadow-sm"
            >
                <div
                    class="border-b
                           border-zinc-200
                           bg-zinc-50
                           px-5 py-4"
                >
                    <h2
                        class="font-bold
                               text-zinc-900"
                    >
                        Roles
                    </h2>

                    <p
                        class="mt-1
                               text-xs
                               text-zinc-500"
                    >
                        Select a role to review its permissions.
                    </p>
                </div>

                <div class="divide-y divide-zinc-100">
                    @forelse ($roles as $role)
                        <button
                            type="button"
                            wire:key="role-{{ $role->id }}"
                            wire:click="selectRole({{ $role->id }})"
                            class="flex
                                   w-full
                                   min-w-0
                                   items-center
                                   justify-between
                                   gap-3
                                   px-5 py-4
                                   text-left
                                   transition
                                   {{ $selectedRoleId === $role->id
                                       ? 'bg-emerald-50'
                                       : 'hover:bg-zinc-50' }}"
                        >
                            <span
                                class="min-w-0
                                       break-words
                                       text-sm font-bold
                                       text-zinc-900"
                            >
                                {{ $role->name }}
                            </span>

                            <span
                                class="shrink-0
                                       whitespace-nowrap
                                       text-xs
                                       text-zinc-500"
                            >
                                {{ $role->users_count }}
                                {{ $role->users_count === 1 ? 'user' : 'users' }}
                            </span>
                        </button>
                    @empty
                        <div
                            class="px-5 py-8
                                   text-center
                                   text-sm
                                   text-zinc-500"
                        >
                            No roles found.
                        </div>
                    @endforelse
                </div>
            </section>

        </aside>


        {{-- =================================================
             PERMISSIONS PANEL
        ================================================== --}}
        <section
            class="min-w-0
                   overflow-hidden
                   rounded-2xl
                   border border-zinc-200
                   bg-white
                   shadow-sm"
        >

            {{-- PANEL HEADER --}}
            <div
                class="border-b
                       border-zinc-200
                       bg-zinc-50
                       px-5 py-4
                       sm:px-6"
            >
                <h2
                    class="break-words
                           font-bold
                           text-zinc-900"
                >
                    {{ $selectedRole?->name ?? 'Select a role' }}
                </h2>

                @if ($selectedRole)
                    @if ($selectedRole->name === 'Super Administrator')
                        <p
                            class="mt-1
                                   text-xs leading-5
                                   text-amber-700"
                        >
                            Protected system role. Its permissions
                            are synchronised from the permission seeder.
                        </p>
                    @else
                        <p
                            class="mt-1
                                   text-xs leading-5
                                   text-zinc-500"
                        >
                            Select the capabilities available to this role.
                        </p>
                    @endif
                @else
                    <p
                        class="mt-1
                               text-xs
                               text-zinc-500"
                    >
                        Choose a role from the list to manage permissions.
                    </p>
                @endif
            </div>


            @if ($selectedRole)

                {{-- PERMISSION GROUPS --}}
                <div
                    class="grid
                           min-w-0
                           grid-cols-1
                           gap-5
                           p-4
                           sm:grid-cols-2
                           sm:p-6
                           2xl:grid-cols-3"
                >
                    @foreach ($groupedPermissions as $group => $permissions)

                        <article
                            wire:key="permission-group-{{ \Illuminate\Support\Str::slug($group) }}"
                            class="min-w-0
                                   overflow-hidden
                                   rounded-xl
                                   border border-zinc-200
                                   bg-white
                                   p-4"
                        >
                            <div
                                class="border-b
                                       border-zinc-100
                                       pb-3"
                            >
                                <h3
                                    class="break-words
                                           text-sm font-black
                                           text-zinc-900"
                                >
                                    {{ $group }}
                                </h3>

                                <p
                                    class="mt-1
                                           text-xs
                                           text-zinc-400"
                                >
                                    {{ $permissions->count() }}
                                    {{ $permissions->count() === 1
                                        ? 'permission'
                                        : 'permissions' }}
                                </p>
                            </div>


                            <div
                                class="mt-3
                                       min-w-0
                                       space-y-1"
                            >
                                @foreach ($permissions as $permission)

                                    <label
                                        wire:key="permission-{{ $permission->id }}"
                                        class="flex
                                               min-w-0
                                               cursor-pointer
                                               items-start
                                               gap-3
                                               rounded-lg
                                               px-2 py-2
                                               text-sm
                                               text-zinc-700
                                               transition
                                               hover:bg-zinc-50"
                                    >
                                        <input
                                            type="checkbox"
                                            wire:model="selectedPermissions"
                                            value="{{ $permission->name }}"
                                            @disabled($selectedRole->name === 'Super Administrator')
                                            class="mt-0.5
                                                   h-4 w-4
                                                   shrink-0
                                                   rounded
                                                   border-zinc-300
                                                   text-emerald-700
                                                   focus:ring-emerald-500
                                                   disabled:cursor-not-allowed
                                                   disabled:opacity-60"
                                        >

                                        <span
                                            class="min-w-0
                                                   flex-1
                                                   break-words
                                                   leading-5
                                                   [overflow-wrap:anywhere]"
                                        >
                                            {{ $permission->name }}
                                        </span>
                                    </label>

                                @endforeach
                            </div>
                        </article>

                    @endforeach
                </div>


                {{-- SAVE --}}
                @if ($selectedRole->name !== 'Super Administrator')
                    <div
                        class="flex
                               flex-col
                               gap-3
                               border-t
                               border-zinc-200
                               bg-zinc-50
                               px-5 py-4
                               sm:flex-row
                               sm:items-center
                               sm:justify-between
                               sm:px-6"
                    >
                        <p
                            class="text-xs leading-5
                                   text-zinc-500"
                        >
                            Permission changes take effect after saving.
                        </p>

                        <button
                            type="button"
                            wire:click="savePermissions"
                            wire:loading.attr="disabled"
                            wire:target="savePermissions"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   rounded-xl
                                   bg-emerald-700
                                   px-5 py-2.5
                                   text-sm font-bold
                                   text-white
                                   transition
                                   hover:bg-emerald-800
                                   disabled:cursor-not-allowed
                                   disabled:opacity-60"
                        >
                            <span
                                wire:loading.remove
                                wire:target="savePermissions"
                            >
                                Save Permissions
                            </span>

                            <span
                                wire:loading
                                wire:target="savePermissions"
                            >
                                Saving...
                            </span>
                        </button>
                    </div>
                @endif

            @else

                {{-- EMPTY STATE --}}
                <div
                    class="px-6 py-16
                           text-center"
                >
                    <p
                        class="font-bold
                               text-zinc-800"
                    >
                        No role selected
                    </p>

                    <p
                        class="mt-1
                               text-sm
                               text-zinc-500"
                    >
                        Select a role from the left side to
                        review and manage its permissions.
                    </p>
                </div>

            @endif
        </section>

    </div>

</div>
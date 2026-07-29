@forelse ($passkeys as $passkey)
    <div class="flex items-center justify-between p-4">
        <div>
            <p class="font-medium">
                {{ $passkey['name'] }}
            </p>
        </div>
    </div>
@empty
    <div class="p-8 text-center">
        <div
            class="mx-auto mb-4 flex size-14 items-center
                   justify-center rounded-2xl bg-zinc-100
                   dark:bg-zinc-800"
        >
            <flux:icon.key
                class="size-7 text-zinc-400 dark:text-zinc-500"
            />
        </div>

        <p class="font-medium">
            {{ __('No passkeys yet') }}
        </p>

        <flux:text class="mt-1">
            {{ __('Add a passkey to sign in without a password') }}
        </flux:text>
    </div>
@endforelse
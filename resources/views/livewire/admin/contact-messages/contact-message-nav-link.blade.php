<x-admin.nav-link :href="route('admin.contact-messages.index')" :active="$active"
    icon="fa-regular fa-envelope" x-show="!navSearch.trim() || $el.innerText.toLowerCase().includes(navSearch.trim().toLowerCase())"
    wire:poll.15s.visible="refreshUnreadCount">
    <span>Contact Messages</span>
    <span class="ml-auto" aria-live="polite" aria-atomic="true">
        @if ($unreadCount > 0)
            <span class="inline-flex items-center gap-1 rounded-full bg-red-500 px-2 py-1 text-xs font-bold text-white"
                title="New contact messages: {{ $unreadCount }}">
                <svg class="size-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="m3 7 9 6 9-6" />
                </svg>
                <span aria-hidden="true">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                <span class="sr-only">{{ $unreadCount }} new contact messages</span>
            </span>
        @endif
    </span>
</x-admin.nav-link>

@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
    'icon' => null,
])

@if ($disabled)
    <span
        {{ $attributes->class([
            'group flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5',
            'text-[13px] font-medium text-zinc-500',
        ]) }}
    >
        @if (is_string($icon) && $icon !== '')
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-800 text-zinc-500">
                <i class="{{ $icon }} text-sm" aria-hidden="true"></i>
            </span>
        @endif
        <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>

        <span
            class="rounded-full bg-zinc-800 px-2 py-0.5
                   text-[9px] font-bold uppercase tracking-wide
                   text-zinc-400"
        >
            Soon
        </span>
    </span>
@else
    <a
        href="{{ $href }}"
        {{ $attributes->class([
            'group flex items-center gap-3 rounded-xl px-3 py-2',
            'text-[13px] font-medium transition duration-150',
            'bg-emerald-500/15 text-emerald-200 ring-1 ring-inset ring-emerald-400/20' => $active,
            'text-slate-300 hover:bg-white/[0.06] hover:text-white' => ! $active,
        ]) }}
        @if ($active) aria-current="page" @endif
    >
        @if (is_string($icon) && $icon !== '')
            <span @class([
                'flex size-8 shrink-0 items-center justify-center rounded-lg transition',
                'bg-emerald-400/15 text-emerald-300' => $active,
                'bg-white/[0.04] text-slate-400 group-hover:bg-white/[0.08] group-hover:text-slate-200' => ! $active,
            ])>
                <i class="{{ $icon }} text-sm" aria-hidden="true"></i>
            </span>
        @endif
        <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>
        @if ($active)
            <i class="fa-solid fa-chevron-right mr-1 text-[10px] text-emerald-300/80" aria-hidden="true"></i>
        @endif
    </a>
@endif
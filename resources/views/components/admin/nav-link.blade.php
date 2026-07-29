@props([
    'href' => '#',
    'active' => false,
    'disabled' => false,
])

@if ($disabled)
    <span
        {{ $attributes->class([
            'flex cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3',
            'text-sm font-medium text-zinc-600',
        ]) }}
    >
        {{ $slot }}

        <span
            class="ml-auto rounded-full bg-zinc-800 px-2 py-0.5
                   text-[10px] font-semibold uppercase tracking-wide
                   text-zinc-400"
        >
            Soon
        </span>
    </span>
@else
    <a
        href="{{ $href }}"
        {{ $attributes->class([
            'flex items-center gap-3 rounded-xl px-4 py-3',
            'text-sm font-medium transition duration-150',
            'bg-emerald-600 text-white shadow-sm' => $active,
            'text-zinc-300 hover:bg-zinc-800 hover:text-white' => ! $active,
        ]) }}
    >
        {{ $slot }}
    </a>
@endif
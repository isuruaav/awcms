@props([
    'label',
    'icon',
    'tone' => 'neutral',
])

@php
    $classes = 'inline-flex size-9 shrink-0 items-center justify-center rounded-lg border transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

    $colors = match ($tone) {
        'primary' => 'border-blue-700 bg-blue-700 text-white hover:bg-blue-800 focus-visible:outline-blue-700',
        'info' => 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 focus-visible:outline-blue-700',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus-visible:outline-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 focus-visible:outline-amber-700',
        'danger' => 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 focus-visible:outline-red-700',
        default => 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50 focus-visible:outline-zinc-700',
    };

    $loadingTarget = $attributes->get('wire:target') ?? $attributes->get('wire:click');
@endphp

<flux:tooltip :content="$label" class="inline-flex align-middle">
    @if ($attributes->has('href'))
        <a {{ $attributes->merge(['aria-label' => $label])->class([$classes, $colors]) }}>
            <flux:icon :name="$icon" class="size-4" />
        </a>
    @else
        <button {{ $attributes->merge(['type' => 'button', 'aria-label' => $label])->class([$classes, $colors]) }}>
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @elseif ($loadingTarget)
                <flux:icon :name="$icon" class="size-4" wire:loading.remove wire:target="{{ $loadingTarget }}" />
                <flux:icon.arrow-path class="size-4 animate-spin" wire:loading wire:target="{{ $loadingTarget }}" />
            @else
                <flux:icon :name="$icon" class="size-4" />
            @endif
        </button>
    @endif
</flux:tooltip>

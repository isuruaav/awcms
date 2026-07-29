@props([
    'status' => 'draft',
])

@php
    $classes = match ($status) {
        'published', 'active' =>
            'bg-emerald-100 text-emerald-700 ring-emerald-600/20',

        'review', 'pending' =>
            'bg-amber-100 text-amber-700 ring-amber-600/20',

        'archived', 'inactive' =>
            'bg-zinc-100 text-zinc-700 ring-zinc-600/20',

        default =>
            'bg-blue-100 text-blue-700 ring-blue-600/20',
    };
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center rounded-full px-2.5 py-1',
        'text-xs font-semibold capitalize ring-1 ring-inset',
        $classes,
    ]) }}
>
    {{ str_replace('_', ' ', $status) }}
</span>
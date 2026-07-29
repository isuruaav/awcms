@props([
    'title',
    'value' => 0,
    'description' => null,
])

<article
    {{ $attributes->class([
        'rounded-2xl border border-zinc-200',
        'bg-white p-5 shadow-sm',
    ]) }}
>
    <p class="text-sm font-medium text-zinc-500">
        {{ $title }}
    </p>

    <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-950">
        {{ $value }}
    </p>

    @if ($description)
        <p class="mt-2 text-xs leading-5 text-zinc-500">
            {{ $description }}
        </p>
    @endif
</article>
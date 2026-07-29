@props([
    'title',
    'description' => null,
])

<div
    {{ $attributes->class([
        'flex flex-col gap-4 sm:flex-row',
        'sm:items-start sm:justify-between',
    ]) }}
>
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-950">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-1 max-w-3xl text-sm leading-6 text-zinc-600">
                {{ $description }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
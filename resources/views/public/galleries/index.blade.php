@php
    $mediaUrl = static function (?\App\Models\MediaAsset $media, ?string $preferredVariant = null): ?string {
        if (!$media instanceof \App\Models\MediaAsset) {
            return null;
        }

        if ($preferredVariant !== null) {
            $variant = $media->variants->first(
                static function ($candidate) use ($preferredVariant): bool {
                    $name = $candidate->getAttribute('name');

                    if ($name instanceof \BackedEnum) {
                        $name = $name->value;
                    }

                    return is_string($name)
                        && $name === $preferredVariant;
                },
            );

            if ($variant !== null) {
                $disk = $variant->getAttribute('disk');
                $path = $variant->getAttribute('path');

                if (
                    is_string($disk)
                    && trim($disk) !== ''
                    && is_string($path)
                    && trim($path) !== ''
                ) {
                    $url =
                        \Illuminate\Support\Facades\Storage::disk(
                            $disk,
                        )->url(
                            $path,
                        );

                    return str_starts_with($url, 'http://')
                        || str_starts_with($url, 'https://')
                            ? $url
                            : url($url);
                }
            }
        }

        $externalUrl =
            $media->getAttribute(
                'external_url',
            );

        if (
            is_string($externalUrl)
            && trim($externalUrl) !== ''
        ) {
            return $externalUrl;
        }

        $disk =
            $media->getAttribute(
                'disk',
            );

        $path =
            $media->getAttribute(
                'path',
            );

        if (
            !is_string($disk)
            || trim($disk) === ''
            || !is_string($path)
            || trim($path) === ''
        ) {
            return null;
        }

        $url =
            \Illuminate\Support\Facades\Storage::disk(
                $disk,
            )->url(
                $path,
            );

        return str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://')
                ? $url
                : url($url);
    };
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Gallery</title>

    <meta
        name="description"
        content="Explore photographs from our events, programmes and activities."
    >

    <meta
        name="robots"
        content="index, follow"
    >

    <link
        rel="canonical"
        href="{{ route('galleries.index') }}"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="bg-gray-50 text-slate-900">

    <main class="min-h-screen">

        <section class="bg-slate-900 py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="text-center">

                    <p class="mb-2 text-sm font-semibold uppercase tracking-widest text-amber-400">
                        Photo Gallery
                    </p>

                    <h1 class="text-3xl font-bold text-white sm:text-4xl">
                        Gallery
                    </h1>

                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                        Explore photographs from our events, programmes and activities.
                    </p>

                </div>

            </div>
        </section>

        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                @if ($galleries->count() > 0)

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                        @foreach ($galleries as $gallery)
                            @php
                                $coverUrl =
                                    $mediaUrl(
                                        $gallery->coverMedia,
                                        'medium',
                                    );

                                $coverMediaAlt =
                                    $gallery->coverMedia?->getAttribute(
                                        'alt_text',
                                    );

                                $coverAlt =
                                    is_string($coverMediaAlt)
                                    && trim($coverMediaAlt) !== ''
                                        ? trim($coverMediaAlt)
                                        : $gallery->title;

                                $description =
                                    is_string($gallery->description)
                                        ? trim(
                                            strip_tags(
                                                $gallery->description,
                                            ),
                                        )
                                        : '';
                            @endphp

                            <article
                                class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl"
                            >

                                <a
                                    href="{{ route('galleries.show', ['slug' => $gallery->slug]) }}"
                                    class="block"
                                    aria-label="View {{ $gallery->title }}"
                                >

                                    <div class="aspect-[4/3] overflow-hidden bg-slate-100">

                                        @if ($coverUrl !== null)
                                            <img
                                                src="{{ $coverUrl }}"
                                                alt="{{ $coverAlt }}"
                                                loading="lazy"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >
                                        @else
                                            <div class="flex h-full items-center justify-center px-6 text-center text-sm text-slate-500">
                                                No cover image available
                                            </div>
                                        @endif

                                    </div>

                                    <div class="p-5">

                                        @if ($gallery->event_date !== null)
                                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                                                {{ $gallery->event_date->format('d M Y') }}
                                            </p>
                                        @endif

                                        <h2 class="mt-1 text-lg font-bold text-slate-900 transition group-hover:text-amber-700">
                                            {{ $gallery->title }}
                                        </h2>

                                        @if ($description !== '')
                                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                                {{
                                                    \Illuminate\Support\Str::limit(
                                                        $description,
                                                        120,
                                                    )
                                                }}
                                            </p>
                                        @endif

                                        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-amber-700">
                                            View Gallery
                                            <span aria-hidden="true">→</span>
                                        </span>

                                    </div>

                                </a>

                            </article>
                        @endforeach

                    </div>

                    @if ($galleries->hasPages())
                        <div class="mt-10">
                            {{ $galleries->links() }}
                        </div>
                    @endif

                @else

                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center">

                        <h2 class="text-lg font-semibold text-slate-900">
                            No galleries available
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Published galleries will appear here.
                        </p>

                    </div>

                @endif

            </div>
        </section>

    </main>

</body>

</html>
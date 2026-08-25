@php
    $mediaUrl = static function (?\App\Models\MediaAsset $media, ?string $preferredVariant = null): ?string {
        if (!$media instanceof \App\Models\MediaAsset) {
            return null;
        }

        if ($preferredVariant !== null) {
            $variant = $media->variants->first(static function ($candidate) use ($preferredVariant): bool {
                $name = $candidate->getAttribute('name');

                if ($name instanceof \BackedEnum) {
                    $name = $name->value;
                }

                return is_string($name) && $name === $preferredVariant;
            });

            if ($variant !== null) {
                $disk = $variant->getAttribute('disk');
                $path = $variant->getAttribute('path');

                if (
                    is_string($disk)
                    && trim($disk) !== ''
                    && is_string($path)
                    && trim($path) !== ''
                ) {
                    $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

                    return str_starts_with($url, 'http://')
                        || str_starts_with($url, 'https://')
                            ? $url
                            : url($url);
                }
            }
        }

        $externalUrl = $media->getAttribute('external_url');

        if (is_string($externalUrl) && trim($externalUrl) !== '') {
            return $externalUrl;
        }

        $disk = $media->getAttribute('disk');
        $path = $media->getAttribute('path');

        if (
            !is_string($disk)
            || trim($disk) === ''
            || !is_string($path)
            || trim($path) === ''
        ) {
            return null;
        }

        $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

        return str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://')
                ? $url
                : url($url);
    };

    /*
    |--------------------------------------------------------------------------
    | Cover
    |--------------------------------------------------------------------------
    */

    $coverUrl = $mediaUrl(
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

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    */

    $description =
        is_string($gallery->description)
            ? trim(
                strip_tags(
                    $gallery->description,
                ),
            )
            : '';

    $seoTitle =
        is_string($gallery->seo_title)
        && trim($gallery->seo_title) !== ''
            ? trim($gallery->seo_title)
            : $gallery->title;

    $seoDescription =
        is_string($gallery->seo_description)
        && trim($gallery->seo_description) !== ''
            ? trim($gallery->seo_description)
            : (
                $description !== ''
                    ? \Illuminate\Support\Str::limit(
                        $description,
                        160,
                        '',
                    )
                    : 'View photographs from '.$gallery->title.'.'
            );

    $canonicalUrl =
        route(
            'galleries.show',
            [
                'slug' => $gallery->slug,
            ],
        );
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $seoTitle }}</title>

    <meta
        name="description"
        content="{{ $seoDescription }}"
    >

    <meta
        name="robots"
        content="index, follow"
    >

    <link
        rel="canonical"
        href="{{ $canonicalUrl }}"
    >

    {{-- Open Graph --}}
    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="{{ $seoTitle }}"
    >

    <meta
        property="og:description"
        content="{{ $seoDescription }}"
    >

    <meta
        property="og:url"
        content="{{ $canonicalUrl }}"
    >

    @if ($coverUrl !== null)
        <meta
            property="og:image"
            content="{{ $coverUrl }}"
        >
    @endif

    {{-- Twitter / X --}}
    <meta
        name="twitter:card"
        content="{{ $coverUrl !== null ? 'summary_large_image' : 'summary' }}"
    >

    <meta
        name="twitter:title"
        content="{{ $seoTitle }}"
    >

    <meta
        name="twitter:description"
        content="{{ $seoDescription }}"
    >

    @if ($coverUrl !== null)
        <meta
            name="twitter:image"
            content="{{ $coverUrl }}"
        >
    @endif

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="bg-slate-50 text-slate-900">

    <main class="min-h-screen">

        <section class="bg-slate-900 py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <a
                    href="{{ route('galleries.index') }}"
                    class="inline-flex text-sm font-semibold text-amber-400 transition hover:text-amber-300"
                >
                    ← Back to Gallery
                </a>

                <div class="mt-6 max-w-4xl">

                    @if ($gallery->event_date !== null)
                        <p class="text-sm font-semibold uppercase tracking-widest text-amber-400">
                            {{ $gallery->event_date->format('d M Y') }}
                        </p>
                    @endif

                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $gallery->title }}
                    </h1>

                    @if ($description !== '')
                        <p class="mt-4 max-w-3xl text-base leading-7 text-slate-300">
                            {{ $description }}
                        </p>
                    @endif

                </div>

            </div>
        </section>

        @if ($coverUrl !== null)
            <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-slate-200 shadow-sm ring-1 ring-slate-200">

                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $coverAlt }}"
                        class="aspect-[16/7] w-full object-cover"
                    >

                </div>
            </section>
        @endif

        <section class="py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="mb-6 flex items-end justify-between gap-4">

                    <div>
                        <p class="text-sm font-semibold uppercase tracking-widest text-amber-700">
                            Photographs
                        </p>

                        <h2 class="mt-1 text-2xl font-bold text-slate-900">
                            Gallery Images
                        </h2>
                    </div>

                    <p class="text-sm text-slate-500">
                        {{ $gallery->images->count() }}
                        {{ $gallery->images->count() === 1 ? 'image' : 'images' }}
                    </p>

                </div>

                @if ($gallery->images->isNotEmpty())

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                        @foreach ($gallery->images as $galleryImage)
                            @php
                                $media =
                                    $galleryImage->media;

                                $imageUrl =
                                    $media instanceof \App\Models\MediaAsset
                                        ? $mediaUrl(
                                            $media,
                                            'medium',
                                        )
                                        : null;

                                $galleryImageAlt =
                                    $galleryImage->getAttribute(
                                        'alt_text',
                                    );

                                $mediaAlt =
                                    $media instanceof \App\Models\MediaAsset
                                        ? $media->getAttribute(
                                            'alt_text',
                                        )
                                        : null;

                                $imageAlt =
                                    is_string($galleryImageAlt)
                                    && trim($galleryImageAlt) !== ''
                                        ? trim($galleryImageAlt)
                                        : (
                                            is_string($mediaAlt)
                                            && trim($mediaAlt) !== ''
                                                ? trim($mediaAlt)
                                                : $gallery->title
                                        );
                            @endphp

                            @if ($imageUrl !== null)
                                <figure class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

                                    <div class="aspect-[4/3] overflow-hidden bg-slate-100">

                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $imageAlt }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover"
                                        >

                                    </div>

                                    @if (
                                        is_string($galleryImage->caption)
                                        && trim($galleryImage->caption) !== ''
                                    )
                                        <figcaption class="px-4 py-3 text-sm leading-6 text-slate-600">
                                            {{ $galleryImage->caption }}
                                        </figcaption>
                                    @endif

                                </figure>
                            @endif
                        @endforeach

                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                        <p class="text-sm text-slate-500">
                            No public gallery images are available.
                        </p>
                    </div>
                @endif

            </div>
        </section>

    </main>

</body>

</html>
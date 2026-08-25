@php
    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    $description = is_string($document->description) ? trim(strip_tags($document->description)) : '';

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    */

    $seoTitle =
        is_string($document->seo_title) && trim($document->seo_title) !== ''
            ? trim($document->seo_title)
            : $document->title;

    $seoDescription =
        is_string($document->seo_description) && trim($document->seo_description) !== ''
            ? trim($document->seo_description)
            : ($description !== ''
                ? \Illuminate\Support\Str::limit($description, 160, '')
                : 'View and download ' . $document->title . '.');

    $canonicalUrl = route('documents.show', [
        'slug' => $document->slug,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Current PDF
    |--------------------------------------------------------------------------
    */

    $media = $currentVersion->media;

    $originalName =
        $media instanceof \App\Models\MediaAsset &&
        is_string($media->original_name) &&
        trim($media->original_name) !== ''
            ? trim($media->original_name)
            : null;

    $fileSize =
        $media instanceof \App\Models\MediaAsset && is_numeric($media->size_bytes) && (int) $media->size_bytes > 0
            ? (int) $media->size_bytes
            : null;

    $formattedFileSize = null;

    if ($fileSize !== null) {
        if ($fileSize >= 1024 * 1024) {
            $formattedFileSize = number_format($fileSize / 1024 / 1024, 2) . ' MB';
        } else {
            $formattedFileSize = number_format($fileSize / 1024, 1) . ' KB';
        }
    }
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $seoTitle }}</title>

    <meta name="description" content="{{ $seoDescription }}">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ $canonicalUrl }}">


    {{-- =====================================================
         OPEN GRAPH
    ====================================================== --}}

    <meta property="og:type" content="website">

    <meta property="og:title" content="{{ $seoTitle }}">

    <meta property="og:description" content="{{ $seoDescription }}">

    <meta property="og:url" content="{{ $canonicalUrl }}">


    {{-- =====================================================
         TWITTER / X
    ====================================================== --}}

    <meta name="twitter:card" content="summary">

    <meta name="twitter:title" content="{{ $seoTitle }}">

    <meta name="twitter:description" content="{{ $seoDescription }}">


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="bg-slate-50 text-slate-900">

    <main class="min-h-screen">

        {{-- =====================================================
             HERO
        ====================================================== --}}

        <section class="bg-slate-900 py-12 sm:py-16">

            <div class="mx-auto max-w-6xl
                       px-4 sm:px-6 lg:px-8">

                <a href="{{ route('documents.index') }}"
                    class="inline-flex
                           text-sm font-semibold
                           text-amber-400
                           transition
                           hover:text-amber-300">
                    ← Back to Documents
                </a>


                <div class="mt-6 max-w-4xl">

                    <div
                        class="flex flex-wrap
                               items-center
                               gap-2">

                        @if ($document->category)
                            <span
                                class="inline-flex
                                       rounded-full
                                       bg-amber-400/10
                                       px-3 py-1
                                       text-xs font-semibold
                                       text-amber-300
                                       ring-1
                                       ring-inset
                                       ring-amber-400/20">
                                {{ $document->category->name }}
                            </span>
                        @endif


                        <span
                            class="inline-flex
                                   rounded-full
                                   bg-white/10
                                   px-3 py-1
                                   text-xs font-semibold
                                   text-slate-200">
                            PDF
                        </span>


                        <span
                            class="inline-flex
                                   rounded-full
                                   bg-white/10
                                   px-3 py-1
                                   text-xs font-semibold
                                   text-slate-200">
                            Version {{ $currentVersion->version }}
                        </span>

                    </div>


                    <h1
                        class="mt-4
                               text-3xl font-bold
                               tracking-tight
                               text-white
                               sm:text-4xl
                               lg:text-5xl">
                        {{ $document->title }}
                    </h1>


                    @if ($description !== '')
                        <p
                            class="mt-4
                                   max-w-3xl
                                   text-base leading-7
                                   text-slate-300">
                            {{ $description }}
                        </p>
                    @endif

                </div>

            </div>

        </section>


        {{-- =====================================================
             MAIN CONTENT
        ====================================================== --}}

        <section class="py-10 sm:py-14">

            <div class="mx-auto max-w-6xl
                       px-4 sm:px-6 lg:px-8">

                <div class="grid gap-6
                           lg:grid-cols-[minmax(0,1fr)_340px]">

                    {{-- =================================================
                         DOCUMENT DETAILS
                    ================================================== --}}

                    <div class="space-y-6">

                        <section
                            class="overflow-hidden
                                   rounded-2xl
                                   bg-white
                                   shadow-sm
                                   ring-1 ring-slate-200">

                            <div
                                class="border-b
                                       border-slate-200
                                       bg-slate-50
                                       px-6 py-4">
                                <p
                                    class="text-xs font-semibold
                                           uppercase tracking-widest
                                           text-amber-700">
                                    Publication
                                </p>

                                <h2
                                    class="mt-1
                                           text-xl font-bold
                                           text-slate-900">
                                    Document Details
                                </h2>
                            </div>


                            <dl class="divide-y
                                       divide-slate-100">

                                @if ($document->category)
                                    <div
                                        class="grid gap-1
                                               px-6 py-4
                                               sm:grid-cols-[180px_minmax(0,1fr)]">
                                        <dt
                                            class="text-sm font-semibold
                                                   text-slate-500">
                                            Category
                                        </dt>

                                        <dd
                                            class="text-sm font-medium
                                                   text-slate-900">
                                            {{ $document->category->name }}
                                        </dd>
                                    </div>
                                @endif


                                <div
                                    class="grid gap-1
                                           px-6 py-4
                                           sm:grid-cols-[180px_minmax(0,1fr)]">
                                    <dt
                                        class="text-sm font-semibold
                                               text-slate-500">
                                        Document Date
                                    </dt>

                                    <dd
                                        class="text-sm font-medium
                                               text-slate-900">
                                        {{ $document->document_date?->format('d M Y') ?? '—' }}
                                    </dd>
                                </div>


                                <div
                                    class="grid gap-1
                                           px-6 py-4
                                           sm:grid-cols-[180px_minmax(0,1fr)]">
                                    <dt
                                        class="text-sm font-semibold
                                               text-slate-500">
                                        Published
                                    </dt>

                                    <dd
                                        class="text-sm font-medium
                                               text-slate-900">
                                        {{ $document->published_at?->format('d M Y H:i') ?? '—' }}
                                    </dd>
                                </div>


                                <div
                                    class="grid gap-1
                                           px-6 py-4
                                           sm:grid-cols-[180px_minmax(0,1fr)]">
                                    <dt
                                        class="text-sm font-semibold
                                               text-slate-500">
                                        Current Version
                                    </dt>

                                    <dd
                                        class="text-sm font-medium
                                               text-slate-900">
                                        Version {{ $currentVersion->version }}

                                        @if ($currentVersion->version_label)
                                            —
                                            {{ $currentVersion->version_label }}
                                        @endif
                                    </dd>
                                </div>


                                @if ($originalName !== null)
                                    <div
                                        class="grid gap-1
                                               px-6 py-4
                                               sm:grid-cols-[180px_minmax(0,1fr)]">
                                        <dt
                                            class="text-sm font-semibold
                                                   text-slate-500">
                                            File
                                        </dt>

                                        <dd
                                            class="break-all
                                                   text-sm font-medium
                                                   text-slate-900">
                                            {{ $originalName }}
                                        </dd>
                                    </div>
                                @endif


                                @if ($formattedFileSize !== null)
                                    <div
                                        class="grid gap-1
                                               px-6 py-4
                                               sm:grid-cols-[180px_minmax(0,1fr)]">
                                        <dt
                                            class="text-sm font-semibold
                                                   text-slate-500">
                                            File Size
                                        </dt>

                                        <dd
                                            class="text-sm font-medium
                                                   text-slate-900">
                                            {{ $formattedFileSize }}
                                        </dd>
                                    </div>
                                @endif

                            </dl>

                        </section>


                        {{-- DESCRIPTION --}}

                        @if ($description !== '')
                            <section
                                class="rounded-2xl
                                       bg-white
                                       p-6
                                       shadow-sm
                                       ring-1 ring-slate-200">
                                <p
                                    class="text-xs font-semibold
                                           uppercase tracking-widest
                                           text-amber-700">
                                    About
                                </p>

                                <h2
                                    class="mt-1
                                           text-xl font-bold
                                           text-slate-900">
                                    About this document
                                </h2>

                                <p
                                    class="mt-4
                                           whitespace-pre-line
                                           text-sm leading-7
                                           text-slate-600">
                                    {{ $description }}
                                </p>
                            </section>
                        @endif


                        {{-- VERSION NOTE --}}

                        @if ($currentVersion->change_note)
                            <section
                                class="rounded-2xl
                                       bg-white
                                       p-6
                                       shadow-sm
                                       ring-1 ring-slate-200">
                                <p
                                    class="text-xs font-semibold
                                           uppercase tracking-widest
                                           text-amber-700">
                                    Current Version
                                </p>

                                <h2
                                    class="mt-1
                                           text-xl font-bold
                                           text-slate-900">
                                    Version Information
                                </h2>

                                <p
                                    class="mt-4
                                           whitespace-pre-line
                                           text-sm leading-7
                                           text-slate-600">
                                    {{ $currentVersion->change_note }}
                                </p>
                            </section>
                        @endif

                    </div>


                    {{-- =================================================
                         SIDEBAR
                    ================================================== --}}

                    <aside class="space-y-6">

                        {{-- PDF ACTIONS --}}

                        <section
                            class="rounded-2xl
                                   bg-white
                                   p-6
                                   shadow-sm
                                   ring-1 ring-slate-200">

                            <div class="flex h-14 w-14
                                       items-center
                                       justify-center
                                       rounded-2xl
                                       bg-red-50
                                       text-sm font-black
                                       text-red-700"
                                aria-hidden="true">
                                PDF
                            </div>


                            <h2
                                class="mt-4
                                       text-lg font-bold
                                       text-slate-900">
                                Current PDF
                            </h2>

                            <p
                                class="mt-1
                                       text-sm leading-6
                                       text-slate-500">
                                Version {{ $currentVersion->version }}

                                @if ($currentVersion->version_label)
                                    — {{ $currentVersion->version_label }}
                                @endif
                            </p>


                            <a href="{{ route('documents.view', [
                                'slug' => $document->slug,
                            ]) }}"
                                target="_blank" rel="noopener"
                                class="mt-5
                                       inline-flex
                                       w-full
                                       items-center
                                       justify-center
                                       rounded-xl
                                       border border-amber-300
                                       bg-amber-50
                                       px-5 py-3
                                       text-sm font-bold
                                       text-amber-800
                                       transition
                                       hover:bg-amber-100">
                                View PDF
                            </a>


                            <a href="{{ route('documents.download', [
                                'slug' => $document->slug,
                            ]) }}"
                                class="mt-3
                                       inline-flex
                                       w-full
                                       items-center
                                       justify-center
                                       rounded-xl
                                       bg-slate-900
                                       px-5 py-3
                                       text-sm font-bold
                                       text-white
                                       transition
                                       hover:bg-slate-800">
                                Download PDF
                            </a>

                        </section>


                        {{-- STABLE LINK --}}

                        <section
                            class="rounded-2xl
                                   bg-white
                                   p-6
                                   shadow-sm
                                   ring-1 ring-slate-200">

                            <h2 class="text-sm font-bold
                                       text-slate-900">
                                Document Link
                            </h2>

                            <p
                                class="mt-2
                                       text-xs leading-5
                                       text-slate-500">
                                This document page keeps the same
                                public URL when the PDF version is replaced.
                            </p>

                            <div
                                class="mt-4
                                       break-all
                                       rounded-xl
                                       bg-slate-50
                                       px-3 py-3
                                       text-xs
                                       text-slate-600
                                       ring-1 ring-slate-200">
                                {{ $canonicalUrl }}
                            </div>

                        </section>


                        {{-- BACK --}}

                        <a href="{{ route('documents.index') }}"
                            class="inline-flex
                                   w-full
                                   items-center
                                   justify-center
                                   rounded-xl
                                   border border-slate-300
                                   bg-white
                                   px-5 py-3
                                   text-sm font-semibold
                                   text-slate-700
                                   transition
                                   hover:bg-slate-50">
                            ← Back to Documents
                        </a>

                    </aside>

                </div>

            </div>

        </section>

    </main>

</body>

</html>

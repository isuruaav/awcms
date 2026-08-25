<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Documents</title>

    <meta name="description" content="Browse official documents, reports, publications and downloadable PDF files.">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ route('documents.index') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-slate-900">

    <main class="min-h-screen">

        {{-- =====================================================
             HERO
        ====================================================== --}}

        <section class="bg-slate-900 py-14">

            <div class="mx-auto max-w-7xl
                       px-4 sm:px-6 lg:px-8">

                <div class="text-center">

                    <p
                        class="mb-2
                               text-sm font-semibold
                               uppercase tracking-widest
                               text-amber-400">
                        Publications & Downloads
                    </p>

                    <h1
                        class="text-3xl font-bold
                               text-white
                               sm:text-4xl">
                        Documents
                    </h1>

                    <p
                        class="mx-auto mt-3
                               max-w-2xl
                               text-sm leading-6
                               text-slate-300
                               sm:text-base">
                        Browse official documents, reports,
                        publications and downloadable PDF files.
                    </p>

                </div>

            </div>

        </section>


        {{-- =====================================================
             DOCUMENT LIST
        ====================================================== --}}

        <section class="py-12 sm:py-16">

            <div class="mx-auto max-w-5xl
                       px-4 sm:px-6 lg:px-8">

                @if ($documents->count() > 0)

                    <div class="space-y-5">

                        @foreach ($documents as $document)
                            @php
                                $description = is_string($document->description)
                                    ? trim(strip_tags($document->description))
                                    : '';
                            @endphp

                            <article
                                class="group
                                       overflow-hidden
                                       rounded-2xl
                                       bg-white
                                       shadow-sm
                                       ring-1 ring-gray-200
                                       transition duration-300
                                       hover:shadow-lg">

                                <div
                                    class="flex flex-col
                                           gap-5
                                           p-5
                                           sm:p-6
                                           lg:flex-row
                                           lg:items-center
                                           lg:justify-between">

                                    {{-- DOCUMENT DETAILS --}}

                                    <div class="min-w-0
                                               flex-1">

                                        <div
                                            class="flex flex-wrap
                                                   items-center
                                                   gap-2">

                                            @if ($document->category)
                                                <span
                                                    class="inline-flex
                                                           rounded-full
                                                           bg-amber-50
                                                           px-2.5 py-1
                                                           text-xs font-semibold
                                                           text-amber-700
                                                           ring-1
                                                           ring-inset
                                                           ring-amber-200">
                                                    {{ $document->category->name }}
                                                </span>
                                            @endif


                                            @if ($document->document_date)
                                                <span
                                                    class="text-xs
                                                           font-medium
                                                           text-slate-500">
                                                    {{ $document->document_date->format('d M Y') }}
                                                </span>
                                            @endif


                                            <span
                                                class="inline-flex
                                                       rounded-full
                                                       bg-slate-100
                                                       px-2.5 py-1
                                                       text-xs font-semibold
                                                       text-slate-600">
                                                PDF
                                            </span>

                                        </div>


                                        <h2
                                            class="mt-3
                                                   text-lg font-bold
                                                   leading-7
                                                   text-slate-900
                                                   transition
                                                   group-hover:text-amber-700
                                                   sm:text-xl">
                                            <a
                                                href="{{ route('documents.show', [
                                                    'slug' => $document->slug,
                                                ]) }}">
                                                {{ $document->title }}
                                            </a>
                                        </h2>


                                        @if ($description !== '')
                                            <p
                                                class="mt-2
                                                       max-w-3xl
                                                       text-sm leading-6
                                                       text-slate-600">
                                                {{ \Illuminate\Support\Str::limit($description, 180) }}
                                            </p>
                                        @endif


                                        <p
                                            class="mt-3
                                                   text-xs
                                                   text-slate-400">
                                            Current version:
                                            v{{ $document->current_version }}
                                        </p>

                                    </div>


                                    {{-- ACTIONS --}}

                                    <div
                                        class="flex shrink-0
                                               flex-wrap
                                               items-center
                                               gap-2
                                               lg:justify-end">

                                        <a href="{{ route('documents.show', [
                                            'slug' => $document->slug,
                                        ]) }}"
                                            class="inline-flex
                                                   items-center
                                                   justify-center
                                                   rounded-xl
                                                   border border-slate-300
                                                   bg-white
                                                   px-4 py-2.5
                                                   text-sm font-semibold
                                                   text-slate-700
                                                   transition
                                                   hover:bg-slate-50">
                                            Details
                                        </a>


                                        <a href="{{ route('documents.view', [
                                            'slug' => $document->slug,
                                        ]) }}"
                                            target="_blank" rel="noopener"
                                            class="inline-flex
                                                   items-center
                                                   justify-center
                                                   rounded-xl
                                                   border border-amber-300
                                                   bg-amber-50
                                                   px-4 py-2.5
                                                   text-sm font-semibold
                                                   text-amber-800
                                                   transition
                                                   hover:bg-amber-100">
                                            View PDF
                                        </a>


                                        <a href="{{ route('documents.download', [
                                            'slug' => $document->slug,
                                        ]) }}"
                                            class="inline-flex
                                                   items-center
                                                   justify-center
                                                   rounded-xl
                                                   bg-slate-900
                                                   px-4 py-2.5
                                                   text-sm font-semibold
                                                   text-white
                                                   transition
                                                   hover:bg-slate-800">
                                            Download
                                        </a>

                                    </div>

                                </div>

                            </article>
                        @endforeach

                    </div>


                    {{-- PAGINATION --}}

                    @if ($documents->hasPages())
                        <div class="mt-10">
                            {{ $documents->links() }}
                        </div>
                    @endif
                @else
                    {{-- EMPTY STATE --}}

                    <div
                        class="rounded-2xl
                               border border-dashed
                               border-gray-300
                               bg-white
                               px-6 py-16
                               text-center">

                        <div class="mx-auto
                                   flex h-14 w-14
                                   items-center
                                   justify-center
                                   rounded-2xl
                                   bg-slate-100
                                   text-sm font-black
                                   text-slate-600"
                            aria-hidden="true">
                            PDF
                        </div>

                        <h2
                            class="mt-4
                                   text-lg font-semibold
                                   text-slate-900">
                            No documents available
                        </h2>

                        <p
                            class="mt-2
                                   text-sm
                                   text-gray-500">
                            Published documents will appear here.
                        </p>

                    </div>

                @endif

            </div>

        </section>

    </main>

</body>

</html>

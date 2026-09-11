@extends('theme-school-of-signals::layout')

@section('title', $pageTitle)
@section('meta_description', $metaDescription)

@section('canonical', $currentLocale === 'en' ? route('history.past-commandants') :
    route('history.past-commandants.localized', ['locale' => 'si']))

@section('content')
    @php($mediaUrls = app(\App\Services\MediaUrlService::class))

    <article class="past-command-page">
        <header class="section section-soft past-command-section">
            <div class="container text-center">
                <p class="kicker">{{ $currentLocale === 'si' ? 'ඉතිහාසය' : 'History' }}</p>
                <h1 class="title-lg">{{ $currentLocale === 'si' ? 'හිටපු සේනාවිධායකවරු' : 'Past Commandants' }}</h1>
                <p class="section-subtitle mx-auto">
                    {{ $currentLocale === 'si' ? 'ශ්‍රී ලංකා සංඥා පාසලට නායකත්වය දුන් සේනාවිධායකවරු.' : 'The leaders who served the School of Signals.' }}
                </p>
            </div>
        </header>

        @if ($currentCommandant)
            @php($currentImage = $currentCommandant->image ? $mediaUrls->mediumOrOriginal($currentCommandant->image) : null)
            <section class="section white" aria-labelledby="current-commandant-title">
                <div class="container">
                    <div class="current-commandant-card">
                        <div class="current-commandant-label">
                            {{ $currentLocale === 'si' ? 'වර්තමාන සේනාවිධායක' : 'Present Commandant' }}</div>
                        @if ($currentImage)
                            <img src="{{ $currentImage }}" alt="{{ $currentCommandant->nameForLocale($currentLocale) }}"
                                loading="eager">
                        @else
                            <div class="current-commandant-placeholder"><i class="fa-solid fa-user-tie"
                                    aria-hidden="true"></i></div>
                        @endif
                        <div class="current-commandant-body">
                            <p class="role">{{ $currentCommandant->titleForLocale($currentLocale) }}</p>
                            <h2 id="current-commandant-title">
                                {{ $currentCommandant->nameForLocale($currentLocale) ?: ($currentLocale === 'si' ? 'නම ඇතුළත් කර නැත' : 'Name not added') }}
                            </h2>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="section past-command-section" aria-labelledby="past-commandants-title">
            <div class="container">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ $currentLocale === 'si' ? 'පෙර නායකත්වය' : 'Former Leadership' }}</p>
                        <h2 class="title-lg" id="past-commandants-title">
                            {{ $currentLocale === 'si' ? 'හිටපු සේනාවිධායකවරු' : 'Past Commandants' }}</h2>
                    </div>
                </div>

                @if ($pastCommandants->isNotEmpty())
                    <div class="past-command-grid">
                        @foreach ($pastCommandants as $index => $commandant)
                            @php($imageUrl = $commandant->image ? $mediaUrls->mediumOrOriginal($commandant->image) : null)
                            <article class="card past-command-card reveal">
                                <div class="command-img">
                                    <span
                                        class="command-no">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}"
                                            alt="{{ $commandant->nameForLocale($currentLocale) }}" loading="lazy">
                                    @else
                                        <div class="command-placeholder"><i class="fa-solid fa-user-tie"
                                                aria-hidden="true"></i><span>{{ $currentLocale === 'si' ? 'රූපය නොමැත' : 'Image unavailable' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="role">{{ $currentLocale === 'si' ? 'සේනාවිධායක' : 'Commandant' }}</p>
                                    <h3>{{ $commandant->nameForLocale($currentLocale) }}</h3>
                                    <div class="command-meta">
                                        <span><i class="fa-regular fa-calendar"
                                                aria-hidden="true"></i>{{ $commandant->from_date->format('d.m.Y') }}</span>
                                        <span><i class="fa-solid fa-arrow-right"
                                                aria-hidden="true"></i>{{ $commandant->to_date->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="news-empty"><i class="fa-solid fa-landmark" aria-hidden="true"></i>
                        <p>{{ $currentLocale === 'si' ? 'හිටපු සේනාවිධායකවරුන්ගේ තොරතුරු ඉක්මනින්.' : 'Past Commandant records will be available soon.' }}
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </article>
@endsection

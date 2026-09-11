@extends('theme-school-of-signals::layout')

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('canonical', $currentLocale === 'en' ? route('history.past-chief-instructors') :
    route('history.past-chief-instructors.localized', ['locale' => 'si']))

@section('content')
    @php($mediaUrls = app(\App\Services\MediaUrlService::class))
    <article class="past-command-page">
        <header class="section section-soft past-command-section">
            <div class="container text-center">
                <p class="kicker">{{ $currentLocale === 'si' ? 'ඉතිහාසය' : 'History' }}</p>
                <h1 class="title-lg">{{ $currentLocale === 'si' ? 'හිටපු ප්‍රධාන උපදේශකවරු' : 'Past Chief Instructors' }}
                </h1>
                <p class="section-subtitle mx-auto">
                    {{ $currentLocale === 'si' ? 'ශ්‍රී ලංකා සංඥා පාසලට සේවය කළ ප්‍රධාන උපදේශකවරු.' : 'The Chief Instructors who served the School of Signals.' }}
                </p>
            </div>
        </header>

        @if ($currentChiefInstructor)
            @php($currentImage = $currentChiefInstructor->image ? $mediaUrls->mediumOrOriginal($currentChiefInstructor->image) : null)
            <section class="section white" aria-labelledby="current-chief-instructor-title">
                <div class="container">
                    <div class="current-commandant-card">
                        <div class="current-commandant-label">
                            {{ $currentLocale === 'si' ? 'වර්තමාන ප්‍රධාන උපදේශක' : 'Present Chief Instructor' }}</div>
                        @if ($currentImage)
                            <img src="{{ $currentImage }}"
                                alt="{{ $currentChiefInstructor->nameForLocale($currentLocale) }}" loading="eager">
                        @else
                            <div class="current-commandant-placeholder"><i class="fa-solid fa-user-tie"
                                    aria-hidden="true"></i></div>
                        @endif
                        <div class="current-commandant-body">
                            <p class="role">{{ $currentChiefInstructor->titleForLocale($currentLocale) }}</p>
                            <h2 id="current-chief-instructor-title">
                                {{ $currentChiefInstructor->nameForLocale($currentLocale) ?: ($currentLocale === 'si' ? 'නම ඇතුළත් කර නැත' : 'Name not added') }}
                            </h2>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="section past-command-section" aria-labelledby="past-chief-instructors-title">
            <div class="container">
                <div class="section-head">
                    <div>
                        <p class="kicker">{{ $currentLocale === 'si' ? 'පෙර නායකත්වය' : 'Former Leadership' }}</p>
                        <h2 class="title-lg" id="past-chief-instructors-title">
                            {{ $currentLocale === 'si' ? 'හිටපු ප්‍රධාන උපදේශකවරු' : 'Past Chief Instructors' }}</h2>
                    </div>
                </div>
                @if ($pastChiefInstructors->isNotEmpty())
                    <div class="past-command-grid">
                        @foreach ($pastChiefInstructors as $index => $instructor)
                            @php($imageUrl = $instructor->image ? $mediaUrls->mediumOrOriginal($instructor->image) : null)
                            <article class="card past-command-card reveal">
                                <div class="command-img"><span
                                        class="command-no">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}"
                                        alt="{{ $instructor->nameForLocale($currentLocale) }}" loading="lazy">@else<div
                                            class="command-placeholder"><i class="fa-solid fa-user-tie"
                                                aria-hidden="true"></i><span>{{ $currentLocale === 'si' ? 'රූපය නොමැත' : 'Image unavailable' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="role">{{ $currentLocale === 'si' ? 'ප්‍රධාන උපදේශක' : 'Chief Instructor' }}
                                    </p>
                                    <h3>{{ $instructor->nameForLocale($currentLocale) }}</h3>
                                    <div class="command-meta"><span><i class="fa-regular fa-calendar"
                                                aria-hidden="true"></i>{{ $instructor->from_date->format('d.m.Y') }}</span><span><i
                                                class="fa-solid fa-arrow-right"
                                                aria-hidden="true"></i>{{ $instructor->to_date->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="news-empty"><i class="fa-solid fa-landmark" aria-hidden="true"></i>
                        <p>{{ $currentLocale === 'si' ? 'හිටපු ප්‍රධාන උපදේශකවරුන්ගේ තොරතුරු ඉක්මනින්.' : 'Past Chief Instructor records will be available soon.' }}
                        </p>
                    </div>
                @endif
            </div>
        </section>
    </article>
@endsection

@extends('theme-school-of-signals::layout')

@section('title', $settings?->default_seo_title ?: $settings?->site_name ?: config('app.name'))
@section('meta_description', $settings?->default_seo_description ?: $settings?->site_tagline ?:
    __('theme-school-of-signals::home.meta_description'))

    @php
        $mediaUrls = app(\App\Services\MediaUrlService::class);
        $homeLocale = in_array(app()->getLocale(), ['en', 'si', 'ta'], true) ? app()->getLocale() : 'en';
        $pageUrl = static fn(string $slug): string => $homeLocale === 'en'
            ? route('pages.show', ['slug' => $slug])
            : route('pages.show.localized', ['locale' => $homeLocale, 'slug' => $slug]);
        $newsIndexUrl =
            $homeLocale === 'en' ? route('news.index') : route('news.index.localized', ['locale' => $homeLocale]);
    @endphp

@section('content')
    <section class="hero" id="home">
        <div class="hero-frame" data-carousel>
            <div class="carousel-track" id="carouselTrack">
                @forelse ($slides as $slide)
                    @php
                        $slideTranslation = $slide->translation($homeLocale);

                        $slideTitle = $slideTranslation?->title ?? $slide->title;

                        $slideSubtitle = $slideTranslation?->subtitle ?? $slide->subtitle;

                        $slideButtonLabel = $slideTranslation?->button_label ?? $slide->button_label;

                        $slideButtonUrl = $slideTranslation?->button_url ?? $slide->button_url;

                        $slideImage = $slide->image ? $mediaUrls->mediumOrOriginal($slide->image) : null;
                    @endphp

                    <article class="slide" data-carousel-slide aria-label="{{ $slideTitle }}">
                        @if ($slideImage)
                            <img src="{{ $slideImage }}" alt="{{ $slideTitle }}"
                                @if ($loop->first) fetchpriority="high"
                                @else
                                    loading="lazy" @endif>
                        @endif

                        <div class="container hero-content">
                            <div class="hero-text {{ $loop->first ? 'reveal' : '' }}">
                                <span class="badge">
                                    <i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>

                                    {{ $settings?->site_tagline ?: __('theme-school-of-signals::home.sri_lanka_army') }}
                                </span>

                                @if ($loop->first)
                                    <h1>{{ $slideTitle }}</h1>
                                @else
                                    <h2 class="hero-slide-title">
                                        {{ $slideTitle }}
                                    </h2>
                                @endif

                                @if (is_string($slideSubtitle) && $slideSubtitle !== '')
                                    <p>{{ $slideSubtitle }}</p>
                                @endif

                                @if (is_string($slideButtonLabel) && $slideButtonLabel !== '' && is_string($slideButtonUrl) && $slideButtonUrl !== '')
                                    <div class="hero-actions">
                                        <a class="btn btn-green" href="{{ $slideButtonUrl }}">
                                            <span>
                                                {{ $slideButtonLabel }}
                                            </span>

                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="slide">
                        <img src="{{ asset('themes/school-of-signals/assets/images/image-placeholder.svg') }}"
                            alt="">
                        <div class="container hero-content">
                            <div class="hero-text reveal">
                                <span class="badge"><i class="fa-solid fa-tower-broadcast"
                                        aria-hidden="true"></i>{{ __('theme-school-of-signals::home.sri_lanka_army') }}</span>
                                <h1>{{ $settings?->site_name ?: config('app.name') }}</h1>
                                <p>{{ $settings?->site_tagline ?: __('theme-school-of-signals::home.hero.fallback_description') }}
                                </p>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>

            @if ($slides->count() > 1)
                <button class="carousel-arrow carousel-prev" id="prevSlide" type="button"
                    aria-label="{{ __('theme-school-of-signals::home.hero.previous_slide') }}"><i
                        class="fa-solid fa-chevron-left"></i></button>
                <button class="carousel-arrow carousel-next" id="nextSlide" type="button"
                    aria-label="{{ __('theme-school-of-signals::home.hero.next_slide') }}"><i
                        class="fa-solid fa-chevron-right"></i></button>
                <div class="carousel-dots" id="carouselDots"
                    aria-label="{{ __('theme-school-of-signals::home.hero.carousel_navigation') }}"></div>
            @endif
        </div>
    </section>

    <section class="section" id="mission">
        <div class="container mission-layout">
            <div class="reveal">
                <p class="kicker">{{ __('theme-school-of-signals::home.mission.kicker') }}</p>
                <h2 class="title-lg">{{ __('theme-school-of-signals::home.mission.title') }}</h2>
                <p class="lead">{{ __('theme-school-of-signals::home.mission.description') }}</p>
                <div class="feature-grid">
                    <article class="card">
                        <div class="card-body"><span class="icon-chip"><i class="fa-solid fa-satellite-dish"></i></span>
                            <h3>{{ __('theme-school-of-signals::home.mission.radio_title') }}</h3>
                            <p>{{ __('theme-school-of-signals::home.mission.radio_description') }}</p>
                        </div>
                    </article>
                    <article class="card">
                        <div class="card-body"><span class="icon-chip"><i class="fa-solid fa-server"></i></span>
                            <h3>{{ __('theme-school-of-signals::home.mission.ict_title') }}</h3>
                            <p>{{ __('theme-school-of-signals::home.mission.ict_description') }}</p>
                        </div>
                    </article>
                    <article class="card">
                        <div class="card-body"><span class="icon-chip"><i class="fa-solid fa-shield-halved"></i></span>
                            <h3>{{ __('theme-school-of-signals::home.mission.leadership_title') }}</h3>
                            <p>{{ __('theme-school-of-signals::home.mission.leadership_description') }}</p>
                        </div>
                    </article>
                </div>
            </div>

            <aside class="card notice-card reveal">
                <div class="notice-title">
                    <h3><i class="fa-solid fa-bullhorn"></i> {{ __('theme-school-of-signals::home.information.title') }}
                    </h3>
                </div>
                <div class="notice-list">
                    <div class="notice-row"><i class="fa-solid fa-circle-info"></i>
                        <div>
                            <strong>{{ __('theme-school-of-signals::home.information.course_title') }}</strong><span>{{ __('theme-school-of-signals::home.information.course_description') }}</span>
                        </div>
                    </div>
                    <div class="notice-row"><i class="fa-solid fa-calendar-days"></i>
                        <div>
                            <strong>{{ __('theme-school-of-signals::home.information.events_title') }}</strong><span>{{ __('theme-school-of-signals::home.information.events_description') }}</span>
                        </div>
                    </div>
                    <div class="notice-row"><i class="fa-solid fa-images"></i>
                        <div>
                            <strong>{{ __('theme-school-of-signals::home.information.galleries_title') }}</strong><span>{{ __('theme-school-of-signals::home.information.galleries_description') }}</span>
                        </div>
                    </div>
                    <div class="notice-row"><i class="fa-solid fa-phone-volume"></i>
                        <div>
                            <strong>{{ __('theme-school-of-signals::home.information.contact_title') }}</strong><span>{{ __('theme-school-of-signals::home.information.contact_description') }}</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section white" id="about">
        <div class="container about-grid">
            <div class="about-text reveal">
                <span class="decor"></span><span
                    class="eyebrow">{{ __('theme-school-of-signals::home.about.eyebrow') }}</span>
                <h2>{{ __('theme-school-of-signals::home.about.title_start') }}
                    <span>{{ __('theme-school-of-signals::home.about.title_end') }}</span>
                </h2>
                <p>{{ __('theme-school-of-signals::home.about.paragraph_one') }}</p>
                <p>{{ __('theme-school-of-signals::home.about.paragraph_two_start') }}
                    <strong>{{ __('theme-school-of-signals::home.about.motto') }}</strong>,
                    {{ __('theme-school-of-signals::home.about.paragraph_two_end') }}
                </p>
                <div class="about-points">
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i>
                        {{ __('theme-school-of-signals::home.about.discipline') }}</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i>
                        {{ __('theme-school-of-signals::home.about.technical_excellence') }}</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i>
                        {{ __('theme-school-of-signals::home.about.field_readiness') }}</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i>
                        {{ __('theme-school-of-signals::home.about.cyber_awareness') }}</div>
                </div>
                <a href="{{ $pageUrl('about-us') }}"
                    class="btn btn-green"><span>{{ __('theme-school-of-signals::home.about.explore_more') }}</span><i
                        class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="image-frame reveal">
                <img src="{{ asset('themes/school-of-signals/assets/images/image-placeholder.svg') }}"
                    alt="{{ __('theme-school-of-signals::home.about.image_alt') }}">
                <div class="years-badge">
                    <strong>{{ __('theme-school-of-signals::home.about.years') }}</strong><span>{{ __('theme-school-of-signals::home.about.years_label') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section white" id="news" aria-labelledby="news-section-title">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <p class="kicker">{{ __('theme-school-of-signals::home.news.kicker') }}</p>
                    <h2 class="title-lg" id="news-section-title">
                        {{ __('theme-school-of-signals::home.news.title') }}</h2>
                </div>
                <a class="btn btn-white" href="{{ $newsIndexUrl }}"
                    aria-label="{{ __('theme-school-of-signals::home.news.all') }}"><i
                        class="fa-solid fa-newspaper"></i><span>{{ __('theme-school-of-signals::home.news.all') }}</span></a>
            </div>
            <div class="news-grid">
                @forelse ($latestNews as $news)
                    @php($newsImage = $news->featuredImage ? $mediaUrls->mediumOrOriginal($news->featuredImage) : null)
                    <article class="card news-card reveal">
                        <div class="news-card-media">
                            <img src="{{ $newsImage ?: asset('themes/school-of-signals/assets/images/image-placeholder.svg') }}"
                                alt="{{ $newsImage ? $news->title : '' }}" loading="lazy">
                        </div>
                        <div class="card-body">
                            <div class="news-card-meta">
                                @if ($news->category)
                                    <span class="news-category">{{ $news->category->name }}</span>
                                @endif
                                @if ($news->published_at)
                                    <time class="date"
                                        datetime="{{ $news->published_at->toIso8601String() }}">{{ $news->published_at->format('d M Y') }}</time>
                                @endif
                            </div>
                            <h3>{{ $news->title }}</h3>
                            @if ($news->summary)
                                <p>{{ \Illuminate\Support\Str::limit($news->summary, 145) }}</p>
                            @endif
                            <a class="read-more-btn"
                                href="{{ $homeLocale === 'en' ? route('news.show', ['slug' => $news->slug]) : route('news.show.localized', ['locale' => $homeLocale, 'slug' => $news->slug]) }}"
                                aria-label="{{ __('theme-school-of-signals::home.news.read_more') }}: {{ $news->title }}"><span>{{ __('theme-school-of-signals::home.news.read_more') }}</span><i
                                    class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="news-empty">
                        <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                        <p>{{ __('theme-school-of-signals::home.news.empty') }}</p>
                        <a class="read-more-btn"
                            href="{{ $newsIndexUrl }}">{{ __('theme-school-of-signals::home.news.all') }}<i
                                class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @if ($schoolLeaders->isNotEmpty())
        <section class="section section-soft" id="leadership">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <p class="kicker">{{ __('theme-school-of-signals::home.leadership.kicker') }}</p>
                        <h2 class="title-lg">{{ __('theme-school-of-signals::home.leadership.title') }}</h2>
                    </div>
                </div>

                <div class="leadership-grid">
                    @foreach ($schoolLeaders as $leader)
                        @php($leaderImage = $leader->image ? $mediaUrls->mediumOrOriginal($leader->image) : null)
                        <article class="card leader-card reveal">
                            @if ($leaderImage)
                                <img src="{{ $leaderImage }}"
                                    alt="{{ $leader->nameForLocale($homeLocale) ?: $leader->titleForLocale($homeLocale) }}"
                                    loading="lazy">
                            @else
                                <div class="leader-placeholder" aria-hidden="true">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                            @endif
                            <div class="leader-body">
                                <p class="role">{{ $leader->titleForLocale($homeLocale) }}</p>
                                @if ($leader->nameForLocale($homeLocale) !== '')
                                    <h3>{{ $leader->nameForLocale($homeLocale) }}</h3>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section" id="media">
        <div class="container">
            <div class="section-head reveal">
                <div>
                    <p class="kicker">{{ __('theme-school-of-signals::home.galleries.kicker') }}</p>
                    <h2 class="title-lg">{{ __('theme-school-of-signals::home.galleries.title') }}</h2>
                </div>
                <a class="btn btn-white" href="{{ route('galleries.index') }}"><i
                        class="fa-solid fa-images"></i><span>{{ __('theme-school-of-signals::home.galleries.all') }}</span></a>
            </div>
            <div class="wings-grid">
                @forelse ($latestGalleries as $gallery)
                    @php($coverImage = $gallery->coverMedia ? $mediaUrls->mediumOrOriginal($gallery->coverMedia) : asset('themes/school-of-signals/assets/images/image-placeholder.svg'))
                    <article class="wing-card reveal" style="--wing-img: url('{{ $coverImage }}');">
                        <span class="wing-icon"><i class="fa-solid fa-camera"></i></span>
                        <h3>{{ $gallery->title }}</h3>
                        @if ($gallery->description)
                            <p>{{ \Illuminate\Support\Str::limit($gallery->description, 120) }}</p>
                        @endif
                        <a href="{{ route('galleries.show', ['slug' => $gallery->slug]) }}">{{ __('theme-school-of-signals::home.galleries.view') }}
                            <i class="fa-solid fa-arrow-right"></i></a>
                    </article>
                @empty
                    <p>{{ __('theme-school-of-signals::home.galleries.empty') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($latestDocuments->isNotEmpty())
        <section class="section white" id="documents">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <p class="kicker">{{ __('theme-school-of-signals::home.documents.kicker') }}</p>
                        <h2 class="title-lg">{{ __('theme-school-of-signals::home.documents.title') }}</h2>
                    </div>
                    <a class="btn btn-white" href="{{ route('documents.index') }}"><i
                            class="fa-solid fa-file-pdf"></i><span>{{ __('theme-school-of-signals::home.documents.all') }}</span></a>
                </div>
                <div class="feature-grid">
                    @foreach ($latestDocuments as $document)
                        <article class="card reveal">
                            <div class="card-body"><span class="icon-chip"><i class="fa-solid fa-file-pdf"></i></span>
                                <h3>{{ $document->title }}</h3><a class="read-more-btn"
                                    href="{{ route('documents.show', ['slug' => $document->slug]) }}"><span>{{ __('theme-school-of-signals::home.documents.view') }}</span><i
                                        class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section contact-section school-contact-cta" id="contact-short">
        <div class="container">
            <div class="school-contact-panel reveal">
                <div class="school-contact-intro">
                    <span class="school-contact-icon" aria-hidden="true">
                        <i class="fa-solid fa-headset"></i>
                    </span>

                    <div>
                        <p class="kicker">
                            {{ __('theme-school-of-signals::home.contact.kicker') }}
                        </p>

                        <h2 class="title-lg">
                            {{ __('theme-school-of-signals::home.contact.title') }}
                        </h2>

                        <p class="school-contact-description">
                            Contact the School of Signals for official inquiries,
                            training information and administrative assistance.
                        </p>
                    </div>
                </div>

                <div class="school-contact-details">
                    @if ($settings?->address)
                        <a class="school-contact-item" href="{{ $settings->map_url ?: route('contact.create') }}">
                            <span class="school-contact-item-icon">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            </span>

                            <span>
                                <small>Location</small>
                                <strong>{{ $settings->address }}</strong>
                            </span>
                        </a>
                    @endif

                    @if ($settings?->phone_primary)
                        <a class="school-contact-item"
                            href="tel:{{ preg_replace('/[^0-9+]/', '', $settings->phone_primary) }}">
                            <span class="school-contact-item-icon">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            </span>

                            <span>
                                <small>Telephone</small>
                                <strong>{{ $settings->phone_primary }}</strong>
                            </span>
                        </a>
                    @endif

                    @if ($settings?->email)
                        <a class="school-contact-item" href="mailto:{{ $settings->email }}">
                            <span class="school-contact-item-icon">
                                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            </span>

                            <span>
                                <small>Email Address</small>
                                <strong>{{ $settings->email }}</strong>
                            </span>
                        </a>
                    @endif
                </div>

                <div class="school-contact-action">
                    <p>Need further assistance?</p>

                    <a class="btn btn-green" href="{{ route('contact.create') }}">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        <span>
                            {{ __('theme-school-of-signals::home.contact.button') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container reveal">
            <span class="badge"><i
                    class="fa-solid fa-star"></i>{{ __('theme-school-of-signals::home.cta.badge') }}</span>
            <h2>{{ __('theme-school-of-signals::home.cta.title') }}</h2>
            <p>{{ __('theme-school-of-signals::home.cta.description') }}</p>
            <div class="cta-actions">
                <a class="btn btn-green" href="{{ $pageUrl('courses') }}"><i
                        class="fa-solid fa-graduation-cap"></i><span>{{ __('theme-school-of-signals::home.cta.courses') }}</span></a>
                <a class="btn btn-blue" href="{{ route('contact.create') }}"><i
                        class="fa-solid fa-envelope"></i><span>{{ __('theme-school-of-signals::home.cta.contact') }}</span></a>
            </div>
        </div>
    </section>
@endsection

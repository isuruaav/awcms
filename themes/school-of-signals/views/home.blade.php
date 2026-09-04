@extends('theme-school-of-signals::layout')

@section('title', $settings?->default_seo_title ?: $settings?->site_name ?: config('app.name'))
@section('meta_description', $settings?->default_seo_description ?: $settings?->site_tagline ?: 'Official website')

@php
    $mediaUrls = app(\App\Services\MediaUrlService::class);
    $pageUrl = static fn (string $slug): string => route('pages.show', ['slug' => $slug]);
@endphp

@section('content')
    <section class="hero" id="home">
        <div class="hero-frame" data-carousel>
            <div class="carousel-track" id="carouselTrack">
                @forelse ($slides as $slide)
                    @php($slideImage = $slide->image ? $mediaUrls->mediumOrOriginal($slide->image) : null)
                    <article class="slide">
                        @if ($slideImage)
                            <img src="{{ $slideImage }}" alt="{{ $slide->title }}">
                        @endif
                        <div class="container hero-content">
                            <div class="hero-text {{ $loop->first ? 'reveal' : '' }}">
                                <span class="badge">
                                    <i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>
                                    {{ $settings?->site_tagline ?: 'Sri Lanka Army' }}
                                </span>
                                @if ($loop->first)
                                    <h1>{{ $slide->title }}</h1>
                                @else
                                    <h2 class="hero-slide-title">{{ $slide->title }}</h2>
                                @endif
                                @if ($slide->subtitle)
                                    <p>{{ $slide->subtitle }}</p>
                                @endif
                                @if ($slide->button_label && $slide->button_url)
                                    <div class="hero-actions">
                                        <a class="btn btn-green" href="{{ $slide->button_url }}">
                                            <span>{{ $slide->button_label }}</span>
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="slide">
                        <img src="{{ asset('themes/school-of-signals/assets/images/image-placeholder.svg') }}" alt="">
                        <div class="container hero-content">
                            <div class="hero-text reveal">
                                <span class="badge"><i class="fa-solid fa-tower-broadcast" aria-hidden="true"></i>Sri Lanka Army</span>
                                <h1>{{ $settings?->site_name ?: config('app.name') }}</h1>
                                <p>{{ $settings?->site_tagline ?: 'Official information, news, publications and media.' }}</p>
                            </div>
                        </div>
                    </article>
                @endforelse
            </div>

            @if ($slides->count() > 1)
                <button class="carousel-arrow carousel-prev" id="prevSlide" type="button" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="carousel-arrow carousel-next" id="nextSlide" type="button" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
                <div class="carousel-dots" id="carouselDots" aria-label="Carousel navigation"></div>
            @endif
        </div>
    </section>

    <section class="section" id="mission">
        <div class="container mission-layout">
            <div class="reveal">
                <p class="kicker">Training Mission</p>
                <h2 class="title-lg">Communication discipline, technical skill and field readiness.</h2>
                <p class="lead">The School conducts technical and tactical training across communications, computer operations, information technology, cyber awareness and leadership development.</p>
                <div class="feature-grid">
                    <article class="card"><div class="card-body"><span class="icon-chip"><i class="fa-solid fa-satellite-dish"></i></span><h3>Radio &amp; Telecom</h3><p>Structured signal communication training for field and exchange operations.</p></div></article>
                    <article class="card"><div class="card-body"><span class="icon-chip"><i class="fa-solid fa-server"></i></span><h3>ICT &amp; Networks</h3><p>Practical computer, hardware, software, network and GIS learning pathways.</p></div></article>
                    <article class="card"><div class="card-body"><span class="icon-chip"><i class="fa-solid fa-shield-halved"></i></span><h3>Leadership</h3><p>Command, management and promotion courses for officers and other ranks.</p></div></article>
                </div>
            </div>

            <aside class="card notice-card reveal">
                <div class="notice-title"><h3><i class="fa-solid fa-bullhorn"></i> Information Desk</h3></div>
                <div class="notice-list">
                    <div class="notice-row"><i class="fa-solid fa-circle-info"></i><div><strong>Course Information</strong><span>View ICT, communication and leadership pathways.</span></div></div>
                    <div class="notice-row"><i class="fa-solid fa-calendar-days"></i><div><strong>Events &amp; Updates</strong><span>Read the latest school events and ceremonies.</span></div></div>
                    <div class="notice-row"><i class="fa-solid fa-images"></i><div><strong>Photo Galleries</strong><span>Explore published school galleries.</span></div></div>
                    <div class="notice-row"><i class="fa-solid fa-phone-volume"></i><div><strong>Contact Office</strong><span>Use the contact page for official information.</span></div></div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section white" id="about">
        <div class="container about-grid">
            <div class="about-text reveal">
                <span class="decor"></span><span class="eyebrow">School of Signals</span>
                <h2>Who <span>We Are</span></h2>
                <p>In 1964, Signal Training Squadron was formed under the 1st Regiment of Sri Lanka Signal Corps at Panagoda. The Squadron was elevated to a School of Signals in 1991.</p>
                <p>Keeping to the motto <strong>Technological Sound</strong>, the School maintains high standards of training in the field of Signals.</p>
                <div class="about-points">
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i> Military Discipline</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i> Technical Excellence</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i> Field Readiness</div>
                    <div class="about-point"><i class="fa-solid fa-check-circle"></i> Cyber Awareness</div>
                </div>
                <a href="{{ $pageUrl('about-us') }}" class="btn btn-green"><span>Explore More</span><i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="image-frame reveal">
                <img src="{{ asset('themes/school-of-signals/assets/images/image-placeholder.svg') }}" alt="School of Signals campus">
                <div class="years-badge"><strong>35+</strong><span>Years Excellence</span></div>
            </div>
        </div>
    </section>

    <section class="section white" id="news">
        <div class="container">
            <div class="section-head reveal">
                <div><p class="kicker">Latest Updates</p><h2 class="title-lg">News features</h2></div>
                <a class="btn btn-white" href="{{ route('news.index') }}"><i class="fa-solid fa-newspaper"></i><span>All News</span></a>
            </div>
            <div class="news-grid">
                @forelse ($latestNews as $news)
                    @php($newsImage = $news->featuredImage ? $mediaUrls->mediumOrOriginal($news->featuredImage) : null)
                    <article class="card news-card reveal">
                        @if ($newsImage)
                            <img src="{{ $newsImage }}" alt="{{ $news->title }}">
                        @endif
                        <div class="card-body">
                            <p class="date">{{ $news->published_at?->format('d M Y') }}</p>
                            <h3>{{ $news->title }}</h3>
                            @if ($news->summary)<p>{{ \Illuminate\Support\Str::limit($news->summary, 145) }}</p>@endif
                            <a class="read-more-btn" href="{{ route('news.show', ['slug' => $news->slug]) }}"><span>Read More</span><i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                @empty
                    <p>No published news yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($settings?->commander_name || $settings?->commander_message)
        @php($commanderImage = $settings?->commanderImage ? $mediaUrls->mediumOrOriginal($settings->commanderImage) : null)
        <section class="section section-soft" id="leadership">
            <div class="container about-grid">
                @if ($commanderImage)
                    <div class="image-frame reveal"><img src="{{ $commanderImage }}" alt="{{ $settings->commander_name }}"></div>
                @endif
                <div class="about-text reveal">
                    <p class="kicker">Leadership Message</p>
                    <h2>{{ $settings->commander_name }}</h2>
                    @if ($settings->commander_title)<p class="eyebrow">{{ $settings->commander_title }}</p>@endif
                    @if ($settings->commander_message)<p>{!! nl2br(e($settings->commander_message)) !!}</p>@endif
                </div>
            </div>
        </section>
    @endif

    <section class="section" id="media">
        <div class="container">
            <div class="section-head reveal">
                <div><p class="kicker">Media</p><h2 class="title-lg">Recent galleries</h2></div>
                <a class="btn btn-white" href="{{ route('galleries.index') }}"><i class="fa-solid fa-images"></i><span>All Galleries</span></a>
            </div>
            <div class="wings-grid">
                @forelse ($latestGalleries as $gallery)
                    @php($coverImage = $gallery->coverMedia ? $mediaUrls->mediumOrOriginal($gallery->coverMedia) : asset('themes/school-of-signals/assets/images/image-placeholder.svg'))
                    <article class="wing-card reveal" style="--wing-img: url('{{ $coverImage }}');">
                        <span class="wing-icon"><i class="fa-solid fa-camera"></i></span>
                        <h3>{{ $gallery->title }}</h3>
                        @if ($gallery->description)<p>{{ \Illuminate\Support\Str::limit($gallery->description, 120) }}</p>@endif
                        <a href="{{ route('galleries.show', ['slug' => $gallery->slug]) }}">View gallery <i class="fa-solid fa-arrow-right"></i></a>
                    </article>
                @empty
                    <p>No published galleries yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($latestDocuments->isNotEmpty())
        <section class="section white" id="documents">
            <div class="container">
                <div class="section-head reveal">
                    <div><p class="kicker">Downloads</p><h2 class="title-lg">Recent documents</h2></div>
                    <a class="btn btn-white" href="{{ route('documents.index') }}"><i class="fa-solid fa-file-pdf"></i><span>All Documents</span></a>
                </div>
                <div class="feature-grid">
                    @foreach ($latestDocuments as $document)
                        <article class="card reveal"><div class="card-body"><span class="icon-chip"><i class="fa-solid fa-file-pdf"></i></span><h3>{{ $document->title }}</h3><a class="read-more-btn" href="{{ route('documents.show', ['slug' => $document->slug]) }}"><span>View Document</span><i class="fa-solid fa-arrow-right"></i></a></div></article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section contact-section" id="contact-short">
        <div class="container">
            <div class="section-head reveal">
                <div><p class="kicker" style="color:var(--green-100);">Quick Contact</p><h2 class="title-lg" style="color:#fff;">Need official information?</h2></div>
                <a class="btn btn-green" href="{{ route('contact.create') }}"><i class="fa-solid fa-envelope"></i><span>Contact Us</span></a>
            </div>
            <div class="contact-grid reveal">
                <div class="contact-box">
                    <h3>{{ $settings?->site_name ?: config('app.name') }}</h3>
                    @if ($settings?->address)<div class="contact-row"><i class="fa-solid fa-location-dot"></i><span>{{ $settings->address }}</span></div>@endif
                    @if ($settings?->phone_primary)<div class="contact-row"><i class="fa-solid fa-phone"></i><span>{{ $settings->phone_primary }}</span></div>@endif
                    @if ($settings?->email)<div class="contact-row"><i class="fa-solid fa-envelope"></i><span>{{ $settings->email }}</span></div>@endif
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container reveal">
            <span class="badge"><i class="fa-solid fa-star"></i>School of Signals</span>
            <h2>Communication and cyber excellence through disciplined training.</h2>
            <p>Explore our courses, latest news, publications and official school information.</p>
            <div class="cta-actions">
                <a class="btn btn-green" href="{{ $pageUrl('courses') }}"><i class="fa-solid fa-graduation-cap"></i><span>View Courses</span></a>
                <a class="btn btn-blue" href="{{ route('contact.create') }}"><i class="fa-solid fa-envelope"></i><span>Get in Touch</span></a>
            </div>
        </div>
    </section>
@endsection

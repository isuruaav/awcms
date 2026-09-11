@php
    $publicSiteName = $siteSettings?->site_name ?: config('app.name', 'AWCMS');
    $publicDescription = $siteSettings?->site_tagline ?: 'Sri Lanka School of Signals';
    $currentLocale = in_array(app()->getLocale(), ['en', 'si', 'ta'], true) ? app()->getLocale() : 'en';
    $themeAssetBase = asset('themes/school-of-signals/assets');
    $mediaUrls = app(\App\Services\MediaUrlService::class);
    $logoUrl = $siteSettings?->logo
        ? $mediaUrls->mediumOrOriginal($siteSettings->logo)
        : $themeAssetBase . '/images/logo.png';
    $faviconUrl = $siteSettings?->favicon ? $mediaUrls->original($siteSettings->favicon) : null;
    $configuredThemeLocales = config('awcms.theme_locales.school-of-signals', ['en', 'si', 'ta']);
    $supportedThemeLocales = is_array($configuredThemeLocales)
        ? array_values(
            array_filter(
                $configuredThemeLocales,
                static fn(mixed $locale): bool => is_string($locale) && in_array($locale, ['en', 'si', 'ta'], true),
            ),
        )
        : ['en', 'si', 'ta'];
    $supportedThemeLocales = $supportedThemeLocales !== [] ? $supportedThemeLocales : ['en'];
    $languageOptions =
        isset($languageVersions) && is_array($languageVersions)
            ? array_values(
                array_filter(
                    $languageVersions,
                    static fn(mixed $language): bool => is_array($language) &&
                        isset($language['code']) &&
                        is_string($language['code']) &&
                        in_array($language['code'], $supportedThemeLocales, true),
                ),
            )
            : [];
    $localeNames = ['en' => 'English', 'si' => 'සිංහල', 'ta' => 'தமிழ்'];
    $themeLayoutRenderer = app(\App\Services\ThemeLayoutRenderer::class);

    $managedHeaderLayout = $themeLayoutRenderer->render(
        themeSlug: 'school-of-signals',
        region: \App\Enums\ThemeLayoutRegion::Header,
        siteSettings: $siteSettings,
        primaryMenu: $primaryMenu,
        socialLinks: $publicSocialLinks,
        languageOptions: $languageOptions,
        currentLocale: $currentLocale,
        siteName: $publicSiteName,
        siteTagline: $publicDescription,
        logoUrl: $logoUrl,
    );

    $managedFooterLayout = $themeLayoutRenderer->render(
        themeSlug: 'school-of-signals',
        region: \App\Enums\ThemeLayoutRegion::Footer,
        siteSettings: $siteSettings,
        primaryMenu: $primaryMenu,
        socialLinks: $publicSocialLinks,
        languageOptions: $languageOptions,
        currentLocale: $currentLocale,
        siteName: $publicSiteName,
        siteTagline: $publicDescription,
        logoUrl: $logoUrl,
    );
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" data-theme-asset-base="{{ $themeAssetBase }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $publicSiteName)</title>
    <meta name="description" content="@yield('meta_description', $publicDescription)">
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif
    @yield('meta')
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link rel="stylesheet" href="{{ $themeAssetBase }}/fonts/fontawesome-7/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ $themeAssetBase }}/css/theme.css">
    <link rel="stylesheet" href="{{ $themeAssetBase }}/css/navbar.css">

    @if ($managedHeaderLayout !== null && $managedHeaderLayout['css'] !== '')
        <style data-awcms-theme-layout="header">
            {!! $managedHeaderLayout['css'] !!}
        </style>
    @endif

    @if ($managedFooterLayout !== null && $managedFooterLayout['css'] !== '')
        <style data-awcms-theme-layout="footer">
            {!! $managedFooterLayout['css'] !!}
        </style>
    @endif

    @stack('styles')
</head>

<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    @if ($managedHeaderLayout !== null)
        {!! $managedHeaderLayout['html'] !!}
    @else
        <div class="topbar">
            <div class="container">
                <div class="topbar-left">
                    <span>
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        {{ $publicDescription }}
                    </span>
                </div>
                <div class="topbar-right">
                    <span class="motto">Technological Sound</span>
                    <a class="portal-small" href="https://lms.army.lk" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-laptop-code" aria-hidden="true"></i>LMS Portal
                    </a>
                </div>
            </div>
        </div>

        <header class="header">
            <div class="container nav-wrap">
                <a class="brand" href="{{ url('/') }}" aria-label="{{ $publicSiteName }} home">
                    <span class="brand-logo">
                        <img src="{{ $logoUrl }}" alt="{{ $publicSiteName }} logo">
                    </span>
                    <span>
                        <span class="brand-title">{{ $publicSiteName }}</span>
                        <span class="brand-subtitle">Sri Lanka Army</span>
                    </span>
                </a>

                <nav class="desktop-nav" aria-label="Primary navigation">
                    @if ($primaryMenu)
                        @foreach ($primaryMenu->rootItems->where('is_active', true) as $menuItem)
                            @php($children = $menuItem->children->where('is_active', true))
                            @if ($children->isNotEmpty())
                                <div class="drop">
                                    <a class="drop-toggle" href="{{ $menuItem->resolvedUrl($currentLocale) }}"
                                        @if ($menuItem->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                        {{ $menuItem->labelForLocale($currentLocale) }}
                                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    </a>
                                    <div class="dropdown">
                                        @foreach ($children as $child)
                                            <a href="{{ $child->resolvedUrl($currentLocale) }}"
                                                @if ($child->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                                {{ $child->labelForLocale($currentLocale) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $menuItem->resolvedUrl($currentLocale) }}"
                                    @if ($menuItem->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                    {{ $menuItem->labelForLocale($currentLocale) }}
                                </a>
                            @endif
                        @endforeach
                    @else
                        <a href="{{ url('/') }}">Home</a>
                        <a
                            href="{{ $currentLocale === 'en' ? route('news.index') : route('news.index.localized', ['locale' => $currentLocale]) }}">News</a>
                        <a href="{{ route('galleries.index') }}">Gallery</a>
                    @endif

                    <div class="drop">
                        <a class="drop-toggle"
                            href="{{ $currentLocale === 'en' ? route('history.past-commandants') : route('history.past-commandants.localized') }}">
                            {{ $currentLocale === 'si' ? 'ඉතිහාසය' : 'History' }}
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </a>
                        <div class="dropdown">
                            <a href="#">
                                {{ $currentLocale === 'si' ? 'පාසලේ ඉතිහාසය' : 'School History' }}
                            </a>
                            <a
                                href="{{ $currentLocale === 'en' ? route('history.past-commandants') : route('history.past-commandants.localized') }}">
                                {{ $currentLocale === 'si' ? 'හිටපු සේනාවිධායකවරු' : 'Past Commandants' }}
                            </a>
                            <a href="#">
                                {{ $currentLocale === 'si' ? 'සුවිශේෂී සන්ධිස්ථාන' : 'Milestones' }}
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="nav-actions">
                    <div class="language-switch" data-language-switch>
                        <button class="language-toggle" type="button" aria-label="Change language" aria-haspopup="true"
                            aria-expanded="false" data-language-toggle>
                            <i class="fa-solid fa-language" aria-hidden="true"></i>
                            <span class="language-current">{{ strtoupper($currentLocale) }}</span>
                        </button>
                        <div class="language-menu" role="menu" aria-label="Language options">
                            @forelse ($languageOptions as $language)
                                @if (($language['available'] ?? false) && isset($language['url'], $language['code']))
                                    <a class="language-option {{ $language['code'] === $currentLocale ? 'active' : '' }}"
                                        href="{{ $language['url'] }}" lang="{{ $language['code'] }}" role="menuitem"
                                        data-language-option data-lang="{{ $language['code'] }}">
                                        <span>{{ $localeNames[$language['code']] ?? strtoupper($language['code']) }}</span>
                                        <strong>{{ strtoupper($language['code']) }}</strong>
                                    </a>
                                @endif
                            @empty
                                <span
                                    class="language-option active"><span>{{ $localeNames[$currentLocale] }}</span><strong>{{ strtoupper($currentLocale) }}</strong></span>
                            @endforelse
                        </div>
                    </div>

                    <a href="https://lms.army.lk" target="_blank" rel="noopener noreferrer"
                        class="btn btn-green portal-button">
                        <i class="fa-solid fa-laptop-code" aria-hidden="true"></i><span>LMS Portal</span>
                    </a>

                    <button class="mobile-btn" id="mobileToggle" type="button" aria-expanded="false"
                        aria-label="Open menu" data-mobile-toggle>
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="mobile-menu" id="mobileMenu" data-mobile-menu>
                <div class="container">
                    @if ($languageOptions !== [])
                        <div class="mobile-language" aria-label="Language selection">
                            @foreach ($languageOptions as $language)
                                @if (($language['available'] ?? false) && isset($language['url'], $language['code']))
                                    <a class="{{ $language['code'] === $currentLocale ? 'active' : '' }}"
                                        href="{{ $language['url'] }}" lang="{{ $language['code'] }}"
                                        data-language-option data-lang="{{ $language['code'] }}">
                                        <span><i class="fa-solid fa-language" aria-hidden="true"></i>
                                            {{ $localeNames[$language['code']] ?? strtoupper($language['code']) }}</span>
                                        <strong>{{ strtoupper($language['code']) }}</strong>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if ($primaryMenu)
                        @foreach ($primaryMenu->rootItems->where('is_active', true) as $menuItem)
                            <a href="{{ $menuItem->resolvedUrl($currentLocale) }}">
                                <span>{{ $menuItem->labelForLocale($currentLocale) }}</span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                            @foreach ($menuItem->children->where('is_active', true) as $child)
                                <a class="mobile-submenu-link" href="{{ $child->resolvedUrl($currentLocale) }}">
                                    <span>{{ $child->labelForLocale($currentLocale) }}</span>
                                    <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        @endforeach
                    @else
                        <a href="{{ url('/') }}"><span>Home</span><i class="fa-solid fa-arrow-right"></i></a>
                        <a href="{{ route('news.index') }}"><span>News</span><i
                                class="fa-solid fa-arrow-right"></i></a>
                        <a href="{{ route('galleries.index') }}"><span>Gallery</span><i
                                class="fa-solid fa-arrow-right"></i></a>
                    @endif

                    <a
                        href="{{ $currentLocale === 'en' ? route('history.past-commandants') : route('history.past-commandants.localized') }}">
                        <span>{{ $currentLocale === 'si' ? 'හිටපු සේනාවිධායකවරු' : 'Past Commandants' }}</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </header>
    @endif
    <main id="main-content">
        @yield('content')
    </main>
    @if ($managedFooterLayout !== null)
        {!! $managedFooterLayout['html'] !!}
    @else
        <footer class="footer">
            <div class="container footer-grid">
                <section>
                    <h2>{{ $publicSiteName }}</h2>
                    <p>{{ $publicDescription }}</p>
                </section>
                <section>
                    <h2>Quick Links</h2>
                    <a href="{{ url('/') }}">Home</a>
                    <a
                        href="{{ $currentLocale === 'en' ? route('news.index') : route('news.index.localized', ['locale' => $currentLocale]) }}">News</a>
                    <a href="{{ route('galleries.index') }}">Gallery</a>
                </section>
                <section>
                    <h2>Contact</h2>
                    @if ($siteSettings?->email)
                        <a href="mailto:{{ $siteSettings->email }}">{{ $siteSettings->email }}</a>
                    @endif
                    @if ($siteSettings?->phone_primary)
                        <a href="tel:{{ $siteSettings->phone_primary }}">{{ $siteSettings->phone_primary }}</a>
                    @endif
                    @if ($siteSettings?->address)
                        <p>{{ $siteSettings->address }}</p>
                    @endif
                </section>
                @if ($publicSocialLinks->isNotEmpty())
                    <section>
                        <h2>Follow Us</h2>
                        <div class="footer-socials">
                            @foreach ($publicSocialLinks as $socialLink)
                                <a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer"
                                    aria-label="{{ $socialLink->platform }}">
                                    <i class="{{ $socialLink->icon_class ?: 'fa-solid fa-link' }}"
                                        aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
            <div class="footer-bottom">
                <div class="container">
                    <p>&copy; {{ now()->year }} {{ $publicSiteName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    @endif
    <button class="scroll-top" type="button" aria-label="Scroll to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

    <script src="{{ $themeAssetBase }}/js/theme.js" defer></script>
    @stack('scripts')
</body>

</html>

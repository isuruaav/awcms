<?php

namespace App\Services;

use App\Enums\ThemeLayoutLocale;
use App\Enums\ThemeLayoutRegion;
use App\Enums\ThemeLayoutStatus;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Models\ThemeLayout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class ThemeLayoutRenderer
{
    public function __construct(
        private readonly ThemeLayoutSanitizer $sanitizer,
    ) {}

    /**
     * Render a published layout without evaluating database content as Blade,
     * PHP or JavaScript.
     *
     * @param  iterable<SocialLink>  $socialLinks
     * @param  array<int|string, mixed>  $languageOptions
     * @return array{html: string, css: string}|null
     */
    public function render(
        string $themeSlug,
        ThemeLayoutRegion $region,
        ?SiteSetting $siteSettings,
        ?Menu $primaryMenu,
        iterable $socialLinks,
        array $languageOptions,
        string $currentLocale,
        string $siteName,
        string $siteTagline,
        ?string $logoUrl,
    ): ?array {
        if (
            preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $themeSlug) !== 1
            || ! Schema::hasTable('theme_layouts')
        ) {
            return null;
        }

        $requestedLocale = ThemeLayoutLocale::fromApplicationLocale($currentLocale);

        $layout = $this->publishedLayout(
            themeSlug: $themeSlug,
            region: $region,
            locale: $requestedLocale,
        );

        if (
            ! $layout instanceof ThemeLayout
            && $requestedLocale !== ThemeLayoutLocale::English
        ) {
            $layout = $this->publishedLayout(
                themeSlug: $themeSlug,
                region: $region,
                locale: ThemeLayoutLocale::English,
            );
        }

        if (
            ! $layout instanceof ThemeLayout
            || ! is_string($layout->published_html)
        ) {
            return null;
        }

        try {
            $html = $this->sanitizer->sanitizeHtml($layout->published_html);
            $css = $this->sanitizer->sanitizeCss($layout->published_css);
        } catch (ValidationException) {
            return null;
        }

        if ($html === '') {
            return null;
        }

        $locale = $requestedLocale->value;

        $replacements = [
            '[[site_logo]]' => $this->siteLogo($logoUrl, $siteName),
            '[[site_name]]' => $this->escape($siteName),
            '[[site_tagline]]' => $this->escape($siteTagline),
            '[[responsive_primary_navigation]]' => $this->responsivePrimaryNavigation(
                $primaryMenu,
                $locale,
            ),
            '[[mobile_menu_toggle]]' => $this->mobileMenuToggle(),
            '[[language_switcher]]' => $this->languageSwitcher(
                $languageOptions,
                $locale,
            ),
            '[[social_links]]' => $this->socialLinks($socialLinks),
            '[[contact_details]]' => $this->contactDetails($siteSettings),
            '[[copyright_year]]' => (string) now()->year,
        ];
        $renderedHtml = str_ireplace(
            array_keys($replacements),
            array_values($replacements),
            $html,
        );

        return [
            'html' => $this->replaceSiteUrlPlaceholders($renderedHtml),
            'css' => $css,
        ];
    }

    private function replaceSiteUrlPlaceholders(string $html): string
    {
        $renderedHtml = preg_replace_callback(
            '/\[\[site_url:(\/[A-Za-z0-9._~\/%\-]*)\]\]/i',
            function (array $matches): string {
                $path = $matches[1];

                return $this->escape(url($path));
            },
            $html,
        );

        return is_string($renderedHtml)
            ? $renderedHtml
            : $html;
    }

    private function publishedLayout(
        string $themeSlug,
        ThemeLayoutRegion $region,
        ThemeLayoutLocale $locale,
    ): ?ThemeLayout {
        $layout = ThemeLayout::query()
            ->where('theme_slug', $themeSlug)
            ->where('region', $region->value)
            ->where('locale', $locale->value)
            ->where('status', ThemeLayoutStatus::Published->value)
            ->first();

        return $layout instanceof ThemeLayout
            ? $layout
            : null;
    }

    private function siteLogo(?string $logoUrl, string $siteName): string
    {
        if (! is_string($logoUrl) || trim($logoUrl) === '') {
            return '';
        }

        $safeLogoUrl = $this->safeHref($logoUrl);

        if ($safeLogoUrl === '#') {
            return '';
        }

        return '<img class="cms-site-logo" src="'
            .$this->escape($safeLogoUrl)
            .'" alt="'
            .$this->escape($siteName)
            .' logo">';
    }

    private function mobileMenuToggle(): string
    {
        return '<button id="mobileToggle" class="mobile-btn cms-mobile-menu-toggle" type="button" aria-label="Open menu" aria-controls="mobileMenu" aria-expanded="false" data-mobile-toggle>'
            .'<i class="fa-solid fa-bars" aria-hidden="true"></i>'
            .'<span class="cms-mobile-menu-toggle-label">Menu</span>'
            .'</button>';
    }

    private function responsivePrimaryNavigation(
        ?Menu $menu,
        string $locale,
    ): string {
        $menuHtml = $this->primaryMenu($menu, $locale);

        return '<div class="cms-responsive-navigation">'
            .'<button id="mobileToggle" class="cms-mobile-menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-mobile-toggle>'
            .'<i class="fa-solid fa-bars" aria-hidden="true"></i>'
            .'<span>Menu</span>'
            .'</button>'
            .'<div id="mobileMenu" class="cms-responsive-menu-panel" data-mobile-menu>'
            .$menuHtml
            .'</div>'
            .'</div>';
    }

    private function primaryMenu(?Menu $menu, string $locale): string
    {
        if (! $menu instanceof Menu) {
            return $this->fallbackMenu($locale);
        }

        $items = $menu->rootItems
            ->where('is_active', true);

        if ($items->isEmpty()) {
            return $this->fallbackMenu($locale);
        }

        $html = '<ul class="cms-primary-menu">';

        foreach ($items as $item) {
            $html .= $this->menuItem($item, $locale);
        }

        return $html.'</ul>';
    }

    private function menuItem(MenuItem $item, string $locale): string
    {
        $children = $item->children
            ->where('is_active', true);

        $hasChildren = $children->isNotEmpty();

        $itemClass = $hasChildren
            ? 'cms-menu-item cms-menu-parent'
            : 'cms-menu-item';

        $attributes = $item->open_in_new_tab
            ? ' target="_blank" rel="noopener noreferrer"'
            : '';

        $html = '<li class="'.$itemClass.'"><a href="'
            .$this->escape(
                $this->safeHref($item->resolvedUrl($locale)),
            )
            .'"'
            .$attributes
            .'>'
            .$this->escape($item->labelForLocale($locale))
            .'</a>';

        if ($hasChildren) {
            $html .= '<ul class="cms-primary-submenu">';

            foreach ($children as $child) {
                $childAttributes = $child->open_in_new_tab
                    ? ' target="_blank" rel="noopener noreferrer"'
                    : '';

                $html .= '<li class="cms-menu-item"><a href="'
                    .$this->escape(
                        $this->safeHref($child->resolvedUrl($locale)),
                    )
                    .'"'
                    .$childAttributes
                    .'>'
                    .$this->escape($child->labelForLocale($locale))
                    .'</a></li>';
            }

            $html .= '</ul>';
        }

        return $html.'</li>';
    }

    private function fallbackMenu(string $locale): string
    {
        $newsUrl = $locale === ThemeLayoutLocale::English->value
            ? route('news.index')
            : route('news.index.localized', [
                'locale' => $locale,
            ]);

        $links = [
            'Home' => route('home'),
            'News' => $newsUrl,
            'Gallery' => route('galleries.index'),
        ];

        $html = '<ul class="cms-primary-menu">';

        foreach ($links as $label => $url) {
            $html .= '<li class="cms-menu-item"><a href="'
                .$this->escape($this->safeHref($url))
                .'">'
                .$this->escape($label)
                .'</a></li>';
        }

        return $html.'</ul>';
    }

    /**
     * @return list<array{code: string, available: true, url: string}>
     */
    private function homeLanguageOptions(): array
    {
        $options = [
            [
                'code' => 'en',
                'available' => true,
                'url' => url('/'),
            ],
        ];

        if (! Schema::hasTable('pages')) {
            return $options;
        }

        foreach (['si', 'ta'] as $locale) {
            $homePageExists = Page::query()
                ->published()
                ->where('locale', $locale)
                ->where('slug', 'home')
                ->exists();

            if (! $homePageExists) {
                continue;
            }

            $options[] = [
                'code' => $locale,
                'available' => true,
                'url' => route('pages.show.localized', [
                    'locale' => $locale,
                    'slug' => 'home',
                ]),
            ];
        }

        return $options;
    }

    /**
     * @param  array<int|string, mixed>  $languageOptions
     */
    private function languageSwitcher(
        array $languageOptions,
        string $currentLocale,
    ): string {
        if ($languageOptions === []) {
            $languageOptions = $this->homeLanguageOptions();
        }

        $localeNames = [
            'en' => 'English',
            'si' => 'සිංහල',
            'ta' => 'தமிழ்',
        ];
        $links = '';

        foreach ($languageOptions as $option) {
            if (
                ! is_array($option)
                || ($option['available'] ?? false) !== true
            ) {
                continue;
            }

            $code = $option['code'] ?? null;
            $url = $option['url'] ?? null;

            if (
                ! is_string($code)
                || ! array_key_exists($code, $localeNames)
                || ! is_string($url)
            ) {
                continue;
            }

            $activeClass = $code === $currentLocale
                ? ' active'
                : '';

            $links .= '<a class="cms-language-option'
                .$activeClass
                .'" href="'
                .$this->escape($this->safeHref($url))
                .'" lang="'
                .$this->escape($code)
                .'" data-language-option data-lang="'
                .$this->escape($code)
                .'">'
                .$this->escape($localeNames[$code])
                .'</a>';
        }

        if ($links === '') {
            return '<span class="cms-language-current" lang="'
                .$this->escape($currentLocale)
                .'">'
                .$this->escape(strtoupper($currentLocale))
                .'</span>';
        }

        return '<nav class="cms-language-switcher" aria-label="Language selection">'
            .$links
            .'</nav>';
    }

    /**
     * @param  iterable<SocialLink>  $socialLinks
     */
    private function socialLinks(iterable $socialLinks): string
    {
        $links = '';

        foreach ($socialLinks as $socialLink) {
            $url = $this->safeHref($socialLink->url);

            if ($url === '#') {
                continue;
            }

            $label = is_string($socialLink->label)
                && trim($socialLink->label) !== ''
                    ? $socialLink->label
                    : $socialLink->platform;

            $links .= '<a class="cms-social-link" href="'
                .$this->escape($url)
                .'" target="_blank" rel="noopener noreferrer">'
                .$this->escape($label)
                .'</a>';
        }

        return $links === ''
            ? ''
            : '<nav class="cms-social-links" aria-label="Social media">'
                .$links
                .'</nav>';
    }

    private function contactDetails(?SiteSetting $settings): string
    {
        if (! $settings instanceof SiteSetting) {
            return '';
        }

        $details = '';

        if (
            is_string($settings->address)
            && trim($settings->address) !== ''
        ) {
            $details .= '<p class="cms-contact-address">'
                .nl2br(
                    $this->escape($settings->address),
                    false,
                )
                .'</p>';
        }

        $details .= $this->phoneLink($settings->phone_primary);
        $details .= $this->phoneLink($settings->phone_secondary);

        if (
            is_string($settings->email)
            && filter_var(
                $settings->email,
                FILTER_VALIDATE_EMAIL,
            ) !== false
        ) {
            $email = trim($settings->email);

            $details .= '<a class="cms-contact-email" href="mailto:'
                .$this->escape($email)
                .'">'
                .$this->escape($email)
                .'</a>';
        }

        return $details === ''
            ? ''
            : '<div class="cms-contact-details">'
                .$details
                .'</div>';
    }

    private function phoneLink(?string $phone): string
    {
        if (! is_string($phone) || trim($phone) === '') {
            return '';
        }

        $number = preg_replace('/[^0-9+]/', '', $phone);

        if (! is_string($number) || $number === '') {
            return '';
        }

        return '<a class="cms-contact-phone" href="tel:'
            .$this->escape($number)
            .'">'
            .$this->escape(trim($phone))
            .'</a>';
    }

    private function safeHref(string $url): string
    {
        $url = trim($url);

        if ($url === '#') {
            return $url;
        }

        if (
            str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
        ) {
            return $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '#';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme)
            && in_array(
                strtolower($scheme),
                ['http', 'https', 'mailto', 'tel'],
                true,
            )
                ? $url
                : '#';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }
}

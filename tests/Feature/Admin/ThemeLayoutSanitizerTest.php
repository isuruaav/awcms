<?php

use App\Services\ThemeLayoutSanitizer;
use Illuminate\Validation\ValidationException;

it('preserves safe semantic html tailwind classes and approved placeholders', function (): void {
    $clean = app(ThemeLayoutSanitizer::class)->sanitizeHtml(
        '<header class="flex items-center"><nav>[[primary_menu]]</nav></header>',
    );

    expect($clean)
        ->toContain('<header class="flex items-center">')
        ->toContain('[[primary_menu]]')
        ->not->toContain('<script');
});

it('removes unsafe attributes and uri schemes from otherwise safe html', function (): void {
    $clean = app(ThemeLayoutSanitizer::class)->sanitizeHtml(
        '<header onclick="alert(1)"><a href="javascript:alert(1)">Link</a>[[site_name]]</header>',
    );

    expect($clean)
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->toContain('[[site_name]]');
});

it('rejects executable template syntax', function (string $html): void {
    app(ThemeLayoutSanitizer::class)->sanitizeHtml($html);
})->with([
    '<?php echo "unsafe"; ?>',
    '{{ config("app.key") }}',
    '@php echo "unsafe"; @endphp',
    '<script>alert(1)</script>',
])->throws(ValidationException::class);

it('rejects unknown placeholders', function (): void {
    app(ThemeLayoutSanitizer::class)->sanitizeHtml(
        '<header>[[execute_code]]</header>',
    );
})->throws(ValidationException::class);

it('accepts local presentation css', function (): void {
    $clean = app(ThemeLayoutSanitizer::class)->sanitizeCss(
        '.site-header { display: flex; color: #ffffff; }',
    );

    expect($clean)->toBe('.site-header { display: flex; color: #ffffff; }');
});

it('rejects css capable of loading external resources or breaking the style element', function (string $css): void {
    app(ThemeLayoutSanitizer::class)->sanitizeCss($css);
})->with([
    '@import "https://example.com/tracker.css";',
    '.header { background: url("https://example.com/pixel") }',
    '</style><script>alert(1)</script>',
    '.header { background: image-set("https://example.com/image.png" 1x) }',
])->throws(ValidationException::class);

it('preserves a safe local site url placeholder inside an href attribute', function (): void {
    $clean = app(ThemeLayoutSanitizer::class)->sanitizeHtml(
        '<nav><a class="nav-link" href="[[site_url:/en/pages/about-us-2]]">About Us</a></nav>',
    );

    expect($clean)
        ->toContain('href="[[site_url:/en/pages/about-us-2]]"')
        ->toContain('class="nav-link"')
        ->toContain('About Us');
});

it('rejects site url placeholders outside an exact local href value', function (string $html): void {
    app(ThemeLayoutSanitizer::class)->sanitizeHtml($html);
})->with([
    'outside an attribute' => '<div>[[site_url:/admin]]</div>',
    'combined with other href text' => '<a href="prefix-[[site_url:/news]]">News</a>',
    'external absolute url' => '<a href="[[site_url:https://example.com]]">External</a>',
    'javascript url' => '<a href="[[site_url:javascript:alert(1)]]">Unsafe</a>',
    'inside a class attribute' => '<div class="[[site_url:/news]]">News</div>',
])->throws(ValidationException::class);

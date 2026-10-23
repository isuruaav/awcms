<?php

use App\Services\PageCss;

it('scopes course selectors and preserves responsive rules and declaration values', function (): void {
    $css = <<<'CSS'
/* course cards */
.sos-courses-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.sos-course-card { background: linear-gradient(135deg, #102b43 0%, #245578 100%); }
.sos-course-card:nth-child(even) { background: #174c33; }
.sos-course-card::before { content: ""; width: 5px; }
.sos-course-card h3, .sos-course-card a { color: #ffffff; }
@media (hover: hover) { .sos-course-card:hover { transform: translateY(-5px); } }
@media (max-width: 767px) { .sos-courses-grid { grid-template-columns: 1fr; } }
@media (prefers-reduced-motion: reduce) { .sos-course-card, .sos-course-card::before { transition: none; } }
CSS;

    $service = new PageCss;
    $compiled = $service->forPage($css, 42);

    expect($service->error($css))->toBeNull()
        ->and($compiled)
        ->toContain(':where(#cms-page-42) .sos-course-card::before')
        ->toContain(':where(#cms-page-42) .sos-course-card:nth-child(even)')
        ->toContain(":where(#cms-page-42) .sos-course-card h3,\n:where(#cms-page-42) .sos-course-card a")
        ->toContain('repeat(2, minmax(0, 1fr))')
        ->toContain('linear-gradient(135deg, #102b43 0%, #245578 100%)')
        ->toContain('@media (max-width: 767px)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

it('handles commas and punctuation inside selectors functions comments and strings', function (): void {
    $css = <<<'CSS'
@supports (display: grid) {
    @media (600px <= width < 1000px) {
        @container cards (min-width: 300px) {
            :is(.one, .two)[title="a,b"]::before, .three { content: "{ Sinhala සිංහල, };"; }
        }
    }
}
CSS;

    $service = new PageCss;

    expect($service->error($css))->toBeNull()
        ->and($service->forPage($css, 1))
        ->toContain(':where(#cms-page-1) :is(.one, .two)[title="a,b"]::before,')
        ->toContain(':where(#cms-page-1) .three')
        ->toContain('@media (600px <= width < 1000px)')
        ->toContain('Sinhala සිංහල');
});

it('rejects invalid or active CSS and fails closed when rendering', function (string $css): void {
    $service = new PageCss;

    expect($service->error($css))->toBeString()
        ->and($service->forPage($css, 1))->toBe('');
})->with([
    'HTML injection' => '</style><script>alert(1)</script>',
    'style element' => '<style>.card {color:red}</style>',
    'external stylesheet' => '@import url("https://example.com/styles.css");',
    'escaped import' => '@\69mport "https://example.com/styles.css";',
    'global font rule' => '@font-face { font-family: test; src: url(font.woff); }',
    'unmatched closing brace' => '.card { color:red; }} body { display:none; }',
    'unmatched opening brace' => '@media (max-width:767px) { .card { color:red; }',
    'unclosed comment' => '.card { color:red; } /*',
    'unclosed quote' => '.card::before { content: "unfinished; }',
    'bad string' => ".card::before { content: \"broken\n} body { color:red; } /*\"; }",
    'bad brackets' => '.card { color: rgb(0,0,0]; }',
    'nested selectors' => '.card { h3 { color:red; } }',
    'expression' => '.card { width: expression(alert(1)); }',
    'escaped expression' => '.card { width: e\78pression(alert(1)); }',
    'script URL' => '.card { background: url("javascript:alert(1)"); }',
    'continued script URL' => ".card { background: url(\"java\\\nscript:alert(1)\"); }",
    'escape at end' => '.card {color:red;}\\',
]);

it('rejects oversized CSS but accepts empty optional CSS', function (): void {
    $service = new PageCss;

    expect($service->error(str_repeat(' ', PageCss::MAX_LENGTH).'a'))->toBeString()
        ->and($service->error(null))->toBeNull()
        ->and($service->forPage(null, 1))->toBe('');
});

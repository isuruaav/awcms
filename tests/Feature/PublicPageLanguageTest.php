<?php

use App\Enums\PageLocale;
use App\Models\Page;

test('english sinhala and tamil pages are rendered from manually entered versions', function (): void {
    $english = Page::factory()
        ->published()
        ->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'locale' => PageLocale::English->value,
            'content' => '<p>Approved English content.</p>',
        ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    $sinhala = Page::factory()
        ->published()
        ->create([
            'title' => 'අප ගැන',
            'slug' => 'about-us',
            'locale' => PageLocale::Sinhala->value,
            'translation_group' => $translationGroup,
            'content' => '<p>අනුමත සිංහල අන්තර්ගතය.</p>',
        ]);

    $tamil = Page::factory()
        ->published()
        ->create([
            'title' => 'எங்களைப் பற்றி',
            'slug' => 'about-us',
            'locale' => PageLocale::Tamil->value,
            'translation_group' => $translationGroup,
            'content' => '<p>அங்கீகரிக்கப்பட்ட தமிழ் உள்ளடக்கம்.</p>',
        ]);

    $this->get(
        route('pages.show', $english->slug),
    )
        ->assertOk()
        ->assertSee('Approved English content.')
        ->assertDontSee('අනුමත සිංහල අන්තර්ගතය.');

    $this->get(
        route(
            'pages.show.localized',
            [
                'locale' => PageLocale::Sinhala->value,
                'slug' => $sinhala->slug,
            ],
        ),
    )
        ->assertOk()
        ->assertSee('lang="si"', false)
        ->assertSee('අනුමත සිංහල අන්තර්ගතය.')
        ->assertDontSee('Approved English content.')
        ->assertSee(
            route(
                'pages.show.localized',
                [
                    'locale' => PageLocale::English->value,
                    'slug' => $english->slug,
                ],
            ),
            false,
        )
        ->assertSee(
            route(
                'pages.show.localized',
                [
                    'locale' => PageLocale::Tamil->value,
                    'slug' => $tamil->slug,
                ],
            ),
            false,
        );
});

test('an unpublished translation is not offered as an active public language link', function (): void {
    $english = Page::factory()
        ->published()
        ->create([
            'slug' => 'language-availability',
            'locale' => PageLocale::English->value,
        ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    Page::factory()->create([
        'slug' => 'language-availability',
        'locale' => PageLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]);

    $sinhalaUrl = route(
        'pages.show.localized',
        [
            'locale' => PageLocale::Sinhala->value,
            'slug' => 'language-availability',
        ],
    );

    $this->get(
        route('pages.show', $english->slug),
    )
        ->assertOk()
        ->assertDontSee(
            'href="'.$sinhalaUrl.'"',
            false,
        );
});

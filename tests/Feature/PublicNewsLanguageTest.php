<?php

use App\Enums\NewsLocale;
use App\Models\News;

test('english sinhala and tamil news are rendered from manually entered versions', function (): void {
    $english = News::factory()
        ->published()
        ->create([
            'title' => 'Official Training News',
            'slug' => 'official-training-news',
            'locale' => NewsLocale::English->value,
            'content' => '<p>Approved English news content.</p>',
        ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    $sinhala = News::factory()
        ->published()
        ->create([
            'title' => 'නිල පුහුණු පුවත',
            'slug' => 'official-training-news',
            'locale' => NewsLocale::Sinhala->value,
            'translation_group' => $translationGroup,
            'content' => '<p>අනුමත සිංහල පුවත් අන්තර්ගතය.</p>',
        ]);

    $tamil = News::factory()
        ->published()
        ->create([
            'title' => 'அதிகாரப்பூர்வ பயிற்சி செய்தி',
            'slug' => 'official-training-news',
            'locale' => NewsLocale::Tamil->value,
            'translation_group' => $translationGroup,
            'content' => '<p>அங்கீகரிக்கப்பட்ட தமிழ் செய்தி உள்ளடக்கம்.</p>',
        ]);

    $this->get(
        route(
            'news.show',
            [
                'slug' => $english->slug,
            ],
        ),
    )
        ->assertOk()
        ->assertSee('Approved English news content.')
        ->assertDontSee('අනුමත සිංහල පුවත් අන්තර්ගතය.');

    $this->get(
        route(
            'news.show.localized',
            [
                'locale' => NewsLocale::Sinhala->value,
                'slug' => $sinhala->slug,
            ],
        ),
    )
        ->assertOk()
        ->assertSee('lang="si"', false)
        ->assertSee('අනුමත සිංහල පුවත් අන්තර්ගතය.')
        ->assertDontSee('Approved English news content.')
        ->assertSee(
            route(
                'news.show.localized',
                [
                    'locale' => NewsLocale::English->value,
                    'slug' => $english->slug,
                ],
            ),
            false,
        )
        ->assertSee(
            route(
                'news.show.localized',
                [
                    'locale' => NewsLocale::Tamil->value,
                    'slug' => $tamil->slug,
                ],
            ),
            false,
        );
});

test('an unpublished news translation is not offered as an active language link', function (): void {
    $english = News::factory()
        ->published()
        ->create([
            'slug' => 'language-availability-news',
            'locale' => NewsLocale::English->value,
        ]);

    $translationGroup = (string) $english->getAttribute(
        'translation_group',
    );

    News::factory()->create([
        'slug' => 'language-availability-news',
        'locale' => NewsLocale::Sinhala->value,
        'translation_group' => $translationGroup,
    ]);

    $sinhalaUrl = route(
        'news.show.localized',
        [
            'locale' => NewsLocale::Sinhala->value,
            'slug' => 'language-availability-news',
        ],
    );

    $this->get(
        route(
            'news.show',
            [
                'slug' => $english->slug,
            ],
        ),
    )
        ->assertOk()
        ->assertDontSee(
            'href="'.$sinhalaUrl.'"',
            false,
        );
});

test('news index shows only the requested language', function (): void {
    News::factory()
        ->published()
        ->create([
            'title' => 'English News Only',
            'locale' => NewsLocale::English->value,
        ]);

    News::factory()
        ->published()
        ->create([
            'title' => 'සිංහල පුවත පමණි',
            'locale' => NewsLocale::Sinhala->value,
        ]);

    $this->get(
        route(
            'news.index.localized',
            [
                'locale' => NewsLocale::Sinhala->value,
            ],
        ),
    )
        ->assertOk()
        ->assertSee('සිංහල පුවත පමණි')
        ->assertDontSee('English News Only');
});

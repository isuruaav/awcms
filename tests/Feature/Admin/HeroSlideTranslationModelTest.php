<?php

use App\Enums\PageLocale;
use App\Models\HeroSlide;
use App\Models\HeroSlideTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores english and sinhala translations for a hero slide', function (): void {
    $slide = HeroSlide::query()->create([
        'title' => 'Welcome to School of Signals',
        'subtitle' => 'Legacy English content',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $slide->translations()->createMany([
        [
            'locale' => PageLocale::English->value,
            'title' => 'Welcome to School of Signals',
            'subtitle' => 'Technological Sound',
            'button_label' => 'Learn More',
            'button_url' => '/en/pages/about-us',
        ],
        [
            'locale' => PageLocale::Sinhala->value,
            'title' => 'සංඥා පාසල වෙත සාදරයෙන් පිළිගනිමු',
            'subtitle' => 'තාක්ෂණිකව සවිමත්',
            'button_label' => 'වැඩි විස්තර',
            'button_url' => '/si/pages/about-us',
        ],
    ]);

    $slide->refresh()->load('translations');

    $english = $slide->translation(PageLocale::English);
    $sinhala = $slide->translation(PageLocale::Sinhala);

    expect($slide->uuid)
        ->not->toBeEmpty()
        ->and($slide->is_active)->toBeTrue()
        ->and($slide->sort_order)->toBe(1)
        ->and($slide->translations)->toHaveCount(2)
        ->and($english)->toBeInstanceOf(HeroSlideTranslation::class)
        ->and($english?->locale)->toBe(PageLocale::English)
        ->and($english?->title)->toBe('Welcome to School of Signals')
        ->and($sinhala)->toBeInstanceOf(HeroSlideTranslation::class)
        ->and($sinhala?->locale)->toBe(PageLocale::Sinhala)
        ->and($sinhala?->title)->toBe('සංඥා පාසල වෙත සාදරයෙන් පිළිගනිමු');
});

it('falls back to english when the requested translation is unavailable', function (): void {
    $slide = HeroSlide::query()->create([
        'title' => 'English legacy title',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $slide->translations()->create([
        'locale' => PageLocale::English->value,
        'title' => 'English Slider Title',
        'subtitle' => 'English Slider Subtitle',
    ]);

    $slide->load('translations');

    $translation = $slide->translation(PageLocale::Tamil);

    expect($translation)
        ->toBeInstanceOf(HeroSlideTranslation::class)
        ->and($translation?->locale)->toBe(PageLocale::English)
        ->and($translation?->title)->toBe('English Slider Title');
});

it('can disable english fallback', function (): void {
    $slide = HeroSlide::query()->create([
        'title' => 'English legacy title',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $slide->translations()->create([
        'locale' => PageLocale::English->value,
        'title' => 'English Slider Title',
    ]);

    $slide->load('translations');

    expect(
        $slide->translation(
            PageLocale::Sinhala,
            fallbackToEnglish: false,
        ),
    )->toBeNull();
});

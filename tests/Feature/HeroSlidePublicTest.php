<?php

use App\Enums\PageLocale;
use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set(
        'awcms.active_theme',
        'school-of-signals',
    );

    config()->set(
        'awcms.theme_locales.school-of-signals',
        [
            PageLocale::English->value,
            PageLocale::Sinhala->value,
        ],
    );
});

it('renders hero slide content in the requested homepage language', function (): void {
    $slide = HeroSlide::query()->create([
        'title' => 'Legacy English Slider Title',
        'subtitle' => 'Legacy English Slider Subtitle',
        'button_label' => 'Legacy English Button',
        'button_url' => '/legacy-page',
        'is_active' => true,
        'sort_order' => 10,
    ]);

    $slide->translations()->createMany([
        [
            'locale' => PageLocale::English->value,
            'title' => 'English Signals Slider',
            'subtitle' => 'English Signals Description',
            'button_label' => 'Explore English',
            'button_url' => '/en/pages/english-slider-page',
        ],
        [
            'locale' => PageLocale::Sinhala->value,
            'title' => 'සිංහල සංඥා ස්ලයිඩරය',
            'subtitle' => 'සිංහල සංඥා විස්තරය',
            'button_label' => 'සිංහලෙන් බලන්න',
            'button_url' => '/si/pages/sinhala-slider-page',
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('English Signals Slider')
        ->assertSeeText('English Signals Description')
        ->assertSeeText('Explore English')
        ->assertSee('/en/pages/english-slider-page')
        ->assertDontSeeText('සිංහල සංඥා ස්ලයිඩරය')
        ->assertDontSee('/si/pages/sinhala-slider-page');

    $this->get(
        route(
            'home.localized',
            [
                'locale' => PageLocale::Sinhala->value,
            ],
        ),
    )
        ->assertOk()
        ->assertSeeText('සිංහල සංඥා ස්ලයිඩරය')
        ->assertSeeText('සිංහල සංඥා විස්තරය')
        ->assertSeeText('සිංහලෙන් බලන්න')
        ->assertSee('/si/pages/sinhala-slider-page')
        ->assertDontSeeText('English Signals Slider')
        ->assertDontSee('/en/pages/english-slider-page');
});

it('uses english fallback and excludes inactive hero slides', function (): void {
    $activeSlide = HeroSlide::query()->create([
        'title' => 'Legacy Active Title',
        'is_active' => true,
        'sort_order' => 10,
    ]);

    $activeSlide->translations()->create([
        'locale' => PageLocale::English->value,
        'title' => 'Published English Fallback Slide',
        'subtitle' => 'Fallback content for Sinhala',
    ]);

    $inactiveSlide = HeroSlide::query()->create([
        'title' => 'Hidden Legacy Slide',
        'is_active' => false,
        'sort_order' => 20,
    ]);

    $inactiveSlide->translations()->createMany([
        [
            'locale' => PageLocale::English->value,
            'title' => 'Hidden English Slide',
        ],
        [
            'locale' => PageLocale::Sinhala->value,
            'title' => 'සඟවා ඇති සිංහල ස්ලයිඩරය',
        ],
    ]);

    $this->get(
        route(
            'home.localized',
            [
                'locale' => PageLocale::Sinhala->value,
            ],
        ),
    )
        ->assertOk()
        ->assertSeeText('Published English Fallback Slide')
        ->assertSeeText('Fallback content for Sinhala')
        ->assertDontSeeText('Hidden English Slide')
        ->assertDontSeeText('සඟවා ඇති සිංහල ස්ලයිඩරය')
        ->assertDontSeeText('Hidden Legacy Slide');
});

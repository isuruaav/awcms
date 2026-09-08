<?php

use App\Enums\PageLocale;
use App\Livewire\Admin\HeroSlides\HeroSlideIndex;
use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Permission::findOrCreate('admin.access', 'web');
    Permission::findOrCreate('settings.manage', 'web');
    Permission::findOrCreate('media.upload', 'web');

    Storage::fake('public');
    Storage::fake('local');
});

function heroSlideAdminUser(
    bool $mayManageSlides = true,
    bool $mayUploadMedia = true,
): User {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $permissions = [
        'admin.access',
    ];

    if ($mayManageSlides) {
        $permissions[] = 'settings.manage';
    }

    if ($mayUploadMedia) {
        $permissions[] = 'media.upload';
    }

    $user->givePermissionTo($permissions);

    return $user;
}

it('allows an authorized administrator to open the hero slider editor', function (): void {
    $this->actingAs(heroSlideAdminUser())
        ->get(route('admin.hero-slides.index'))
        ->assertOk()
        ->assertSee('Hero Slider')
        ->assertSee('Create Hero Slide')
        ->assertSee('English')
        ->assertSee('Sinhala')
        ->assertSee('Upload New Image')
        ->assertSee('Media Library Image');
});

it('forbids administrators without settings management permission', function (): void {
    $this->actingAs(
        heroSlideAdminUser(
            mayManageSlides: false,
        ),
    )
        ->get(route('admin.hero-slides.index'))
        ->assertForbidden();
});

it('creates english and sinhala hero slide translations', function (): void {
    $user = heroSlideAdminUser();

    $this->actingAs($user);

    Livewire::test(HeroSlideIndex::class)
        ->set(
            'englishTitle',
            'Welcome to the School of Signals',
        )
        ->set(
            'englishSubtitle',
            'Technological Sound',
        )
        ->set(
            'englishButtonLabel',
            'Learn More',
        )
        ->set(
            'englishButtonUrl',
            '/en/pages/about-us',
        )
        ->set(
            'sinhalaTitle',
            'සංඥා පාසල වෙත සාදරයෙන් පිළිගනිමු',
        )
        ->set(
            'sinhalaSubtitle',
            'තාක්ෂණිකව සවිමත්',
        )
        ->set(
            'sinhalaButtonLabel',
            'වැඩි විස්තර',
        )
        ->set(
            'sinhalaButtonUrl',
            '/si/pages/about-us',
        )
        ->set('isActive', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('editingId', null)
        ->assertSet('englishTitle', '')
        ->assertSet('sinhalaTitle', '');

    $slide = HeroSlide::query()
        ->with('translations')
        ->firstOrFail();

    expect($slide->title)
        ->toBe('Welcome to the School of Signals')
        ->and($slide->subtitle)
        ->toBe('Technological Sound')
        ->and($slide->button_label)
        ->toBe('Learn More')
        ->and($slide->button_url)
        ->toBe('/en/pages/about-us')
        ->and($slide->is_active)
        ->toBeTrue()
        ->and($slide->created_by)
        ->toBe($user->id)
        ->and($slide->updated_by)
        ->toBe($user->id)
        ->and($slide->translations)
        ->toHaveCount(2);

    $this->assertDatabaseHas('hero_slide_translations', [
        'hero_slide_id' => $slide->id,
        'locale' => PageLocale::English->value,
        'title' => 'Welcome to the School of Signals',
        'button_label' => 'Learn More',
        'button_url' => '/en/pages/about-us',
    ]);

    $this->assertDatabaseHas('hero_slide_translations', [
        'hero_slide_id' => $slide->id,
        'locale' => PageLocale::Sinhala->value,
        'title' => 'සංඥා පාසල වෙත සාදරයෙන් පිළිගනිමු',
        'button_label' => 'වැඩි විස්තර',
        'button_url' => '/si/pages/about-us',
    ]);
});

it('edits and deactivates an existing hero slide', function (): void {
    $user = heroSlideAdminUser();

    $slide = HeroSlide::query()->create([
        'title' => 'Original English Title',
        'subtitle' => 'Original subtitle',
        'is_active' => true,
        'sort_order' => 10,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $slide->translations()->createMany([
        [
            'locale' => PageLocale::English->value,
            'title' => 'Original English Title',
        ],
        [
            'locale' => PageLocale::Sinhala->value,
            'title' => 'පැරණි සිංහල මාතෘකාව',
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(HeroSlideIndex::class)
        ->call('edit', $slide->id)
        ->assertSet('editingId', $slide->id)
        ->assertSet(
            'englishTitle',
            'Original English Title',
        )
        ->assertSet(
            'sinhalaTitle',
            'පැරණි සිංහල මාතෘකාව',
        )
        ->set(
            'englishTitle',
            'Updated English Title',
        )
        ->set(
            'sinhalaTitle',
            'යාවත්කාලීන කළ සිංහල මාතෘකාව',
        )
        ->set('isActive', false)
        ->call('save')
        ->assertHasNoErrors();

    $slide->refresh()->load('translations');

    expect($slide->title)
        ->toBe('Updated English Title')
        ->and($slide->is_active)
        ->toBeFalse()
        ->and(
            $slide->translation(
                PageLocale::Sinhala,
                fallbackToEnglish: false,
            )?->title,
        )
        ->toBe('යාවත්කාලීන කළ සිංහල මාතෘකාව');
});

it('changes slide order and deletes a slide', function (): void {
    $user = heroSlideAdminUser();

    $first = HeroSlide::query()->create([
        'title' => 'First Slide',
        'is_active' => true,
        'sort_order' => 10,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $second = HeroSlide::query()->create([
        'title' => 'Second Slide',
        'is_active' => true,
        'sort_order' => 20,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $this->actingAs($user);

    Livewire::test(HeroSlideIndex::class)
        ->call('moveDown', $first->id)
        ->assertHasNoErrors();

    expect($first->refresh()->sort_order)
        ->toBe(20)
        ->and($second->refresh()->sort_order)
        ->toBe(10);

    Livewire::test(HeroSlideIndex::class)
        ->call('delete', $first->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('hero_slides', [
        'id' => $first->id,
    ]);

    it('uploads a new public image and links it to the hero slide', function (): void {
        $user = heroSlideAdminUser();

        $file = UploadedFile::fake()
            ->image(
                'signals-hero.jpg',
                1600,
                900,
            )
            ->size(800);

        Livewire::actingAs($user)
            ->test(HeroSlideIndex::class)
            ->set('newImage', $file)
            ->set(
                'newImageTitle',
                'School of Signals Hero',
            )
            ->set(
                'newImageAltText',
                'School of Signals training activities',
            )
            ->set(
                'englishTitle',
                'Signals Training Excellence',
            )
            ->set(
                'englishSubtitle',
                'Professional military signal training',
            )
            ->set(
                'sinhalaTitle',
                'විශිෂ්ට සංඥා පුහුණුව',
            )
            ->set(
                'sinhalaSubtitle',
                'වෘත්තීය හමුදා සංඥා පුහුණුව',
            )
            ->set('isActive', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('newImage', null);

        $media = MediaAsset::query()->firstOrFail();
        $slide = HeroSlide::query()->firstOrFail();

        expect($media->title)
            ->toBe('School of Signals Hero')
            ->and($media->alt_text)
            ->toBe('School of Signals training activities')
            ->and($slide->image_media_id)
            ->toBe($media->id)
            ->and($slide->image)
            ->toBeInstanceOf(MediaAsset::class);

        $this->assertDatabaseHas('media_assets', [
            'id' => $media->id,
            'type' => 'image',
            'visibility' => 'public',
            'title' => 'School of Signals Hero',
            'alt_text' => 'School of Signals training activities',
        ]);

        $this->assertDatabaseHas('hero_slides', [
            'id' => $slide->id,
            'image_media_id' => $media->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('hero_slide_translations', [
            'hero_slide_id' => $slide->id,
            'locale' => PageLocale::English->value,
            'title' => 'Signals Training Excellence',
        ]);

        $this->assertDatabaseHas('hero_slide_translations', [
            'hero_slide_id' => $slide->id,
            'locale' => PageLocale::Sinhala->value,
            'title' => 'විශිෂ්ට සංඥා පුහුණුව',
        ]);
    });
});

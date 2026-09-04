<?php

use App\Enums\ThemeLayoutLocale;
use App\Enums\ThemeLayoutRegion;
use App\Enums\ThemeLayoutStatus;
use App\Livewire\Admin\ThemeLayouts\ThemeLayoutIndex;
use App\Models\ThemeLayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Permission::findOrCreate('admin.access', 'web');
    Permission::findOrCreate('theme-layouts.manage', 'web');

    config()->set('awcms.active_theme', 'school-of-signals');
});

function themeLayoutAdminUser(bool $mayManageLayouts = true): User
{
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $permissions = ['admin.access'];

    if ($mayManageLayouts) {
        $permissions[] = 'theme-layouts.manage';
    }

    $user->givePermissionTo($permissions);

    return $user;
}

it('allows an authorized administrator to open the multilingual editor', function (): void {
    $this->actingAs(themeLayoutAdminUser())
        ->get(route('admin.theme-layouts.index'))
        ->assertOk()
        ->assertSee('Header &amp; Footer', false)
        ->assertSee('school-of-signals')
        ->assertSee('English')
        ->assertSee('සිංහල')
        ->assertSee('தமிழ்');
});

it('forbids administrators without the dedicated layout permission', function (): void {
    $this->actingAs(themeLayoutAdminUser(false))
        ->get(route('admin.theme-layouts.index'))
        ->assertForbidden();
});

it('keeps language editor content independent when switching tabs', function (): void {
    $this->actingAs(themeLayoutAdminUser());

    Livewire::test(ThemeLayoutIndex::class)
        ->assertSet('themeSlug', 'school-of-signals')
        ->assertSet('region', ThemeLayoutRegion::Header->value)
        ->assertSet('locale', ThemeLayoutLocale::English->value)
        ->set(
            'layoutHtml',
            '<header>English [[site_name]] [[primary_menu]]</header>',
        )
        ->set(
            'layoutCss',
            '.school-header { color: #ffffff; }',
        )
        ->call('saveDraft')
        ->assertHasNoErrors()
        ->call('selectLocale', ThemeLayoutLocale::Sinhala->value)
        ->assertSet('locale', ThemeLayoutLocale::Sinhala->value)
        ->assertSet('layoutHtml', '')
        ->set(
            'layoutHtml',
            '<header>සිංහල [[site_name]] [[primary_menu]]</header>',
        )
        ->set(
            'layoutCss',
            '.school-header { color: #f8fafc; }',
        )
        ->call('saveDraft')
        ->assertHasNoErrors()
        ->call('selectLocale', ThemeLayoutLocale::English->value)
        ->assertSet('locale', ThemeLayoutLocale::English->value)
        ->assertSet(
            'layoutHtml',
            '<header>English [[site_name]] [[primary_menu]]</header>',
        );

    expect(
        ThemeLayout::query()
            ->where('theme_slug', 'school-of-signals')
            ->where('region', ThemeLayoutRegion::Header->value)
            ->count(),
    )->toBe(2);

    $this->assertDatabaseHas('theme_layouts', [
        'theme_slug' => 'school-of-signals',
        'region' => ThemeLayoutRegion::Header->value,
        'locale' => ThemeLayoutLocale::English->value,
    ]);

    $this->assertDatabaseHas('theme_layouts', [
        'theme_slug' => 'school-of-signals',
        'region' => ThemeLayoutRegion::Header->value,
        'locale' => ThemeLayoutLocale::Sinhala->value,
    ]);
});

it('saves and publishes only the selected language layout', function (): void {
    $this->actingAs(themeLayoutAdminUser());

    Livewire::test(ThemeLayoutIndex::class)
        ->call('selectLocale', ThemeLayoutLocale::Tamil->value)
        ->assertSet('locale', ThemeLayoutLocale::Tamil->value)
        ->set(
            'layoutHtml',
            '<header class="school-header" onclick="alert(1)">தமிழ் [[site_name]] [[primary_menu]]</header>',
        )
        ->set(
            'layoutCss',
            '.school-header { background: #0f172a; }',
        )
        ->call('saveDraft')
        ->assertHasNoErrors()
        ->call('publish')
        ->assertHasNoErrors();

    $layout = ThemeLayout::query()
        ->where('theme_slug', 'school-of-signals')
        ->where('region', ThemeLayoutRegion::Header->value)
        ->where('locale', ThemeLayoutLocale::Tamil->value)
        ->firstOrFail();

    expect($layout->status)->toBe(ThemeLayoutStatus::Published)
        ->and($layout->locale)->toBe(ThemeLayoutLocale::Tamil)
        ->and($layout->draft_html)->toContain('தமிழ்')
        ->and($layout->draft_html)->toContain('[[primary_menu]]')
        ->and($layout->draft_html)->not->toContain('onclick')
        ->and($layout->published_html)->toBe($layout->draft_html)
        ->and($layout->revision_number)->toBe(2);

    $this->assertDatabaseMissing('theme_layouts', [
        'theme_slug' => 'school-of-signals',
        'region' => ThemeLayoutRegion::Header->value,
        'locale' => ThemeLayoutLocale::English->value,
    ]);
});

<?php

use App\Livewire\Admin\ContactMessages\ContactMessageIndex;
use App\Livewire\Admin\Menus\MenuIndex;
use App\Livewire\Admin\Redirects\RedirectIndex;
use App\Livewire\Admin\Settings\SiteSettingsIndex;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

afterEach(function (): void {
    // Keep this file self-contained and explicit.
});

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('site administrator can open site management modules', function (): void {
    $administrator = User::factory()->create(['email_verified_at' => now()]);
    $administrator->assignRole('Site Administrator');

    Livewire::actingAs($administrator)->test(MenuIndex::class)->assertOk()->assertSee('Menu Builder');
    Livewire::actingAs($administrator)->test(SiteSettingsIndex::class)->assertOk()->assertSee('Site Settings');
    Livewire::actingAs($administrator)->test(ContactMessageIndex::class)->assertOk()->assertSee('Contact Messages');
    Livewire::actingAs($administrator)->test(RedirectIndex::class)->assertOk()->assertSee('Redirect Manager');
});

test('content editor cannot open site management modules', function (): void {
    $editor = User::factory()->create(['email_verified_at' => now()]);
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)->test(MenuIndex::class)->assertForbidden();
    Livewire::actingAs($editor)->test(SiteSettingsIndex::class)->assertForbidden();
    Livewire::actingAs($editor)->test(ContactMessageIndex::class)->assertForbidden();
    Livewire::actingAs($editor)->test(RedirectIndex::class)->assertForbidden();
});

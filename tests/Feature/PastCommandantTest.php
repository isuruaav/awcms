<?php

use App\Livewire\Admin\PastCommandants\PastCommandantIndex;
use App\Models\PastCommandant;
use App\Models\SchoolLeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::findOrCreate('admin.access', 'web');
    Permission::findOrCreate('settings.manage', 'web');

    config()->set('awcms.active_theme', 'school-of-signals');
    config()->set('awcms.theme_locales.school-of-signals', ['en', 'si']);
});

function pastCommandantAdmin(): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->givePermissionTo(['admin.access', 'settings.manage']);

    return $user;
}

it('renders the present commandant above past commandants in both public languages', function (): void {
    SchoolLeader::query()->create([
        'role_key' => SchoolLeader::ROLE_COMMANDANT,
        'title_en' => 'The Commandant',
        'title_si' => 'සේනාවිධායක',
        'name_en' => 'Major Current Commander',
        'name_si' => 'වර්තමාන සේනාවිධායක',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    PastCommandant::query()->create([
        'name_en' => 'Major AA Perera SLSC',
        'name_si' => 'මේජර් ඒ ඒ පෙරේරා SLSC',
        'from_date' => '1993-12-27',
        'to_date' => '1994-09-14',
    ]);

    $this->get('/en/history/past-commandants')
        ->assertOk()
        ->assertSeeText('Present Commandant')
        ->assertSeeText('Major Current Commander')
        ->assertSeeText('Major AA Perera SLSC')
        ->assertSeeText('27.12.1993')
        ->assertSeeText('14.09.1994');

    $this->get('/si/history/past-commandants')
        ->assertOk()
        ->assertSeeText('වර්තමාන සේනාවිධායක')
        ->assertSeeText('මේජර් ඒ ඒ පෙරේරා SLSC');
});

it('allows authorized administrators to create and delete past commandants', function (): void {
    $this->actingAs(pastCommandantAdmin());

    Livewire::test(PastCommandantIndex::class)
        ->set('nameEn', 'Major AA Perera SLSC')
        ->set('nameSi', 'මේජර් ඒ ඒ පෙරේරා SLSC')
        ->set('fromDate', '1993-12-27')
        ->set('toDate', '1994-09-14')
        ->call('save')
        ->assertHasNoErrors();

    $commandant = PastCommandant::query()->firstOrFail();

    expect($commandant->name_en)->toBe('Major AA Perera SLSC');

    Livewire::test(PastCommandantIndex::class)
        ->call('delete', $commandant->id)
        ->assertHasNoErrors();

    expect(PastCommandant::query()->count())->toBe(0);
});

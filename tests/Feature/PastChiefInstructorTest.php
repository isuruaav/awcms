<?php

use App\Livewire\Admin\PastChiefInstructors\PastChiefInstructorIndex;
use App\Models\PastChiefInstructor;
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

function pastChiefInstructorAdmin(): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->givePermissionTo(['admin.access', 'settings.manage']);

    return $user;
}

it('renders past chief instructors in both public languages', function (): void {
    SchoolLeader::query()->create([
        'role_key' => SchoolLeader::ROLE_CHIEF_INSTRUCTOR,
        'title_en' => 'The Chief Instructor',
        'title_si' => 'ප්‍රධාන උපදේශක',
        'name_en' => 'Present Chief Instructor Silva',
        'name_si' => 'වර්තමාන ප්‍රධාන උපදේශක සිල්වා',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    PastChiefInstructor::query()->create([
        'name_en' => 'Major Chief Instructor Perera',
        'name_si' => 'ප්‍රධාන උපදේශක පෙරේරා',
        'from_date' => '1993-12-27',
        'to_date' => '1994-09-14',
    ]);

    $this->get('/en/history/past-chief-instructors')
        ->assertOk()
        ->assertSeeText('Past Chief Instructors')
        ->assertSeeText('Present Chief Instructor')
        ->assertSeeText('Present Chief Instructor Silva')
        ->assertSeeText('Major Chief Instructor Perera');

    $this->get('/si/history/past-chief-instructors')
        ->assertOk()
        ->assertSeeText('හිටපු ප්‍රධාන උපදේශකවරු')
        ->assertSeeText('වර්තමාන ප්‍රධාන උපදේශක')
        ->assertSeeText('වර්තමාන ප්‍රධාන උපදේශක සිල්වා')
        ->assertSeeText('ප්‍රධාන උපදේශක පෙරේරා');
});

it('allows authorized administrators to create and delete past chief instructors', function (): void {
    $this->actingAs(pastChiefInstructorAdmin());

    Livewire::test(PastChiefInstructorIndex::class)
        ->assertSee('Upload New Image')
        ->set('nameEn', 'Major Chief Instructor Perera')
        ->set('nameSi', 'ප්‍රධාන උපදේශක පෙරේරා')
        ->set('fromDate', '1993-12-27')
        ->set('toDate', '1994-09-14')
        ->call('save')
        ->assertHasNoErrors();

    $instructor = PastChiefInstructor::query()->firstOrFail();

    Livewire::test(PastChiefInstructorIndex::class)
        ->call('delete', $instructor->id)
        ->assertHasNoErrors();

    expect(PastChiefInstructor::query()->count())->toBe(0);
});

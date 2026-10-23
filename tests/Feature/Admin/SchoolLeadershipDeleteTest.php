<?php

use App\Livewire\Admin\SchoolLeaders\SchoolLeaderIndex;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Models\SchoolLeader;
use App\Models\SchoolLeadershipPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function leadershipDeleteActor(bool $canUpdate = true): User
{
    $actor = User::factory()->create(['is_active' => true]);
    $actor->givePermissionTo(Permission::findOrCreate('school-leaders.view', 'web'));

    if ($canUpdate) {
        $actor->givePermissionTo(Permission::findOrCreate('school-leaders.update', 'web'));
    }

    return $actor;
}

function leadershipDeleteRecord(array $attributes = []): SchoolLeader
{
    $position = SchoolLeadershipPosition::query()->where('key', 'commandant')->firstOrFail();

    return SchoolLeader::query()->create(array_merge([
        'position_id' => $position->id,
        'role_key' => $position->key,
        'title_en' => $position->name_en,
        'name_en' => 'Appointment Officer',
        'rank' => 'colonel',
        'start_date' => '2025-06-10',
        'is_active' => true,
    ], $attributes));
}

it('deletes present and past appointments with an audit trail while preserving shared records', function (?string $endDate): void {
    $actor = leadershipDeleteActor();
    $image = MediaAsset::factory()->create();
    $leader = leadershipDeleteRecord([
        'end_date' => $endDate,
        'image_media_id' => $image->id,
        'profile_enabled' => true,
        'profile_html_en' => '<p>Saved officer biography</p>',
    ]);
    $other = leadershipDeleteRecord(['image_media_id' => $image->id]);

    Livewire::actingAs($actor)->test(SchoolLeaderIndex::class)
        ->assertSeeHtml('wire:click="delete('.$leader->id.')"')
        ->assertSeeHtml('wire:confirm="Delete the appointment for Appointment Officer?')
        ->call('delete', $leader->id)
        ->assertHasNoErrors()
        ->assertSeeText('Leadership appointment deleted successfully.')
        ->assertDontSeeHtml('wire:click="delete('.$leader->id.')"');

    $this->assertDatabaseMissing('school_leaders', ['id' => $leader->id]);
    $this->assertDatabaseHas('school_leaders', ['id' => $other->id, 'image_media_id' => $image->id]);
    $this->assertDatabaseHas('school_leadership_positions', ['id' => $leader->position_id]);
    $this->assertDatabaseHas('media_assets', ['id' => $image->id]);

    $audit = AuditLog::query()->where('event', 'settings.school-leader-deleted')->sole();
    expect($audit->actor_id)->toBe($actor->id)
        ->and($audit->subject_type)->toBe($leader->getMorphClass())
        ->and($audit->subject_id)->toBe($leader->id)
        ->and($audit->old_values['name_en'])->toBe('Appointment Officer')
        ->and($audit->old_values['profile_html_en'])->toBe('<p>Saved officer biography</p>');

    $this->get(route('school-leadership.show', ['leader' => $leader->id]))->assertNotFound();
})->with(['present' => null, 'past' => '2026-10-01']);

it('hides delete and rejects direct deletion for read-only users', function (): void {
    $leader = leadershipDeleteRecord();

    Livewire::actingAs(leadershipDeleteActor(false))->test(SchoolLeaderIndex::class)
        ->assertDontSeeHtml('wire:click="delete(')
        ->call('delete', $leader->id)
        ->assertForbidden();

    $this->assertDatabaseHas('school_leaders', ['id' => $leader->id]);
    $this->assertDatabaseMissing('audit_logs', ['event' => 'settings.school-leader-deleted']);
});

it('checks view authorization again before deleting an appointment', function (): void {
    $actor = leadershipDeleteActor();
    $leader = leadershipDeleteRecord();
    $component = Livewire::actingAs($actor)->test(SchoolLeaderIndex::class);

    $actor->revokePermissionTo('school-leaders.view');

    $component->call('delete', $leader->id)->assertForbidden();

    $this->assertDatabaseHas('school_leaders', ['id' => $leader->id]);
});

it('returns not found for an appointment that no longer exists', function (): void {
    expect(fn () => Livewire::actingAs(leadershipDeleteActor())->test(SchoolLeaderIndex::class)
        ->call('delete', 999999))->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseMissing('audit_logs', ['event' => 'settings.school-leader-deleted']);
});

it('closes and clears the editor when its appointment is deleted', function (): void {
    $leader = leadershipDeleteRecord();

    Livewire::actingAs(leadershipDeleteActor())->test(SchoolLeaderIndex::class)
        ->call('edit', $leader->id)
        ->assertSet('showEditor', true)
        ->call('delete', $leader->id)
        ->assertSet('showEditor', false)
        ->assertSet('editingId', null)
        ->assertSet('nameEn', '');

    $this->assertDatabaseMissing('school_leaders', ['id' => $leader->id]);
});

it('returns to a populated page after deleting the last appointment on a page', function (): void {
    $leader = leadershipDeleteRecord();
    for ($index = 0; $index < 10; $index++) {
        leadershipDeleteRecord();
    }

    Livewire::actingAs(leadershipDeleteActor())->test(SchoolLeaderIndex::class)
        ->set('search', 'Appointment Officer')
        ->set('statusFilter', 'present')
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->assertViewHas('schoolLeaders', fn ($leaders) => $leaders->count() === 1 && $leaders->first()->id === $leader->id)
        ->call('delete', $leader->id)
        ->assertSet('paginators.page', 1)
        ->assertSet('search', 'Appointment Officer')
        ->assertSet('statusFilter', 'present')
        ->assertSeeText('Appointment Officer')
        ->assertViewHas('schoolLeaders', fn ($leaders) => $leaders->total() === 10);
});

<?php

use App\Livewire\Admin\Media\MediaIndex;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );

    Storage::fake(
        'public',
    );

    Storage::fake(
        'local',
    );
});

test('media operator can move media to trash from media index', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Trash From Index',
    ]);

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaIndex::class,
        )
        ->assertSee(
            'Trash From Index',
        )
        ->call(
            'deleteMedia',
            $media->id,
        )
        ->assertHasNoErrors();

    expect(
        MediaAsset::query()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeFalse();

    expect(
        MediaAsset::onlyTrashed()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeTrue();
});

test('media operator can restore trashed media from media index', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Restore From Index',
    ]);

    $media->delete();

    expect(
        $media->refresh()->trashed(),
    )->toBeTrue();

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaIndex::class,
        )
        ->set(
            'view',
            'trash',
        )
        ->assertSee(
            'Restore From Index',
        )
        ->assertSee(
            'Restore',
        )
        ->call(
            'restoreMedia',
            $media->id,
        )
        ->assertHasNoErrors();

    expect(
        MediaAsset::query()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeTrue();

    expect(
        MediaAsset::onlyTrashed()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeFalse();
});

test('media operator can permanently delete trashed media from media index', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Permanent Delete From Index',
    ]);

    $mediaId = $media->id;

    $media->delete();

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaIndex::class,
        )
        ->set(
            'view',
            'trash',
        )
        ->assertSee(
            'Permanent Delete From Index',
        )
        ->assertSee(
            'Delete Permanently',
        )
        ->call(
            'forceDeleteMedia',
            $mediaId,
        )
        ->assertHasNoErrors();

    expect(
        MediaAsset::withTrashed()
            ->whereKey(
                $mediaId,
            )
            ->exists(),
    )->toBeFalse();
});

test('active media view shows normal media actions', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    MediaAsset::factory()->create([
        'title' => 'Active Action Test',
    ]);

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaIndex::class,
        )
        ->assertSee(
            'Active Action Test',
        )
        ->assertSee(
            'View Details',
        )
        ->assertSee(
            'Move to Trash',
        )
        ->assertDontSee(
            'Delete Permanently',
        );
});

test('trash view shows restore and permanent delete actions', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Trash Action Test',
    ]);

    $media->delete();

    Livewire::actingAs(
        $operator,
    )
        ->test(
            MediaIndex::class,
        )
        ->set(
            'view',
            'trash',
        )
        ->assertSee(
            'Trash Action Test',
        )
        ->assertSee(
            'Restore',
        )
        ->assertSee(
            'Delete Permanently',
        )
        ->assertSee(
            'Permanently delete this media asset? This action cannot be undone.',
        );
});

test('auditor does not see destructive media actions', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    MediaAsset::factory()->create([
        'title' => 'Auditor Media',
    ]);

    Livewire::actingAs(
        $auditor,
    )
        ->test(
            MediaIndex::class,
        )
        ->assertSee(
            'Auditor Media',
        )
        ->assertSee(
            'View Details',
        )
        ->assertDontSee(
            'Move to Trash',
        )
        ->assertDontSee(
            'Delete Permanently',
        );
});

test('auditor cannot call delete media action directly', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Protected Media',
    ]);

    Livewire::actingAs(
        $auditor,
    )
        ->test(
            MediaIndex::class,
        )
        ->call(
            'deleteMedia',
            $media->id,
        )
        ->assertForbidden();

    expect(
        MediaAsset::query()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeTrue();

    expect(
        MediaAsset::onlyTrashed()
            ->whereKey(
                $media->id,
            )
            ->exists(),
    )->toBeFalse();
});
test('active media cannot be force deleted through media index action', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Active Force Delete Protection',
    ]);

    $mediaId = $media->id;

    expect(
        fn () => Livewire::actingAs(
            $operator,
        )
            ->test(
                MediaIndex::class,
            )
            ->call(
                'forceDeleteMedia',
                $mediaId,
            ),
    )->toThrow(
        ModelNotFoundException::class,
    );

    expect(
        MediaAsset::query()
            ->whereKey(
                $mediaId,
            )
            ->exists(),
    )->toBeTrue();

    expect(
        MediaAsset::onlyTrashed()
            ->whereKey(
                $mediaId,
            )
            ->exists(),
    )->toBeFalse();

    expect(
        MediaAsset::withTrashed()
            ->whereKey(
                $mediaId,
            )
            ->exists(),
    )->toBeTrue();
});

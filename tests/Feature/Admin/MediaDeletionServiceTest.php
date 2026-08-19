<?php

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVariantPreset;
use App\Enums\MediaVisibility;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use App\Services\MediaDeletionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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

/**
 * @return array{
 *     media: MediaAsset,
 *     original_path: string,
 *     thumbnail_path: string,
 *     medium_path: string
 * }
 */
function createMediaDeletionFixture(): array
{
    $originalPath =
        'media/image/2026/08/delete-test.jpg';

    $thumbnailPath =
        'media/image/2026/08/variants/delete-test/'
        .'thumbnail-test.webp';

    $mediumPath =
        'media/image/2026/08/variants/delete-test/'
        .'medium-test.webp';

    Storage::disk(
        'public',
    )->put(
        $originalPath,
        'original-image-content',
    );

    Storage::disk(
        'public',
    )->put(
        $thumbnailPath,
        'thumbnail-content',
    );

    Storage::disk(
        'public',
    )->put(
        $mediumPath,
        'medium-content',
    );

    $media = MediaAsset::factory()->create([
        'title' => 'Deletion Test Image',

        'type' => MediaType::Image->value,

        'source' => MediaSource::Upload->value,

        'visibility' => MediaVisibility::Public->value,

        'disk' => 'public',

        'path' => $originalPath,

        'stored_name' => 'delete-test.jpg',

        'original_name' => 'delete-test.jpg',
    ]);

    $media->variants()->create([
        'name' => MediaVariantPreset::Thumbnail->value,

        'disk' => 'public',

        'path' => $thumbnailPath,

        'mime_type' => 'image/webp',

        'extension' => 'webp',

        'width' => 480,

        'height' => 360,

        'size_bytes' => strlen(
            'thumbnail-content',
        ),

        'checksum' => hash(
            'sha256',
            'thumbnail-content',
        ),

        'generated_at' => now(),
    ]);

    $media->variants()->create([
        'name' => MediaVariantPreset::Medium->value,

        'disk' => 'public',

        'path' => $mediumPath,

        'mime_type' => 'image/webp',

        'extension' => 'webp',

        'width' => 1280,

        'height' => 960,

        'size_bytes' => strlen(
            'medium-content',
        ),

        'checksum' => hash(
            'sha256',
            'medium-content',
        ),

        'generated_at' => now(),
    ]);

    return [
        'media' => $media,

        'original_path' => $originalPath,

        'thumbnail_path' => $thumbnailPath,

        'medium_path' => $mediumPath,
    ];
}

test('media operator can move media to trash without deleting physical files', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs(
        $operator,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    $mediaId =
        $media->getKey();

    $deleted = app(
        MediaDeletionService::class,
    )->delete(
        media: $media,

        actor: $operator,
    );

    expect($deleted->trashed())
        ->toBeTrue();

    expect(
        MediaAsset::onlyTrashed()
            ->whereKey($mediaId)
            ->exists(),
    )->toBeTrue();

    expect(
        MediaVariant::query()
            ->where(
                'media_asset_id',
                $mediaId,
            )
            ->count(),
    )->toBe(2);

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['original_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['thumbnail_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['medium_path'],
    );
});

test('media operator can restore trashed media and physical files remain available', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs(
        $operator,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    $mediaId =
        $media->getKey();

    $service = app(
        MediaDeletionService::class,
    );

    $deleted = $service->delete(
        media: $media,

        actor: $operator,
    );

    expect($deleted->trashed())
        ->toBeTrue();

    $restored = $service->restore(
        media: $deleted,

        actor: $operator,
    );

    expect($restored->trashed())
        ->toBeFalse();

    expect(
        MediaAsset::query()
            ->whereKey($mediaId)
            ->exists(),
    )->toBeTrue();

    expect(
        MediaVariant::query()
            ->where(
                'media_asset_id',
                $mediaId,
            )
            ->count(),
    )->toBe(2);

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['original_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['thumbnail_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['medium_path'],
    );
});

test('permanent deletion removes media variants and physical files', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs(
        $operator,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    $mediaId =
        $media->getKey();

    $service = app(
        MediaDeletionService::class,
    );

    $deleted = $service->delete(
        media: $media,

        actor: $operator,
    );

    $service->forceDelete(
        media: $deleted,

        actor: $operator,
    );

    expect(
        MediaAsset::withTrashed()
            ->whereKey($mediaId)
            ->exists(),
    )->toBeFalse();

    expect(
        MediaVariant::query()
            ->where(
                'media_asset_id',
                $mediaId,
            )
            ->exists(),
    )->toBeFalse();

    Storage::disk(
        'public',
    )->assertMissing(
        $fixture['original_path'],
    );

    Storage::disk(
        'public',
    )->assertMissing(
        $fixture['thumbnail_path'],
    );

    Storage::disk(
        'public',
    )->assertMissing(
        $fixture['medium_path'],
    );
});

test('active media cannot be permanently deleted directly', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs(
        $operator,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    expect(
        fn () => app(
            MediaDeletionService::class,
        )->forceDelete(
            media: $media,

            actor: $operator,
        ),
    )->toThrow(
        RuntimeException::class,
        'Only trashed media may be permanently deleted.',
    );

    expect(
        MediaAsset::query()
            ->whereKey(
                $media->getKey(),
            )
            ->exists(),
    )->toBeTrue();

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['original_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['thumbnail_path'],
    );

    Storage::disk(
        'public',
    )->assertExists(
        $fixture['medium_path'],
    );
});

test('auditor cannot delete media', function (): void {
    $auditor = User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $this->actingAs(
        $auditor,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    expect(
        fn () => app(
            MediaDeletionService::class,
        )->delete(
            media: $media,

            actor: $auditor,
        ),
    )->toThrow(
        AuthorizationException::class,
    );

    expect(
        MediaAsset::query()
            ->whereKey(
                $media->getKey(),
            )
            ->exists(),
    )->toBeTrue();
});

test('delete restore and permanent delete actions are audited', function (): void {
    $operator = User::factory()->create();

    $operator->assignRole(
        'Media Operator',
    );

    $this->actingAs(
        $operator,
    );

    $fixture =
        createMediaDeletionFixture();

    $media =
        $fixture['media'];

    $mediaId =
        $media->getKey();

    $service = app(
        MediaDeletionService::class,
    );

    $deleted = $service->delete(
        media: $media,

        actor: $operator,
    );

    $restored = $service->restore(
        media: $deleted,

        actor: $operator,
    );

    $deletedAgain = $service->delete(
        media: $restored,

        actor: $operator,
    );

    $service->forceDelete(
        media: $deletedAgain,

        actor: $operator,
    );

    expect(
        AuditLog::query()
            ->where(
                'event',
                'media.deleted',
            )
            ->where(
                'subject_id',
                $mediaId,
            )
            ->count(),
    )->toBe(2);

    expect(
        AuditLog::query()
            ->where(
                'event',
                'media.restored',
            )
            ->where(
                'subject_id',
                $mediaId,
            )
            ->count(),
    )->toBe(1);

    expect(
        AuditLog::query()
            ->where(
                'event',
                'media.force_deleted',
            )
            ->where(
                'subject_id',
                $mediaId,
            )
            ->count(),
    )->toBe(1);
});

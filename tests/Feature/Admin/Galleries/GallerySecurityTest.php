<?php

use App\Enums\GalleryStatus;
use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Galleries\GalleryEdit;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function galleryInvariantUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function galleryInvariantMedia(
    User $actor,
    MediaVisibility $visibility = MediaVisibility::Public,
): MediaAsset {
    $uuid = Str::uuid()->toString();
    $media = new MediaAsset;

    $media->forceFill([
        'uuid' => $uuid,
        'type' => MediaType::Image->value,
        'source' => MediaSource::Upload->value,
        'visibility' => $visibility->value,
        'title' => 'Gallery Invariant Image',
        'alt_text' => 'Gallery invariant test image',
        'caption' => 'Gallery invariant caption',
        'disk' => 'public',
        'directory' => 'media/images',
        'stored_name' => $uuid.'.jpg',
        'original_name' => 'gallery-invariant.jpg',
        'path' => 'media/images/'.$uuid.'.jpg',
        'external_url' => null,
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size_bytes' => 1024,
        'width' => 1200,
        'height' => 800,
        'checksum' => hash('sha256', $uuid),
        'metadata' => null,
        'uploaded_by' => $actor->id,
    ]);

    $media->save();

    return $media->refresh();
}

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('has the correct gallery permission matrix', function (): void {
    $permissions = [
        'galleries.view',
        'galleries.create',
        'galleries.update',
        'galleries.delete',
        'galleries.publish',
        'galleries.archive',
    ];

    $expected = [
        'Super Administrator' => [true, true, true, true, true, true],
        'Site Administrator' => [true, true, true, true, true, true],
        'Publisher' => [true, true, true, false, true, true],
        'Content Editor' => [true, true, true, false, false, false],
        'Media Operator' => [true, true, true, false, false, false],
        'Auditor' => [true, false, false, false, false, false],
    ];

    foreach ($expected as $role => $expectedResults) {
        $user = galleryInvariantUser($role);

        foreach ($permissions as $index => $permission) {
            expect($user->can($permission))->toBe(
                $expectedResults[$index],
                $role.' / '.$permission,
            );
        }
    }
});

it('enforces one media asset only once per gallery at database level', function (): void {
    $actor = galleryInvariantUser('Site Administrator');
    $gallery = Gallery::factory()->create();
    $media = galleryInvariantMedia($actor);

    GalleryImage::query()->create([
        'gallery_id' => $gallery->id,
        'media_asset_id' => $media->id,
        'caption' => null,
        'alt_text' => null,
        'sort_order' => 0,
        'created_by' => $actor->id,
        'updated_by' => $actor->id,
    ]);

    expect(fn () => GalleryImage::query()->create([
        'gallery_id' => $gallery->id,
        'media_asset_id' => $media->id,
        'caption' => null,
        'alt_text' => null,
        'sort_order' => 1,
        'created_by' => $actor->id,
        'updated_by' => $actor->id,
    ]))->toThrow(QueryException::class);
});

it('enforces unique gallery slugs at database level', function (): void {
    Gallery::factory()->create(['slug' => 'duplicate-gallery-slug']);

    expect(fn () => Gallery::factory()->create(['slug' => 'duplicate-gallery-slug']))
        ->toThrow(QueryException::class);
});

it('does not reuse a slug belonging to a soft deleted gallery', function (): void {
    $actor = galleryInvariantUser('Site Administrator');
    $service = app(GalleryService::class);

    $first = $service->create(actor: $actor, title: 'Historical Parade');
    $service->delete(gallery: $first, actor: $actor);

    expect($first->refresh()->trashed())->toBeTrue();

    $second = $service->create(actor: $actor, title: 'Historical Parade');
    expect($second->slug)->toBe('historical-parade-2');
});

it('soft deletes a draft gallery without deleting its image records', function (): void {
    $actor = galleryInvariantUser('Site Administrator');
    $service = app(GalleryService::class);
    $gallery = Gallery::factory()->create();

    $galleryImage = $service->addImage(
        gallery: $gallery,
        media: galleryInvariantMedia($actor),
        actor: $actor,
    );

    $service->delete(gallery: $gallery, actor: $actor);

    expect(Gallery::withTrashed()->whereKey($gallery->id)->exists())->toBeTrue()
        ->and(Gallery::query()->whereKey($gallery->id)->exists())->toBeFalse()
        ->and(GalleryImage::query()->whereKey($galleryImage->id)->exists())->toBeTrue();
});

it('deletes gallery image rows when a gallery is force deleted', function (): void {
    $actor = galleryInvariantUser('Site Administrator');
    $gallery = Gallery::factory()->create();

    $galleryImage = app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: galleryInvariantMedia($actor),
        actor: $actor,
    );

    $gallery->forceDelete();

    expect(GalleryImage::query()->whereKey($galleryImage->id)->exists())->toBeFalse();
});

it('deletes gallery image rows when the media asset is force deleted', function (): void {
    $actor = galleryInvariantUser('Site Administrator');
    $gallery = Gallery::factory()->create();
    $media = galleryInvariantMedia($actor);

    $galleryImage = app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: $media,
        actor: $actor,
    );

    $media->forceDelete();

    expect(GalleryImage::query()->whereKey($galleryImage->id)->exists())->toBeFalse();
});

it('cannot modify an image belonging to another gallery through gallery edit', function (): void {
    $operator = galleryInvariantUser('Media Operator');
    $firstGallery = Gallery::factory()->create();
    $secondGallery = Gallery::factory()->create();

    $foreignImage = app(GalleryService::class)->addImage(
        gallery: $secondGallery,
        media: galleryInvariantMedia($operator),
        actor: $operator,
    );

    $this->actingAs($operator);

    expect(fn () => Livewire::test(GalleryEdit::class, ['gallery' => $firstGallery])
        ->call('removeImage', $foreignImage->id))
        ->toThrow(ModelNotFoundException::class);

    expect(GalleryImage::query()->whereKey($foreignImage->id)->exists())->toBeTrue();
});

it('does not publish a gallery when an attached media asset was soft deleted', function (): void {
    $publisher = galleryInvariantUser('Publisher');
    $service = app(GalleryService::class);
    $gallery = Gallery::factory()->create();
    $media = galleryInvariantMedia($publisher);

    $service->addImage(gallery: $gallery, media: $media, actor: $publisher);
    $media->delete();

    expect(fn () => $service->publish(gallery: $gallery, actor: $publisher))
        ->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('does not publish a gallery after attached media becomes restricted', function (): void {
    $publisher = galleryInvariantUser('Publisher');
    $service = app(GalleryService::class);
    $gallery = Gallery::factory()->create();
    $media = galleryInvariantMedia($publisher);

    $service->addImage(gallery: $gallery, media: $media, actor: $publisher);
    $media->forceFill(['visibility' => MediaVisibility::Restricted->value])->save();

    expect(fn () => $service->publish(gallery: $gallery, actor: $publisher))
        ->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('cannot archive a draft gallery', function (): void {
    $publisher = galleryInvariantUser('Publisher');
    $gallery = Gallery::factory()->create();

    expect(fn () => app(GalleryService::class)->archive(
        gallery: $gallery,
        actor: $publisher,
    ))->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('cannot publish an archived gallery again', function (): void {
    $publisher = galleryInvariantUser('Publisher');
    $service = app(GalleryService::class);
    $gallery = Gallery::factory()->create();

    $service->addImage(
        gallery: $gallery,
        media: galleryInvariantMedia($publisher),
        actor: $publisher,
    );

    $gallery = $service->publish(gallery: $gallery, actor: $publisher);
    $gallery = $service->archive(gallery: $gallery, actor: $publisher);

    expect(fn () => $service->publish(gallery: $gallery, actor: $publisher))
        ->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Archived);
});

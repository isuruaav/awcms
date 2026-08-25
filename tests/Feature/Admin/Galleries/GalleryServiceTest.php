<?php

use App\Enums\GalleryStatus;
use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function galleryTestMedia(
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
        'title' => 'Gallery Test Image',
        'alt_text' => 'Gallery test image',
        'caption' => 'Gallery test caption',
        'disk' => 'public',
        'directory' => 'media/images',
        'stored_name' => $uuid.'.jpg',
        'original_name' => 'gallery-test.jpg',
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
    $this->actor = User::factory()->create();
    $this->actor->assignRole('Site Administrator');
    $this->service = app(GalleryService::class);
});

it('creates a gallery as draft with a generated slug', function (): void {
    $gallery = $this->service->create(
        actor: $this->actor,
        title: 'Army Day Celebration 2026',
        description: 'Official Army Day gallery.',
    );

    expect($gallery->status)->toBe(GalleryStatus::Draft)
        ->and($gallery->slug)->toBe('army-day-celebration-2026')
        ->and($gallery->created_by)->toBe($this->actor->id)
        ->and($gallery->updated_by)->toBe($this->actor->id)
        ->and($gallery->published_by)->toBeNull()
        ->and($gallery->archived_by)->toBeNull()
        ->and($gallery->uuid)->not->toBeNull();
});

it('generates unique gallery slugs', function (): void {
    $first = $this->service->create(actor: $this->actor, title: 'Training Exercise');
    $second = $this->service->create(actor: $this->actor, title: 'Training Exercise');

    expect($first->slug)->toBe('training-exercise')
        ->and($second->slug)->toBe('training-exercise-2');
});

it('adds a public image to a draft gallery', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryTestMedia($this->actor);

    $galleryImage = $this->service->addImage(
        gallery: $gallery,
        media: $media,
        actor: $this->actor,
        caption: 'Opening ceremony',
        altText: 'Officers attending the opening ceremony',
    );

    expect($galleryImage->gallery_id)->toBe($gallery->id)
        ->and($galleryImage->media_asset_id)->toBe($media->id)
        ->and($galleryImage->caption)->toBe('Opening ceremony')
        ->and($galleryImage->alt_text)->toBe('Officers attending the opening ceremony')
        ->and($galleryImage->sort_order)->toBe(0);
});

it('rejects non public gallery images', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryTestMedia($this->actor, MediaVisibility::Internal);

    expect(fn () => $this->service->addImage(
        gallery: $gallery,
        media: $media,
        actor: $this->actor,
    ))->toThrow(ValidationException::class);
});

it('prevents duplicate images in the same gallery', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryTestMedia($this->actor);

    $this->service->addImage(gallery: $gallery, media: $media, actor: $this->actor);

    expect(fn () => $this->service->addImage(
        gallery: $gallery,
        media: $media,
        actor: $this->actor,
    ))->toThrow(ValidationException::class);
});

it('reorders all images in a gallery', function (): void {
    $gallery = Gallery::factory()->create();

    $first = $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $second = $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $third = $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);

    $this->service->reorderImages(
        gallery: $gallery,
        actor: $this->actor,
        orderedImageIds: [$third->id, $first->id, $second->id],
    );

    $orderedIds = $gallery->refresh()->images()->pluck('id')->all();

    expect($orderedIds)->toBe([$third->id, $first->id, $second->id])
        ->and($third->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1)
        ->and($second->refresh()->sort_order)->toBe(2);
});

it('rejects an incomplete image reorder list', function (): void {
    $gallery = Gallery::factory()->create();
    $first = $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);

    expect(fn () => $this->service->reorderImages(
        gallery: $gallery,
        actor: $this->actor,
        orderedImageIds: [$first->id],
    ))->toThrow(ValidationException::class);
});

it('does not publish an empty gallery', function (): void {
    $gallery = Gallery::factory()->create();

    expect(fn () => $this->service->publish(gallery: $gallery, actor: $this->actor))
        ->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('publishes a gallery that contains a public image', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryTestMedia($this->actor);

    $this->service->addImage(gallery: $gallery, media: $media, actor: $this->actor);
    $published = $this->service->publish(gallery: $gallery, actor: $this->actor);

    expect($published->status)->toBe(GalleryStatus::Published)
        ->and($published->published_at)->not->toBeNull()
        ->and($published->published_by)->toBe($this->actor->id)
        ->and($published->isPublished())->toBeTrue();
});

it('revalidates media before publishing', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryTestMedia($this->actor);

    $this->service->addImage(gallery: $gallery, media: $media, actor: $this->actor);
    $media->forceFill(['visibility' => MediaVisibility::Internal->value])->save();

    expect(fn () => $this->service->publish(gallery: $gallery, actor: $this->actor))
        ->toThrow(ValidationException::class);

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('locks normal editing after publication', function (): void {
    $gallery = Gallery::factory()->create();
    $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $published = $this->service->publish(gallery: $gallery, actor: $this->actor);

    expect(fn () => $this->service->update(
        gallery: $published,
        actor: $this->actor,
        title: 'Changed Published Gallery',
    ))->toThrow(ValidationException::class);
});

it('archives a published gallery', function (): void {
    $gallery = Gallery::factory()->create();
    $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $published = $this->service->publish(gallery: $gallery, actor: $this->actor);
    $archived = $this->service->archive(gallery: $published, actor: $this->actor);

    expect($archived->status)->toBe(GalleryStatus::Archived)
        ->and($archived->archived_at)->not->toBeNull()
        ->and($archived->archived_by)->toBe($this->actor->id)
        ->and($archived->isPublished())->toBeFalse();
});

it('does not delete a published gallery directly', function (): void {
    $gallery = Gallery::factory()->create();
    $this->service->addImage(gallery: $gallery, media: galleryTestMedia($this->actor), actor: $this->actor);
    $published = $this->service->publish(gallery: $gallery, actor: $this->actor);

    expect(fn () => $this->service->delete(gallery: $published, actor: $this->actor))
        ->toThrow(ValidationException::class);

    expect(Gallery::query()->whereKey($gallery->id)->exists())->toBeTrue();
});

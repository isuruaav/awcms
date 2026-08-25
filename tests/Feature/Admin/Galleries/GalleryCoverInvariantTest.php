<?php

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

function galleryCoverUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('Site Administrator');

    return $user;
}

function galleryCoverMedia(User $actor): MediaAsset
{
    $uuid = Str::uuid()->toString();

    $media = new MediaAsset;

    $media->forceFill([
        'uuid' => $uuid,
        'type' => MediaType::Image->value,
        'source' => MediaSource::Upload->value,
        'visibility' => MediaVisibility::Public->value,
        'title' => 'Gallery Cover Test Image',
        'alt_text' => 'Gallery cover test image',
        'caption' => null,
        'disk' => 'public',
        'directory' => 'media/images',
        'stored_name' => $uuid.'.jpg',
        'original_name' => 'gallery-cover.jpg',
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
    $this->actor = galleryCoverUser();
    $this->service = app(GalleryService::class);
});

it('rejects a cover image that is not attached to the gallery', function (): void {
    $gallery = Gallery::factory()->create();
    $unattachedMedia = galleryCoverMedia($this->actor);

    expect(fn () => $this->service->update(
        gallery: $gallery,
        actor: $this->actor,
        title: 'Gallery With Invalid Cover',
        coverMedia: $unattachedMedia,
    ))->toThrow(ValidationException::class);

    expect($gallery->refresh()->cover_media_id)->toBeNull();
});

it('allows an attached gallery image to be used as cover', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryCoverMedia($this->actor);

    $this->service->addImage(
        gallery: $gallery,
        media: $media,
        actor: $this->actor,
    );

    $updated = $this->service->update(
        gallery: $gallery,
        actor: $this->actor,
        title: 'Gallery With Cover',
        coverMedia: $media,
    );

    expect($updated->cover_media_id)->toBe($media->id);
});

it('clears the cover when the cover image is removed', function (): void {
    $gallery = Gallery::factory()->create();
    $media = galleryCoverMedia($this->actor);

    $galleryImage = $this->service->addImage(
        gallery: $gallery,
        media: $media,
        actor: $this->actor,
    );

    $gallery = $this->service->update(
        gallery: $gallery,
        actor: $this->actor,
        title: 'Cover Removal Gallery',
        coverMedia: $media,
    );

    expect($gallery->cover_media_id)->toBe($media->id);

    $this->service->removeImage(
        galleryImage: $galleryImage,
        actor: $this->actor,
    );

    expect($gallery->refresh()->cover_media_id)->toBeNull();
});

it('automatically uses the first ordered image as cover when publishing', function (): void {
    $gallery = Gallery::factory()->create();
    $firstMedia = galleryCoverMedia($this->actor);
    $secondMedia = galleryCoverMedia($this->actor);

    $this->service->addImage(gallery: $gallery, media: $firstMedia, actor: $this->actor);
    $this->service->addImage(gallery: $gallery, media: $secondMedia, actor: $this->actor);

    expect($gallery->refresh()->cover_media_id)->toBeNull();

    $published = $this->service->publish(
        gallery: $gallery,
        actor: $this->actor,
    );

    expect($published->cover_media_id)->toBe($firstMedia->id);
});

it('preserves a manually selected attached cover when publishing', function (): void {
    $gallery = Gallery::factory()->create();
    $firstMedia = galleryCoverMedia($this->actor);
    $secondMedia = galleryCoverMedia($this->actor);

    $this->service->addImage(gallery: $gallery, media: $firstMedia, actor: $this->actor);
    $this->service->addImage(gallery: $gallery, media: $secondMedia, actor: $this->actor);

    $gallery = $this->service->update(
        gallery: $gallery,
        actor: $this->actor,
        title: 'Manual Cover Gallery',
        coverMedia: $secondMedia,
    );

    $published = $this->service->publish(
        gallery: $gallery,
        actor: $this->actor,
    );

    expect($published->cover_media_id)->toBe($secondMedia->id);
});

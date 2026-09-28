<?php

namespace Tests\Feature;

use App\Enums\GalleryStatus;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class GalleryUnpublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublish_keeps_images_and_allows_republishing(): void
    {
        $actor = User::factory()->create();
        Gate::before(static fn (): bool => true);
        $media = MediaAsset::factory()->create([
            'type' => MediaType::Image, 'visibility' => MediaVisibility::Public,
        ]);
        $gallery = Gallery::factory()->create([
            'status' => GalleryStatus::Published, 'published_at' => now(),
            'cover_media_id' => $media->id,
        ]);
        GalleryImage::factory()->create(['gallery_id' => $gallery->id, 'media_asset_id' => $media->id]);
        $service = app(GalleryService::class);
        $draft = $service->unpublish($gallery, $actor);
        $this->assertSame(GalleryStatus::Draft, $draft->status);
        $this->assertNull($draft->published_at);
        $this->assertSame(1, $draft->images()->count());
        $this->assertFalse(Gallery::query()->published()->whereKey($gallery->id)->exists());
        $published = $service->publish($draft, $actor);
        $this->assertSame(GalleryStatus::Published, $published->status);
        $this->assertSame(1, $published->images()->count());
    }

    public function test_unpublish_requires_publication_permission(): void
    {
        $actor = User::factory()->create();
        Gate::before(static fn (): bool => false);
        $gallery = Gallery::factory()->create(['status' => GalleryStatus::Published]);
        try {
            app(GalleryService::class)->unpublish($gallery, $actor);
            $this->fail('Unpublish must be denied.');
        } catch (AuthorizationException) {
            $this->assertSame(GalleryStatus::Published, $gallery->fresh()?->status);
        }
    }

    public function test_legacy_unpublished_record_can_return_to_draft(): void
    {
        $actor = User::factory()->create();
        Gate::before(static fn (): bool => true);
        $gallery = Gallery::factory()->create([
            'status' => GalleryStatus::Archived, 'archived_at' => now(),
        ]);
        $draft = app(GalleryService::class)->unpublish($gallery, $actor);
        $this->assertSame(GalleryStatus::Draft, $draft->status);
        $this->assertNull($draft->archived_at);
        app(GalleryService::class)->delete($draft, $actor);
        $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);
    }
}

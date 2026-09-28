<?php

namespace Tests\Feature;

use App\Enums\GalleryStatus;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Enums\NewsStatus;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\User;
use App\Services\NewsArticleService;
use App\Services\NewsRevisionService;
use App\Services\PublicGalleryFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class NewsGalleryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function image(): MediaAsset
    {
        return MediaAsset::factory()->create([
            'type' => MediaType::Image,
            'visibility' => MediaVisibility::Public,
        ]);
    }

    private function article(?MediaAsset $image = null): News
    {
        $article = News::factory()->create([
            'title' => 'Signals training photographs',
            'status' => NewsStatus::Published,
            'published_at' => now()->subDay(),
            'show_in_gallery' => true,
        ]);
        NewsImage::query()->create([
            'news_id' => $article->id,
            'media_asset_id' => ($image ?? $this->image())->id,
            'sort_order' => 0,
        ]);

        return $article;
    }

    public function test_news_and_independent_albums_share_one_ordered_feed(): void
    {
        $news = $this->article();
        $image = $this->image();
        $gallery = Gallery::factory()->create([
            'status' => GalleryStatus::Published,
            'published_at' => now()->subHour(),
            'cover_media_id' => $image->id,
        ]);
        GalleryImage::factory()->create([
            'gallery_id' => $gallery->id,
            'media_asset_id' => $image->id,
        ]);

        $feed = app(PublicGalleryFeed::class)->paginate();
        $this->assertSame(2, $feed->total());
        $this->assertInstanceOf(Gallery::class, $feed->items()[0]);
        $this->assertSame($gallery->id, $feed->items()[0]->id);
        $this->assertInstanceOf(News::class, $feed->items()[1]);
        $this->assertSame($news->id, $feed->items()[1]->id);
        $this->assertDatabaseCount('gallery_images', 1);
    }

    public function test_unticking_hides_the_album_without_deleting_news_images(): void
    {
        $news = $this->article();
        $news->update(['show_in_gallery' => false]);

        $this->assertSame(0, app(PublicGalleryFeed::class)->paginate()->total());
        $this->get(route('galleries.news', ['newsId' => $news->id]))->assertNotFound();
        $this->assertDatabaseHas('news_images', ['news_id' => $news->id]);
    }

    public function test_draft_scheduled_deleted_and_empty_articles_are_hidden(): void
    {
        $draft = $this->article();
        $draft->update(['status' => NewsStatus::Draft]);
        $scheduled = $this->article();
        $scheduled->update(['published_at' => now()->addDay()]);
        $deleted = $this->article();
        $deleted->delete();
        $empty = $this->article();
        $empty->images()->delete();

        $this->assertSame(0, app(PublicGalleryFeed::class)->paginate()->total());
        foreach ([$draft, $scheduled, $deleted, $empty] as $news) {
            $this->get(route('galleries.news', ['newsId' => $news->id]))->assertNotFound();
        }
    }

    public function test_nonpublic_and_deleted_media_are_excluded_from_feed_and_detail(): void
    {
        $private = $this->image();
        $private->update(['visibility' => MediaVisibility::Internal]);
        $privateNews = $this->article($private);
        $removed = $this->image();
        $removedNews = $this->article($removed);
        $removed->delete();
        $public = $this->image();
        $news = $this->article($public);
        NewsImage::query()->create([
            'news_id' => $news->id, 'media_asset_id' => $private->id, 'sort_order' => 1,
        ]);

        $this->assertSame(1, app(PublicGalleryFeed::class)->paginate()->total());
        $this->get(route('galleries.news', ['newsId' => $privateNews->id]))->assertNotFound();
        $this->get(route('galleries.news', ['newsId' => $removedNews->id]))->assertNotFound();
        $this->get(route('galleries.news', ['newsId' => $news->id]))
            ->assertOk()->assertViewHas('news', function (News $album) use ($public): bool {
                return $album->images->count() === 1
                    && (int) $album->images->first()?->media_asset_id === $public->id;
            });
    }

    public function test_news_title_and_images_stay_live_without_duplicate_gallery_records(): void
    {
        $news = $this->article();
        $news->update(['title' => 'Updated signals title']);
        $second = $this->image();
        NewsImage::query()->create([
            'news_id' => $news->id, 'media_asset_id' => $second->id, 'sort_order' => 1,
        ]);
        $this->get(route('galleries.news', ['newsId' => $news->id]))
            ->assertOk()->assertSeeText('Updated signals title')
            ->assertViewHas('news', fn (News $album): bool => $album->images->count() === 2);
        $this->assertDatabaseCount('galleries', 0);
        $this->assertDatabaseCount('gallery_images', 0);
    }

    public function test_combined_feed_is_paginated_instead_of_loading_every_album(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->article();
        }
        $feed = app(PublicGalleryFeed::class)->paginate();
        $this->assertSame(13, $feed->total());
        $this->assertCount(12, $feed->items());
        $this->get(route('galleries.index', ['page' => 2]))->assertOk()
            ->assertViewHas('albums', fn ($albums): bool => $albums->count() === 1);
    }

    public function test_gallery_preference_is_saved_and_snapshotted_by_article_service(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);
        Gate::before(static fn (): bool => true);
        $source = $this->article();
        $category = $source->category;
        $this->assertNotNull($category);
        $category->update(['is_active' => true]);
        $service = app(NewsArticleService::class);
        $news = $service->create(
            actor: $actor, category: $category,
            title: 'A new gallery preference', content: '<p>Signals school activity.</p>',
            showInGallery: true,
        );
        $this->assertTrue((bool) $news->getAttribute('show_in_gallery'));
        // Older callers omitting the new optional argument must preserve it.
        $news = $service->update(
            news: $news, actor: $actor, category: $category,
            title: 'Edited title', content: '<p>Edited description.</p>',
        );
        $this->assertTrue((bool) $news->getAttribute('show_in_gallery'));
        $news = $service->update(
            news: $news, actor: $actor, category: $category,
            title: 'Hidden album', content: '<p>Images remain in news.</p>',
            showInGallery: false,
        );
        $this->assertFalse((bool) $news->getAttribute('show_in_gallery'));
        $this->assertDatabaseHas('news_revisions', [
            'news_id' => $news->id, 'show_in_gallery' => true,
        ]);
        $revision = $news->revisions()->firstOrFail();
        $restored = app(NewsRevisionService::class)->restore($news, $revision, $actor);
        $this->assertTrue((bool) $restored->getAttribute('show_in_gallery'));
        // A pre-feature revision has NULL; restoring it must not clear today's setting.
        $revision->forceFill(['show_in_gallery' => null])->save();
        $restored = app(NewsRevisionService::class)->restore($restored, $revision, $actor);
        $this->assertTrue((bool) $restored->getAttribute('show_in_gallery'));

    }
}

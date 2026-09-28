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
use App\Services\GalleryLocale;
use App\Services\GalleryService;
use App\Services\PublicGalleryFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class GallerySinhalaTest extends TestCase
{
    use RefreshDatabase;

    private function image(): MediaAsset
    {
        return MediaAsset::factory()->create([
            'type' => MediaType::Image, 'visibility' => MediaVisibility::Public,
        ]);
    }

    private function album(): Gallery
    {
        $image = $this->image();
        $gallery = Gallery::factory()->create([
            'title' => 'Training album', 'title_si' => 'පුහුණු ඡායාරූප',
            'status' => GalleryStatus::Published, 'published_at' => now()->subDay(),
            'cover_media_id' => $image->id,
        ]);
        GalleryImage::factory()->create(['gallery_id' => $gallery->id, 'media_asset_id' => $image->id]);

        return $gallery;
    }

    private function article(string $locale, ?string $group = null): News
    {
        $article = News::factory()->create([
            'locale' => $locale, 'translation_group' => $group,
            'status' => NewsStatus::Published, 'published_at' => now()->subDay(),
            'show_in_gallery' => true,
        ]);
        NewsImage::query()->create([
            'news_id' => $article->id, 'media_asset_id' => $this->image()->id, 'sort_order' => 0,
        ]);

        return $article;
    }

    public function test_manual_album_uses_one_image_set_and_localized_titles(): void
    {
        $gallery = $this->album();
        $this->get(route('galleries.show', ['slug' => $gallery->slug]))
            ->assertOk()->assertSee('Training album');
        $this->get(route('galleries.show.localized', ['locale' => 'si', 'slug' => $gallery->slug]))
            ->assertOk()->assertSee('පුහුණු ඡායාරූප')->assertViewHas('languageVersions');
        $this->assertDatabaseCount('gallery_images', 1);
        $gallery->forceFill(['title_si' => null])->save();
        $this->assertSame('Training album', $gallery->titleForLocale('si'));
    }

    public function test_service_saves_preserves_and_clears_sinhala_title(): void
    {
        Gate::before(static fn (): bool => true);
        $actor = User::factory()->create();
        $service = app(GalleryService::class);
        $gallery = $service->create(actor: $actor, title: 'Annual event', titleSi: 'වාර්ෂික උත්සවය');
        $this->assertSame('වාර්ෂික උත්සවය', $gallery->getAttribute('title_si'));
        $gallery = $service->update(gallery: $gallery, actor: $actor, title: 'Updated event');
        $this->assertSame('වාර්ෂික උත්සවය', $gallery->getAttribute('title_si'));
        $gallery = $service->update(gallery: $gallery, actor: $actor, title: 'Updated event', titleSi: '');
        $this->assertNull($gallery->getAttribute('title_si'));
    }

    public function test_news_feed_is_filtered_by_language_before_pagination(): void
    {
        $this->album();
        $this->article('en');
        $this->article('si');
        $feed = app(PublicGalleryFeed::class);
        $this->assertSame(2, $feed->paginate('en')->total());
        $this->assertSame(2, $feed->paginate('si')->total());
        $this->assertSame(3, $feed->paginate()->total());
        $this->get(route('galleries.index.localized', ['locale' => 'si']))
            ->assertOk()->assertSee('ඡායාරූප ගැලරිය')->assertSee('පුහුණු ඡායාරූප');
    }

    public function test_news_language_switcher_links_only_eligible_translations(): void
    {
        $english = $this->article('en');
        $sinhala = $this->article('si', (string) $english->getAttribute('translation_group'));
        $options = GalleryLocale::versions('en', news: $english);
        $this->assertSame(GalleryLocale::newsUrl('si', $sinhala->id), $options[1]['url']);
        $sinhala->forceFill(['show_in_gallery' => false])->save();
        $options = GalleryLocale::versions('en', news: $english);
        $this->assertFalse($options[1]['available']);
        $this->assertNull($options[1]['url']);
        $this->get(route('galleries.news.localized', ['locale' => 'si', 'newsId' => $sinhala->id]))->assertNotFound();
    }

    public function test_localized_news_detail_rejects_wrong_language_and_private_media(): void
    {
        $news = $this->article('en');
        $this->get(route('galleries.news.localized', ['locale' => 'si', 'newsId' => $news->id]))->assertNotFound();
        $sinhala = $this->article('si');
        $url = route('galleries.news.localized', ['locale' => 'si', 'newsId' => $sinhala->id]);
        $this->get($url)->assertOk();
        $image = $sinhala->images()->firstOrFail()->media;
        $this->assertInstanceOf(MediaAsset::class, $image);
        $image->forceFill(['visibility' => MediaVisibility::Internal])->save();
        $this->get($url)->assertNotFound();
    }

    public function test_sinhala_pagination_keeps_locale_in_links(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->album();
        }
        $this->get(route('galleries.index.localized', ['locale' => 'si']))
            ->assertOk()->assertSee('ඊළඟ')
            ->assertSee('/si/galleries?page=2', false);
    }
}

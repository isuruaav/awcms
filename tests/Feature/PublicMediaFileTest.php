<?php

use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Services\MediaUrlService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('local');
});

it('serves images at a clean filename URL without an identifier folder', function (string $filename, string $publicFilename): void {
    $media = MediaAsset::factory()->create(['original_name' => $filename]);
    Storage::disk('public')->put($media->path, 'original image contents');

    $url = app(MediaUrlService::class)->namedOriginal($media);

    expect($url)->toBe(url('/media-files/'.rawurlencode($publicFilename)))
        ->not->toContain($media->uuid);
    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertStreamedContent('original image contents');
})->with([
    'original filename' => ['448_19_news.jpg', '448_19_news.jpg'],
    'uppercase extension' => ['ArmyTraining.JPG', 'ArmyTraining.JPG'],
    'spaces and URL characters' => ['Training Photo #1 (100%) & review?.jpg', 'Training-Photo-1-100-review.jpg'],
    'Sinhala filename' => ['පුහුණු-රූපය.jpg', 'පුහුණු-රූපය.jpg'],
]);

it('keeps images with the same filename at different public URLs', function (): void {
    $first = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    $second = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    Storage::disk('public')->put($first->path, 'first photo');
    Storage::disk('public')->put($second->path, 'second photo');

    $urls = app(MediaUrlService::class);
    expect($urls->namedOriginal($first))->toBe(url('/media-files/training.jpg'))
        ->and($urls->namedOriginal($second))->toBe(url('/media-files/training-2.jpg'));

    $this->get($urls->namedOriginal($first))->assertOk()->assertStreamedContent('first photo');
    $this->get($urls->namedOriginal($second))->assertOk()->assertStreamedContent('second photo');
});

it('does not serve a copied path after the image becomes private or is trashed', function (): void {
    $media = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    Storage::disk('public')->put($media->path, 'private photo');
    $url = app(MediaUrlService::class)->namedOriginal($media);

    $media->update(['visibility' => MediaVisibility::Internal->value]);
    $this->get($url)->assertNotFound();

    $media->update(['visibility' => MediaVisibility::Public->value]);
    $media->delete();
    $this->get($url)->assertNotFound();
});

it('does not serve files from a private disk even with public metadata', function (): void {
    $media = MediaAsset::factory()->create([
        'original_name' => 'private.jpg',
        'disk' => 'local',
    ]);
    Storage::disk('local')->put($media->path, 'private photo');

    $this->get(route('media.file', ['filename' => 'private.jpg']))
        ->assertNotFound();
});

it('returns not found for a missing file or incorrect filename', function (): void {
    $media = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    $this->get(app(MediaUrlService::class)->namedOriginal($media))->assertNotFound();

    Storage::disk('public')->put($media->path, 'photo');
    $this->get(route('media.file', ['filename' => 'other.jpg']))
        ->assertNotFound();
});

it('continues serving existing UUID links already used in pages', function (): void {
    $media = MediaAsset::factory()->create(['original_name' => '448_19_news.jpg']);
    Storage::disk('public')->put($media->path, 'existing image');

    $this->get(route('media.file.legacy', [
        'media' => $media->uuid,
        'filename' => '448_19_news.jpg',
    ]))->assertOk()->assertStreamedContent('existing image');
});

it('keeps the clean path stable when metadata or the original filename changes', function (): void {
    $media = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    Storage::disk('public')->put($media->path, 'image');
    $url = app(MediaUrlService::class)->namedOriginal($media);

    $media->update(['title' => 'New title', 'original_name' => 'replacement.jpg']);

    expect(app(MediaUrlService::class)->namedOriginal($media))->toBe($url);
    $this->get($url)->assertOk()->assertStreamedContent('image');
});

it('reserves trashed filenames so old links never point at a different upload', function (): void {
    $first = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    $url = app(MediaUrlService::class)->namedOriginal($first);
    $first->delete();

    $second = MediaAsset::factory()->create(['original_name' => 'training.jpg']);
    Storage::disk('public')->put($second->path, 'different photo');

    expect(app(MediaUrlService::class)->namedOriginal($second))->toBe(url('/media-files/training-2.jpg'));
    $this->get($url)->assertNotFound();
});

it('backfills clean paths for existing uploads including duplicate and trashed filenames', function (): void {
    $first = MediaAsset::factory()->create(['original_name' => '448_19_news.jpg']);
    $second = MediaAsset::factory()->create(['original_name' => '448_19_news.jpg']);
    $first->delete();
    Storage::disk('public')->put($second->path, 'existing image');
    $storedPath = $second->path;

    $migration = require database_path('migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php');
    $migration->down();
    $migration->up();

    expect(DB::table('media_assets')->where('id', $first->id)->value('public_filename'))->toBe('448_19_news.jpg')
        ->and($second->refresh()->public_filename)->toBe('448_19_news-2.jpg')
        ->and($second->path)->toBe($storedPath);

    $this->get(app(MediaUrlService::class)->namedOriginal($second))
        ->assertOk()->assertStreamedContent('existing image');
});

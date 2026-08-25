<?php

use App\Enums\GalleryStatus;
use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Livewire\Admin\Galleries\GalleryCreate;
use App\Livewire\Admin\Galleries\GalleryEdit;
use App\Livewire\Admin\Galleries\GalleryIndex;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\GalleryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function galleryLivewireMedia(
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
        'title' => 'Gallery UI Test Image',
        'alt_text' => 'Gallery UI test image',
        'caption' => 'Gallery UI test caption',
        'disk' => 'public',
        'directory' => 'media/images',
        'stored_name' => $uuid.'.jpg',
        'original_name' => 'gallery-ui-test.jpg',
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

function galleryLivewireUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('redirects guests away from the gallery admin route', function (): void {
    $this->get(route('admin.galleries.index'))->assertRedirect(route('login'));
});

it('allows an auditor to view the gallery index', function (): void {
    $auditor = galleryLivewireUser('Auditor');
    $this->actingAs($auditor);
    Livewire::test(GalleryIndex::class)->assertOk();
});

it('allows a content editor to create a gallery', function (): void {
    $editor = galleryLivewireUser('Content Editor');
    $this->actingAs($editor);

    $component = Livewire::test(GalleryCreate::class)
        ->set('title', 'Livewire Gallery Test')
        ->set('eventDate', '2026-08-25')
        ->set('description', 'Gallery created through Livewire.')
        ->call('save')
        ->assertHasNoErrors();

    $gallery = Gallery::query()->where('title', 'Livewire Gallery Test')->firstOrFail();

    expect($gallery->status)->toBe(GalleryStatus::Draft)
        ->and($gallery->slug)->toBe('livewire-gallery-test')
        ->and($gallery->created_by)->toBe($editor->id);

    $component->assertRedirect(route('admin.galleries.edit', ['gallery' => $gallery->id]));
});

it('validates the required gallery title', function (): void {
    $editor = galleryLivewireUser('Content Editor');
    $this->actingAs($editor);

    Livewire::test(GalleryCreate::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    expect(Gallery::query()->count())->toBe(0);
});

it('prevents an auditor from opening gallery edit', function (): void {
    $auditor = galleryLivewireUser('Auditor');
    $gallery = Gallery::factory()->create();
    $this->actingAs($auditor);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])->assertForbidden();
});

it('allows a media operator to add an image to a gallery', function (): void {
    $operator = galleryLivewireUser('Media Operator');
    $gallery = Gallery::factory()->create();
    $media = galleryLivewireMedia($operator);
    $this->actingAs($operator);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->set('selectedMediaId', (string) $media->id)
        ->set('newCaption', 'Livewire image caption')
        ->set('newAltText', 'Officers at the ceremony')
        ->call('addImage')
        ->assertHasNoErrors();

    $galleryImage = GalleryImage::query()
        ->where('gallery_id', $gallery->id)
        ->where('media_asset_id', $media->id)
        ->firstOrFail();

    expect($galleryImage->caption)->toBe('Livewire image caption')
        ->and($galleryImage->alt_text)->toBe('Officers at the ceremony')
        ->and($galleryImage->sort_order)->toBe(0);
});

it('allows a media operator to update gallery image metadata', function (): void {
    $operator = galleryLivewireUser('Media Operator');
    $gallery = Gallery::factory()->create();
    $media = galleryLivewireMedia($operator);

    $galleryImage = app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: $media,
        actor: $operator,
        caption: 'Old caption',
        altText: 'Old alt text',
    );

    $this->actingAs($operator);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->set('imageCaptions.'.$galleryImage->id, 'Updated caption')
        ->set('imageAltTexts.'.$galleryImage->id, 'Updated alternative text')
        ->call('updateImage', $galleryImage->id)
        ->assertHasNoErrors();

    $galleryImage->refresh();
    expect($galleryImage->caption)->toBe('Updated caption')
        ->and($galleryImage->alt_text)->toBe('Updated alternative text');
});

it('allows a media operator to reorder gallery images', function (): void {
    $operator = galleryLivewireUser('Media Operator');
    $gallery = Gallery::factory()->create();
    $service = app(GalleryService::class);

    $first = $service->addImage(gallery: $gallery, media: galleryLivewireMedia($operator), actor: $operator);
    $second = $service->addImage(gallery: $gallery, media: galleryLivewireMedia($operator), actor: $operator);

    expect($first->sort_order)->toBe(0)->and($second->sort_order)->toBe(1);

    $this->actingAs($operator);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->call('moveImageDown', $first->id)
        ->assertHasNoErrors();

    expect($first->refresh()->sort_order)->toBe(1)
        ->and($second->refresh()->sort_order)->toBe(0);
});

it('allows a media operator to remove a gallery image', function (): void {
    $operator = galleryLivewireUser('Media Operator');
    $gallery = Gallery::factory()->create();
    $galleryImage = app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: galleryLivewireMedia($operator),
        actor: $operator,
    );

    $this->actingAs($operator);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->call('removeImage', $galleryImage->id)
        ->assertHasNoErrors();

    expect(GalleryImage::query()->whereKey($galleryImage->id)->exists())->toBeFalse();
});

it('prevents a content editor from publishing a gallery', function (): void {
    $editor = galleryLivewireUser('Content Editor');
    $gallery = Gallery::factory()->create();

    app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: galleryLivewireMedia($editor),
        actor: $editor,
    );

    $this->actingAs($editor);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->call('publish')
        ->assertForbidden();

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('prevents a media operator from publishing a gallery', function (): void {
    $operator = galleryLivewireUser('Media Operator');
    $gallery = Gallery::factory()->create();

    app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: galleryLivewireMedia($operator),
        actor: $operator,
    );

    $this->actingAs($operator);

    Livewire::test(GalleryEdit::class, ['gallery' => $gallery])
        ->call('publish')
        ->assertForbidden();

    expect($gallery->refresh()->status)->toBe(GalleryStatus::Draft);
});

it('allows a publisher to publish and archive a gallery', function (): void {
    $publisher = galleryLivewireUser('Publisher');
    $gallery = Gallery::factory()->create();

    app(GalleryService::class)->addImage(
        gallery: $gallery,
        media: galleryLivewireMedia($publisher),
        actor: $publisher,
    );

    $this->actingAs($publisher);
    $component = Livewire::test(GalleryEdit::class, ['gallery' => $gallery]);

    $component->call('publish')->assertHasNoErrors();
    $gallery->refresh();

    expect($gallery->status)->toBe(GalleryStatus::Published)
        ->and($gallery->published_by)->toBe($publisher->id);

    $component->call('archive')->assertHasNoErrors();
    $gallery->refresh();

    expect($gallery->status)->toBe(GalleryStatus::Archived)
        ->and($gallery->archived_by)->toBe($publisher->id);
});

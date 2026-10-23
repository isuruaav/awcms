<?php

use App\Livewire\Admin\News\NewsIndex;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\User;
use App\Services\NewsDeletionService;
use App\Services\NewsRevisionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (['admin.access', 'news.view', 'news.update', 'news.delete'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
});

/** @param list<string> $permissions */
function newsTrashUser(array $permissions): User
{
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);

    return $user;
}

test('authorized viewer can open a trashed news preview without an edit link', function (): void {
    $viewer = newsTrashUser(['admin.access', 'news.view']);
    $news = News::factory()->create([
        'title' => 'Trashed article preview',
        'content' => '<p>Read-only deleted article content.</p>',
    ]);
    $news->delete();

    $this->actingAs($viewer)
        ->get(route('admin.news.preview', $news))
        ->assertOk()
        ->assertSee('Trashed article preview')
        ->assertSee('Read-only deleted article content.')
        ->assertSee('Back to Trash')
        ->assertSee(route('admin.news.index', ['records' => 'trashed']), false)
        ->assertDontSee(route('admin.news.edit', $news), false)
        ->assertSee('noindex,nofollow', false);
});

test('trash list offers preview restore and confirmed permanent deletion', function (): void {
    $administrator = newsTrashUser(['admin.access', 'news.view', 'news.delete']);
    $news = News::factory()->create();
    $news->delete();

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class, ['recordState' => 'trashed'])
        ->assertSee('Preview')
        ->assertSee('Restore')
        ->assertSee('Delete Permanently')
        ->assertSee('This action cannot be undone.')
        ->assertSee(route('admin.news.preview', $news), false)
        ->assertDontSee(route('admin.news.edit', $news), false);
});

test('permanent deletion removes only the trashed article and its dependent rows', function (): void {
    Storage::fake('public');
    $administrator = newsTrashUser(['admin.access', 'news.view', 'news.delete']);
    $media = MediaAsset::factory()->create(['path' => 'media/testing/shared-news.jpg']);
    Storage::disk('public')->put($media->path, 'shared image bytes');

    $news = News::factory()->create(['featured_image_id' => $media->id]);
    $translation = News::factory()->create([
        'translation_group' => $news->translation_group,
        'locale' => 'si',
        'featured_image_id' => $media->id,
    ]);
    $image = NewsImage::query()->create(['news_id' => $news->id, 'media_asset_id' => $media->id]);
    $otherImage = NewsImage::query()->create(['news_id' => $translation->id, 'media_asset_id' => $media->id]);
    $revision = app(NewsRevisionService::class)->createSnapshot($news, $administrator);
    $news->delete();

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class, ['recordState' => 'trashed'])
        ->call('forceDeleteArticle', $news->id)
        ->assertHasNoErrors()
        ->assertSee('was permanently deleted.');

    $this->assertDatabaseMissing('news', ['id' => $news->id]);
    $this->assertDatabaseMissing('news_images', ['id' => $image->id]);
    $this->assertDatabaseMissing('news_revisions', ['id' => $revision->id]);
    $this->assertDatabaseHas('news', ['id' => $translation->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('news_images', ['id' => $otherImage->id]);
    $this->assertDatabaseHas('media_assets', ['id' => $media->id, 'deleted_at' => null]);
    Storage::disk('public')->assertExists($media->path);
    $this->assertDatabaseHas('audit_logs', [
        'event' => 'news.force_deleted',
        'actor_id' => $administrator->id,
        'subject_type' => News::class,
        'subject_id' => $news->id,
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.news.preview', $news))
        ->assertNotFound();
});

test('permanent deletion rejects an article outside trash', function (): void {
    $administrator = newsTrashUser(['admin.access', 'news.view', 'news.delete']);
    $news = News::factory()->create();

    expect(fn () => app(NewsDeletionService::class)->forceDelete($news->id, $administrator))
        ->toThrow(ValidationException::class, 'Only news articles in Trash may be permanently deleted.');

    $this->assertDatabaseHas('news', ['id' => $news->id, 'deleted_at' => null]);
    expect(AuditLog::query()->where('event', 'news.force_deleted')->exists())->toBeFalse();
});

test('viewer without delete permission cannot permanently delete trash', function (): void {
    $viewer = newsTrashUser(['admin.access', 'news.view']);
    $news = News::factory()->create();
    $news->delete();

    Livewire::actingAs($viewer)
        ->test(NewsIndex::class, ['recordState' => 'trashed'])
        ->assertSee('Preview')
        ->assertDontSee('Delete Permanently')
        ->call('forceDeleteArticle', $news->id)
        ->assertForbidden();

    $this->assertSoftDeleted('news', ['id' => $news->id]);
});

test('service enforces delete permission independently of the news list', function (): void {
    $viewer = newsTrashUser(['admin.access', 'news.view']);
    $news = News::factory()->create();
    $news->delete();

    expect(fn () => app(NewsDeletionService::class)->forceDelete($news->id, $viewer))
        ->toThrow(AuthorizationException::class);

    $this->assertSoftDeleted('news', ['id' => $news->id]);
});

test('trashed preview still requires authentication and news view permission', function (): void {
    $news = News::factory()->create();
    $news->delete();

    $this->get(route('admin.news.preview', $news))->assertRedirect(route('login'));

    $user = newsTrashUser(['admin.access']);
    $this->actingAs($user)->get(route('admin.news.preview', $news))->assertForbidden();
});

test('trash preview support does not expose deleted news publicly or permit editing it', function (): void {
    $news = News::factory()->published()->create(['slug' => 'deleted-public-article']);
    $news->delete();

    $this->get(route('news.show', $news->slug))->assertNotFound();

    $administrator = newsTrashUser(['admin.access', 'news.view', 'news.update']);
    $this->actingAs($administrator)->get(route('admin.news.edit', $news))->assertNotFound();
});

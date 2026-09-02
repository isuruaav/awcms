<?php

use App\Enums\NewsStatus;
use App\Livewire\Admin\News\NewsIndex;
use App\Models\News;
use App\Models\User;
use App\Services\NewsDeletionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('site administrator can publish and unpublish news directly from the news list', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $news = News::factory()->create([
        'title' => 'Direct News Action Test',
        'status' => NewsStatus::Draft->value,
        'published_at' => null,
    ]);

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class)
        ->call(
            'publishArticle',
            $news->id,
        )
        ->assertHasNoErrors();

    $news->refresh();

    expect($news->status)
        ->toBe(NewsStatus::Published)
        ->and($news->published_at)
        ->not->toBeNull();

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class)
        ->call(
            'unpublishArticle',
            $news->id,
        )
        ->assertHasNoErrors();

    $news->refresh();

    expect($news->status)
        ->toBe(NewsStatus::Draft)
        ->and($news->published_at)
        ->toBeNull();
});

test('draft news can be moved to trash and restored from the simplified list', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $news = News::factory()->create([
        'status' => NewsStatus::Draft->value,
    ]);

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class)
        ->call(
            'deleteArticle',
            $news->id,
        )
        ->assertHasNoErrors();

    expect(News::withTrashed()->findOrFail($news->id)->trashed())
        ->toBeTrue();

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class)
        ->call(
            'restoreArticle',
            $news->id,
        )
        ->assertHasNoErrors();

    expect(News::withTrashed()->findOrFail($news->id)->trashed())
        ->toBeFalse();
});

test('published news must be unpublished before deletion', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $news = News::factory()
        ->published()
        ->create();

    expect(function () use (
        $news,
        $administrator,
    ): void {
        app(NewsDeletionService::class)->delete(
            $news,
            $administrator,
        );
    })->toThrow(
        ValidationException::class,
    );

    expect(News::query()->find($news->id))
        ->not->toBeNull();
});

test('active news list exposes only the simplified page style actions', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    News::factory()->create([
        'title' => 'Only Simplified Actions',
        'status' => NewsStatus::Draft->value,
    ]);

    Livewire::actingAs($administrator)
        ->test(NewsIndex::class)
        ->assertSee('Preview')
        ->assertSee('Publish')
        ->assertSee('Edit')
        ->assertSee('Delete')
        ->assertDontSee('History')
        ->assertDontSee('Submit')
        ->assertDontSee('Approve')
        ->assertDontSee('Return')
        ->assertDontSee('Archive');
});

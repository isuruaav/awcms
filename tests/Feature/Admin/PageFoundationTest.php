<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('page stores workflow ownership and blocks', function (): void {
    $creator = User::factory()->create();
    $approver = User::factory()->create();

    $page = Page::factory()->create([
        'title' => 'About the Regiment',
        'slug' => 'about-the-regiment',
        'blocks' => [
            [
                'type' => 'heading',
                'data' => [
                    'text' => 'About the Regiment',
                ],
            ],
        ],
        'status' => PageStatus::Approved->value,
        'created_by' => $creator->id,
        'updated_by' => $creator->id,
        'approved_by' => $approver->id,
        'approved_at' => now(),
    ]);

    expect($page->status)
        ->toBe(PageStatus::Approved)
        ->and($page->blocks)
        ->toBeArray()
        ->and($page->creator?->is($creator))
        ->toBeTrue()
        ->and($page->approver?->is($approver))
        ->toBeTrue();
});

test('page slugs must be unique', function (): void {
    Page::factory()->create([
        'slug' => 'about-us',
    ]);

    expect(
        fn () => Page::factory()->create([
            'slug' => 'about-us',
        ]),
    )->toThrow(QueryException::class);
});

test('published scope returns only currently published pages', function (): void {
    $publishedPage = Page::factory()
        ->published()
        ->create([
            'published_at' => now()->subMinute(),
        ]);

    Page::factory()
        ->published()
        ->create([
            'published_at' => now()->addDay(),
        ]);

    Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    $pages = Page::query()
        ->published()
        ->get();

    expect($pages)
        ->toHaveCount(1)
        ->and($pages->first()?->is($publishedPage))
        ->toBeTrue();
});

test('page workflow allows only valid transitions', function (): void {
    expect(
        PageStatus::Draft->canTransitionTo(
            PageStatus::Submitted,
        ),
    )->toBeTrue();

    expect(
        PageStatus::Submitted->canTransitionTo(
            PageStatus::Approved,
        ),
    )->toBeTrue();

    expect(
        PageStatus::Approved->canTransitionTo(
            PageStatus::Published,
        ),
    )->toBeTrue();

    expect(
        PageStatus::Draft->canTransitionTo(
            PageStatus::Published,
        ),
    )->toBeFalse();

    expect(
        PageStatus::Archived->canTransitionTo(
            PageStatus::Published,
        ),
    )->toBeFalse();
});

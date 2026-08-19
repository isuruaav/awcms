<?php

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

uses(RefreshDatabase::class);

test(
    'news revision service creates a snapshot of current article state',
    function (): void {
        $actor = User::factory()
            ->create();

        $category = NewsCategory::factory()
            ->create();

        $news = News::factory()
            ->create([
                'category_id' => $category->id,
                'title' => 'Original Title',
                'slug' => 'original-title',
                'summary' => 'Original summary.',
                'content' => '<p>Original body.</p>',
                'status' => NewsStatus::Draft,
                'is_featured' => true,
                'seo_title' => 'Original SEO Title',
                'seo_description' => 'Original SEO description.',
            ]);

        $revision = app(
            NewsRevisionService::class,
        )->createSnapshot(
            news: $news,
            actor: $actor,
            reason: 'Content updated.',
        );

        expect($revision->news_id)
            ->toBe($news->id);

        expect($revision->revision_number)
            ->toBe(1);

        expect($revision->title)
            ->toBe('Original Title');

        expect($revision->slug)
            ->toBe('original-title');

        expect($revision->summary)
            ->toBe('Original summary.');

        expect($revision->content)
            ->toBe('<p>Original body.</p>');

        expect($revision->status)
            ->toBe(NewsStatus::Draft);

        expect($revision->is_featured)
            ->toBeTrue();

        expect($revision->created_by)
            ->toBe($actor->id);

        expect($revision->reason)
            ->toBe('Content updated.');
    },
);

test(
    'revision numbers increase sequentially for the same news article',
    function (): void {
        $actor = User::factory()
            ->create();

        $news = News::factory()
            ->create();

        $service = app(
            NewsRevisionService::class,
        );

        $first = $service->createSnapshot(
            news: $news,
            actor: $actor,
        );

        $second = $service->createSnapshot(
            news: $news,
            actor: $actor,
        );

        $third = $service->createSnapshot(
            news: $news,
            actor: $actor,
        );

        expect($first->revision_number)
            ->toBe(1);

        expect($second->revision_number)
            ->toBe(2);

        expect($third->revision_number)
            ->toBe(3);
    },
);

test(
    'different news articles maintain independent revision numbers',
    function (): void {
        $actor = User::factory()
            ->create();

        $firstNews = News::factory()
            ->create();

        $secondNews = News::factory()
            ->create();

        $service = app(
            NewsRevisionService::class,
        );

        $firstRevision =
            $service->createSnapshot(
                news: $firstNews,
                actor: $actor,
            );

        $secondRevision =
            $service->createSnapshot(
                news: $secondNews,
                actor: $actor,
            );

        expect($firstRevision->revision_number)
            ->toBe(1);

        expect($secondRevision->revision_number)
            ->toBe(1);
    },
);

test(
    'deleted news article cannot create a revision',
    function (): void {
        $actor = User::factory()
            ->create();

        $news = News::factory()
            ->create();

        $news->delete();

        app(
            NewsRevisionService::class,
        )->createSnapshot(
            news: $news,
            actor: $actor,
        );
    },
)->throws(
    RuntimeException::class,
    'A deleted news article cannot create a revision.',
);

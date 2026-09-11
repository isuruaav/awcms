<?php

use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsImage;
use App\Models\User;
use App\Services\MediaUploadService;
use App\Services\NewsArticleService;
use App\Services\NewsImageService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );

    Storage::fake(
        'public',
    );

    Storage::fake(
        'local',
    );
});

test('featured image must be selected from the article images', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $news = News::factory()->create([
        'status' => NewsStatus::Draft->value,
        'featured_image_id' => null,
    ]);
    $media = MediaAsset::factory()->create();
    $newsImage = NewsImage::query()->create([
        'news_id' => $news->id,
        'media_asset_id' => $media->id,
        'sort_order' => 0,
    ]);

    app(NewsImageService::class)->setFeaturedImage($newsImage, $editor);

    expect($news->refresh()->featured_image_id)->toBe($media->id);

    app(NewsImageService::class)->clearFeaturedImage($news, $editor);

    expect($news->refresh()->featured_image_id)->toBeNull();

    app(NewsImageService::class)->setFeaturedImage($newsImage, $editor);
    app(NewsImageService::class)->removeImage($newsImage, $editor);

    expect($news->refresh()->featured_image_id)->toBeNull();
});

test('content editor can create a draft news article', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'name' => 'Army News',

            'slug' => 'army-news',

            'is_active' => true,

            'created_by' => $editor->id,

            'updated_by' => $editor->id,
        ]);

    $image = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'news-image.jpg',
            1200,
            800,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Public,

        actor: $editor,

        title: 'News Image',

        altText: 'Army personnel at an official event.',
    );

    $news = app(
        NewsArticleService::class,
    )->create(
        actor: $editor,

        category: $category,

        title: 'Annual Training Programme Begins',

        summary: 'The annual training programme has commenced.',

        content: '<p>The <strong>annual training programme</strong> '
            .'has officially commenced.</p>',

        featuredImage: $image,

        isFeatured: true,

        seoTitle: 'Annual Training Programme',

        seoDescription: 'Official information about the annual training programme.',
    );

    expect(
        $news,
    )->toBeInstanceOf(
        News::class,
    );

    expect(
        $news->title,
    )->toBe(
        'Annual Training Programme Begins',
    );

    expect(
        $news->slug,
    )->toBe(
        'annual-training-programme-begins',
    );

    expect(
        $news->status,
    )->toBe(
        NewsStatus::Draft,
    );

    expect(
        $news->category_id,
    )->toBe(
        $category->id,
    );

    expect(
        $news->featured_image_id,
    )->toBe(
        $image->id,
    );

    expect(
        $news->is_featured,
    )->toBeTrue();

    expect(
        $news->created_by,
    )->toBe(
        $editor->id,
    );

    expect(
        $news->updated_by,
    )->toBe(
        $editor->id,
    );

    $this->assertDatabaseHas(
        'news',
        [
            'id' => $news->id,

            'status' => NewsStatus::Draft->value,

            'category_id' => $category->id,

            'featured_image_id' => $image->id,

            'created_by' => $editor->id,
        ],
    );
});

test('new news articles cannot be created directly as published', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $news = app(
        NewsArticleService::class,
    )->create(
        actor: $editor,

        category: $category,

        title: 'Draft Article',

        content: '<p>This content remains a draft.</p>',
    );

    expect(
        $news->status,
    )->toBe(
        NewsStatus::Draft,
    );

    expect(
        $news->published_by,
    )->toBeNull();
});

test('duplicate article titles receive unique slugs', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $service = app(
        NewsArticleService::class,
    );

    $first = $service->create(
        actor: $editor,

        category: $category,

        title: 'Official Visit',

        content: '<p>First article content.</p>',
    );

    $second = $service->create(
        actor: $editor,

        category: $category,

        title: 'Official Visit',

        content: '<p>Second article content.</p>',
    );

    expect(
        $first->slug,
    )->toBe(
        'official-visit',
    );

    expect(
        $second->slug,
    )->toBe(
        'official-visit-2',
    );
});

test('news rich content is sanitized before storage', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $news = app(
        NewsArticleService::class,
    )->create(
        actor: $editor,

        category: $category,

        title: '<strong>Secure</strong> News',

        summary: '<b>Official</b> summary',

        content: '<p>Safe content.</p>'
            .'<script>alert("xss")</script>',
    );

    expect(
        $news->title,
    )->toBe(
        'Secure News',
    );

    expect(
        $news->summary,
    )->toBe(
        'Official summary',
    );

    expect(
        strtolower(
            (string) $news->content,
        ),
    )->not->toContain(
        '<script',
    );
});

test('content editor can update a news article', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'name' => 'General',

            'slug' => 'general',

            'is_active' => true,
        ]);

    $newCategory =
        NewsCategory::factory()->create([
            'name' => 'Training',

            'slug' => 'training',

            'is_active' => true,
        ]);

    $service = app(
        NewsArticleService::class,
    );

    $news = $service->create(
        actor: $editor,

        category: $category,

        title: 'Original Article',

        summary: 'Original summary.',

        content: '<p>Original article content.</p>',
    );

    $originalId =
        $news->id;

    $originalUuid =
        $news->uuid;

    $originalSlug =
        $news->slug;

    $updated = $service->update(
        news: $news,

        actor: $editor,

        category: $newCategory,

        title: 'Updated Article',

        summary: 'Updated summary.',

        content: '<p>Updated article content.</p>',

        /*
         * No new slug supplied.
         * Existing slug should remain stable.
         */
        slug: null,

        isFeatured: true,
    );

    expect(
        $updated->id,
    )->toBe(
        $originalId,
    );

    expect(
        $updated->uuid,
    )->toBe(
        $originalUuid,
    );

    expect(
        $updated->slug,
    )->toBe(
        $originalSlug,
    );

    expect(
        $updated->title,
    )->toBe(
        'Updated Article',
    );

    expect(
        $updated->summary,
    )->toBe(
        'Updated summary.',
    );

    expect(
        $updated->category_id,
    )->toBe(
        $newCategory->id,
    );

    expect(
        $updated->is_featured,
    )->toBeTrue();

    expect(
        $updated->updated_by,
    )->toBe(
        $editor->id,
    );
});

test('content editor can explicitly change a draft article slug', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $service = app(
        NewsArticleService::class,
    );

    $news = $service->create(
        actor: $editor,

        category: $category,

        title: 'Original Article',

        content: '<p>Original content.</p>',
    );

    $updated = $service->update(
        news: $news,

        actor: $editor,

        category: $category,

        title: 'Original Article',

        content: '<p>Updated content.</p>',

        slug: 'custom-news-url',
    );

    expect(
        $updated->slug,
    )->toBe(
        'custom-news-url',
    );
});

test('auditor cannot create news articles', function (): void {
    $auditor =
        User::factory()->create();

    $auditor->assignRole(
        'Auditor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    expect(
        fn () => app(
            NewsArticleService::class,
        )->create(
            actor: $auditor,

            category: $category,

            title: 'Unauthorized News',

            content: '<p>Unauthorized content.</p>',
        ),
    )->toThrow(
        AuthorizationException::class,
    );

    expect(
        News::query()->count(),
    )->toBe(
        0,
    );
});

test('inactive category cannot be assigned to a news article', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => false,
        ]);

    expect(
        fn () => app(
            NewsArticleService::class,
        )->create(
            actor: $editor,

            category: $category,

            title: 'Invalid Category Article',

            content: '<p>Article content.</p>',
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('internal image cannot be used as a news featured image', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $internalImage = app(
        MediaUploadService::class,
    )->upload(
        file: UploadedFile::fake()->image(
            'internal.jpg',
            800,
            600,
        ),

        type: MediaType::Image,

        visibility: MediaVisibility::Internal,

        actor: $editor,

        title: 'Internal Image',
    );

    expect(
        fn () => app(
            NewsArticleService::class,
        )->create(
            actor: $editor,

            category: $category,

            title: 'News With Internal Image',

            content: '<p>Article content.</p>',

            featuredImage: $internalImage,
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('blank news body is rejected', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    expect(
        fn () => app(
            NewsArticleService::class,
        )->create(
            actor: $editor,

            category: $category,

            title: 'Article Without Body',

            content: '   ',
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('deleted news article cannot be updated', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $service = app(
        NewsArticleService::class,
    );

    $news = $service->create(
        actor: $editor,

        category: $category,

        title: 'Article To Delete',

        content: '<p>Original content.</p>',
    );

    $news->delete();

    expect(
        fn () => $service->update(
            news: $news,

            actor: $editor,

            category: $category,

            title: 'Changed Article',

            content: '<p>Changed content.</p>',
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('published news article cannot be edited through draft update service', function (): void {
    $publisher =
        User::factory()->create();

    $publisher->assignRole(
        'Publisher',
    );

    $category =
        NewsCategory::factory()->create([
            'is_active' => true,
        ]);

    $news =
        News::factory()->published()->create([
            'category_id' => $category->id,

            'title' => 'Published Article',

            'slug' => 'published-article',

            'content' => '<p>Published content.</p>',

            'created_by' => $publisher->id,

            'updated_by' => $publisher->id,

            'published_by' => $publisher->id,
        ]);

    expect(
        fn () => app(
            NewsArticleService::class,
        )->update(
            news: $news,

            actor: $publisher,

            category: $category,

            title: 'Changed Published Article',

            content: '<p>Changed content.</p>',
        ),
    )->toThrow(
        ValidationException::class,
    );

    $news->refresh();

    expect(
        $news->title,
    )->toBe(
        'Published Article',
    );

    expect(
        $news->status,
    )->toBe(
        NewsStatus::Published,
    );
});

test(
    'updating a news article stores the previous state as a revision',
    function (): void {
        $actor = User::factory()
            ->create();

        $actor->givePermissionTo(
            'news.update',
        );

        $category = NewsCategory::factory()
            ->create([
                'is_active' => true,
            ]);

        $news = News::factory()
            ->create([
                'category_id' => $category->id,
                'title' => 'Original News Title',
                'slug' => 'original-news-title',
                'summary' => 'Original summary.',
                'content' => '<p>Original article content.</p>',
                'status' => NewsStatus::Draft,
            ]);

        app(
            NewsArticleService::class,
        )->update(
            news: $news,
            actor: $actor,
            category: $category,
            title: 'Updated News Title',
            content: '<p>Updated article content.</p>',
            summary: 'Updated summary.',
            slug: null,
            featuredImage: null,
            isFeatured: false,
            publishedAt: null,
            seoTitle: null,
            seoDescription: null,
        );

        $news->refresh();

        expect($news->title)
            ->toBe('Updated News Title');

        $revision = $news->revisions()
            ->first();

        expect($revision)
            ->not->toBeNull();

        expect($revision?->revision_number)
            ->toBe(1);

        expect($revision?->title)
            ->toBe('Original News Title');

        expect($revision?->slug)
            ->toBe('original-news-title');

        expect($revision?->summary)
            ->toBe('Original summary.');

        expect($revision?->content)
            ->toBe(
                '<p>Original article content.</p>',
            );

        expect($revision?->status)
            ->toBe(NewsStatus::Draft);
    },
);

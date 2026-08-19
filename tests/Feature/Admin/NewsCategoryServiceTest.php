<?php

use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsCategoryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('site administrator can create a news category', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $category = app(
        NewsCategoryService::class,
    )->create(
        actor: $administrator,

        name: 'Army News',

        description: 'Official Army news articles.',

        sortOrder: 10,

        isActive: true,
    );

    expect(
        $category,
    )->toBeInstanceOf(
        NewsCategory::class,
    );

    expect(
        $category->name,
    )->toBe(
        'Army News',
    );

    expect(
        $category->slug,
    )->toBe(
        'army-news',
    );

    expect(
        $category->description,
    )->toBe(
        'Official Army news articles.',
    );

    expect(
        $category->is_active,
    )->toBeTrue();

    expect(
        $category->sort_order,
    )->toBe(
        10,
    );

    expect(
        $category->created_by,
    )->toBe(
        $administrator->id,
    );

    expect(
        $category->updated_by,
    )->toBe(
        $administrator->id,
    );

    $this->assertDatabaseHas(
        'news_categories',
        [
            'id' => $category->id,

            'name' => 'Army News',

            'slug' => 'army-news',

            'is_active' => true,

            'sort_order' => 10,
        ],
    );
});

test('duplicate news category names receive unique slugs', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $service = app(
        NewsCategoryService::class,
    );

    $first = $service->create(
        actor: $administrator,

        name: 'Training News',
    );

    $second = $service->create(
        actor: $administrator,

        name: 'Training News',
    );

    $third = $service->create(
        actor: $administrator,

        name: 'Training News',
    );

    expect(
        $first->slug,
    )->toBe(
        'training-news',
    );

    expect(
        $second->slug,
    )->toBe(
        'training-news-2',
    );

    expect(
        $third->slug,
    )->toBe(
        'training-news-3',
    );
});

test('publisher can update a news category', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $publisher =
        User::factory()->create();

    $publisher->assignRole(
        'Publisher',
    );

    $service = app(
        NewsCategoryService::class,
    );

    $category = $service->create(
        actor: $administrator,

        name: 'Old Category',

        description: 'Old description.',

        sortOrder: 5,
    );

    $updated = $service->update(
        category: $category,

        actor: $publisher,

        name: 'Operations News',

        description: 'Updated category description.',

        sortOrder: 20,

        isActive: true,
    );

    expect(
        $updated->name,
    )->toBe(
        'Operations News',
    );

    expect(
        $updated->slug,
    )->toBe(
        'operations-news',
    );

    expect(
        $updated->description,
    )->toBe(
        'Updated category description.',
    );

    expect(
        $updated->sort_order,
    )->toBe(
        20,
    );

    expect(
        $updated->updated_by,
    )->toBe(
        $publisher->id,
    );
});

test('updating a category keeps its slug when the name is unchanged', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $service = app(
        NewsCategoryService::class,
    );

    $category = $service->create(
        actor: $administrator,

        name: 'Sports News',
    );

    $originalSlug =
        $category->slug;

    $updated = $service->update(
        category: $category,

        actor: $administrator,

        name: 'Sports News',

        description: 'Army sports news.',
    );

    expect(
        $updated->slug,
    )->toBe(
        $originalSlug,
    );
});

test('news category can be deactivated and activated', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $service = app(
        NewsCategoryService::class,
    );

    $category = $service->create(
        actor: $administrator,

        name: 'Events',

        isActive: true,
    );

    $deactivated = $service->setActive(
        category: $category,

        actor: $administrator,

        isActive: false,
    );

    expect(
        $deactivated->is_active,
    )->toBeFalse();

    $activated = $service->setActive(
        category: $deactivated,

        actor: $administrator,

        isActive: true,
    );

    expect(
        $activated->is_active,
    )->toBeTrue();
});

test('content editor cannot manage news categories', function (): void {
    $editor =
        User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    expect(
        fn () => app(
            NewsCategoryService::class,
        )->create(
            actor: $editor,

            name: 'Unauthorized Category',
        ),
    )->toThrow(
        AuthorizationException::class,
    );

    expect(
        NewsCategory::query()
            ->where(
                'name',
                'Unauthorized Category',
            )
            ->exists(),
    )->toBeFalse();
});

test('category name is required after sanitization', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    expect(
        fn () => app(
            NewsCategoryService::class,
        )->create(
            actor: $administrator,

            name: '   ',
        ),
    )->toThrow(
        ValidationException::class,
    );
});

test('category plain text values are sanitized', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $category = app(
        NewsCategoryService::class,
    )->create(
        actor: $administrator,

        name: '<strong>Training</strong> News',

        description: '<p>Training <b>updates</b>.</p>',
    );

    expect(
        $category->name,
    )->toBe(
        'Training News',
    );

    expect(
        $category->description,
    )->toBe(
        'Training updates.',
    );

    expect(
        $category->slug,
    )->toBe(
        'training-news',
    );
});

test('deleted news category cannot be updated', function (): void {
    $administrator =
        User::factory()->create();

    $administrator->assignRole(
        'Site Administrator',
    );

    $service = app(
        NewsCategoryService::class,
    );

    $category = $service->create(
        actor: $administrator,

        name: 'Deleted Category',
    );

    $category->delete();

    expect(
        fn () => $service->update(
            category: $category,

            actor: $administrator,

            name: 'Changed Category',
        ),
    )->toThrow(
        ValidationException::class,
    );
});

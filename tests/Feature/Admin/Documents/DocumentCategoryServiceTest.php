<?php

use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\DocumentCategoryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test(
    'site administrator can create a document category',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $category =
            app(
                DocumentCategoryService::class,
            )->create(
                actor: $administrator,

                name: 'Annual Reports',

                description: 'Official annual reports and publications.',

                sortOrder: 10,

                isActive: true,
            );

        expect($category)
            ->toBeInstanceOf(
                DocumentCategory::class,
            )
            ->and($category->name)
            ->toBe(
                'Annual Reports',
            )
            ->and($category->slug)
            ->toBe(
                'annual-reports',
            )
            ->and($category->description)
            ->toBe(
                'Official annual reports and publications.',
            )
            ->and($category->is_active)
            ->toBeTrue()
            ->and($category->sort_order)
            ->toBe(10)
            ->and($category->created_by)
            ->toBe(
                $administrator->id,
            )
            ->and($category->updated_by)
            ->toBe(
                $administrator->id,
            );

        $this->assertDatabaseHas(
            'document_categories',
            [
                'id' => $category->id,

                'name' => 'Annual Reports',

                'slug' => 'annual-reports',

                'is_active' => true,

                'sort_order' => 10,
            ],
        );
    },
);

test(
    'duplicate document category names receive unique slugs',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $first =
            $service->create(
                actor: $administrator,

                name: 'Policy Documents',
            );

        $second =
            $service->create(
                actor: $administrator,

                name: 'Policy Documents',
            );

        $third =
            $service->create(
                actor: $administrator,

                name: 'Policy Documents',
            );

        expect($first->slug)
            ->toBe(
                'policy-documents',
            )
            ->and($second->slug)
            ->toBe(
                'policy-documents-2',
            )
            ->and($third->slug)
            ->toBe(
                'policy-documents-3',
            );
    },
);

test(
    'publisher can update a document category',
    function (): void {
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

        $service =
            app(
                DocumentCategoryService::class,
            );

        $category =
            $service->create(
                actor: $administrator,

                name: 'Old Category',

                description: 'Old description.',

                sortOrder: 5,
            );

        $updated =
            $service->update(
                category: $category,

                actor: $publisher,

                name: 'Official Publications',

                description: 'Official publication documents.',

                sortOrder: 20,

                isActive: true,
            );

        expect($updated->name)
            ->toBe(
                'Official Publications',
            )
            ->and($updated->slug)
            ->toBe(
                'official-publications',
            )
            ->and($updated->description)
            ->toBe(
                'Official publication documents.',
            )
            ->and($updated->sort_order)
            ->toBe(20)
            ->and($updated->updated_by)
            ->toBe(
                $publisher->id,
            );
    },
);

test(
    'updating a document category keeps its slug when the name is unchanged',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $category =
            $service->create(
                actor: $administrator,

                name: 'Training Manuals',
            );

        $originalSlug =
            $category->slug;

        $updated =
            $service->update(
                category: $category,

                actor: $administrator,

                name: 'Training Manuals',

                description: 'Official training manuals.',
            );

        expect($updated->slug)
            ->toBe(
                $originalSlug,
            );
    },
);

test(
    'document category can be deactivated and activated',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $category =
            $service->create(
                actor: $administrator,

                name: 'Circulars',

                isActive: true,
            );

        $deactivated =
            $service->setActive(
                category: $category,

                actor: $administrator,

                isActive: false,
            );

        expect($deactivated->is_active)
            ->toBeFalse();

        $activated =
            $service->setActive(
                category: $deactivated,

                actor: $administrator,

                isActive: true,
            );

        expect($activated->is_active)
            ->toBeTrue();
    },
);

test(
    'content editor cannot manage document categories',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        expect(
            fn () => app(
                DocumentCategoryService::class,
            )->create(
                actor: $editor,

                name: 'Unauthorized Category',
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect(
            DocumentCategory::query()
                ->where(
                    'name',
                    'Unauthorized Category',
                )
                ->exists(),
        )->toBeFalse();
    },
);

test(
    'media operator cannot manage document categories',
    function (): void {
        $operator =
            User::factory()->create();

        $operator->assignRole(
            'Media Operator',
        );

        expect(
            fn () => app(
                DocumentCategoryService::class,
            )->create(
                actor: $operator,

                name: 'Operator Category',
            ),
        )->toThrow(
            AuthorizationException::class,
        );

        expect(
            DocumentCategory::query()
                ->where(
                    'name',
                    'Operator Category',
                )
                ->exists(),
        )->toBeFalse();
    },
);

test(
    'document category name is required after sanitization',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        expect(
            fn () => app(
                DocumentCategoryService::class,
            )->create(
                actor: $administrator,

                name: '   ',
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

test(
    'document category plain text values are sanitized',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $category =
            app(
                DocumentCategoryService::class,
            )->create(
                actor: $administrator,

                name: '<strong>Training</strong> Manuals',

                description: '<p>Official <b>training</b> documents.</p>',
            );

        expect($category->name)
            ->toBe(
                'Training Manuals',
            )
            ->and($category->description)
            ->toBe(
                'Official training documents.',
            )
            ->and($category->slug)
            ->toBe(
                'training-manuals',
            );
    },
);

test(
    'deleted document category cannot be updated',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $category =
            $service->create(
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
    },
);

test(
    'deleted document category cannot be activated or deactivated',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $category =
            $service->create(
                actor: $administrator,

                name: 'Deleted Toggle Category',
            );

        $category->delete();

        expect(
            fn () => $service->setActive(
                category: $category,

                actor: $administrator,

                isActive: false,
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

test(
    'soft deleted document category slug is not reused',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $service =
            app(
                DocumentCategoryService::class,
            );

        $first =
            $service->create(
                actor: $administrator,

                name: 'Standing Orders',
            );

        $first->delete();

        $second =
            $service->create(
                actor: $administrator,

                name: 'Standing Orders',
            );

        expect($first->slug)
            ->toBe(
                'standing-orders',
            )
            ->and($second->slug)
            ->toBe(
                'standing-orders-2',
            );
    },
);

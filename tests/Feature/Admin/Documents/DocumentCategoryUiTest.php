<?php

use App\Livewire\Admin\Documents\DocumentCategoryIndex;
use App\Models\DocumentCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test(
    'site administrator can open document category management',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->assertSee(
                'Document Categories',
            )
            ->assertSee(
                'New Category',
            )
            ->assertSee(
                'Back to Documents',
            );
    },
);

test(
    'publisher can open document category management',
    function (): void {
        $publisher =
            User::factory()->create();

        $publisher->assignRole(
            'Publisher',
        );

        Livewire::actingAs(
            $publisher,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->assertSee(
                'Document Categories',
            );
    },
);

test(
    'content editor cannot open document category management',
    function (): void {
        $editor =
            User::factory()->create();

        $editor->assignRole(
            'Content Editor',
        );

        Livewire::actingAs(
            $editor,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->assertForbidden();
    },
);

test(
    'media operator cannot open document category management',
    function (): void {
        $operator =
            User::factory()->create();

        $operator->assignRole(
            'Media Operator',
        );

        Livewire::actingAs(
            $operator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->assertForbidden();
    },
);

test(
    'site administrator can create a document category through livewire',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'openCreate',
            )
            ->assertSet(
                'showForm',
                true,
            )
            ->set(
                'name',
                'Annual Reports',
            )
            ->set(
                'description',
                'Official annual reports and publications.',
            )
            ->set(
                'sortOrder',
                15,
            )
            ->set(
                'isActive',
                true,
            )
            ->call(
                'save',
            )
            ->assertHasNoErrors()
            ->assertSet(
                'showForm',
                false,
            )
            ->assertSet(
                'editingCategoryId',
                null,
            )
            ->assertSee(
                'Document category was created successfully.',
            );

        $this->assertDatabaseHas(
            'document_categories',
            [
                'name' => 'Annual Reports',

                'slug' => 'annual-reports',

                'description' => 'Official annual reports and publications.',

                'sort_order' => 15,

                'is_active' => true,

                'created_by' => $administrator->id,

                'updated_by' => $administrator->id,
            ],
        );
    },
);

test(
    'document category form validates required category name',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'openCreate',
            )
            ->set(
                'name',
                '',
            )
            ->call(
                'save',
            )
            ->assertHasErrors([
                'name' => 'required',
            ]);

        expect(
            DocumentCategory::query()->count(),
        )->toBe(
            0,
        );
    },
);

test(
    'administrator can open an existing document category for editing',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $category =
            DocumentCategory::query()
                ->create([
                    'name' => 'Training Manuals',

                    'slug' => 'training-manuals',

                    'description' => 'Official training manuals.',

                    'sort_order' => 12,

                    'is_active' => true,

                    'created_by' => $administrator->id,

                    'updated_by' => $administrator->id,
                ]);

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'openEdit',
                $category->id,
            )
            ->assertSet(
                'showForm',
                true,
            )
            ->assertSet(
                'editingCategoryId',
                $category->id,
            )
            ->assertSet(
                'name',
                'Training Manuals',
            )
            ->assertSet(
                'description',
                'Official training manuals.',
            )
            ->assertSet(
                'sortOrder',
                12,
            )
            ->assertSet(
                'isActive',
                true,
            );
    },
);

test(
    'publisher can update a document category through livewire',
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

        $category =
            DocumentCategory::query()
                ->create([
                    'name' => 'Old Category',

                    'slug' => 'old-category',

                    'description' => 'Old description.',

                    'sort_order' => 5,

                    'is_active' => true,

                    'created_by' => $administrator->id,

                    'updated_by' => $administrator->id,
                ]);

        Livewire::actingAs(
            $publisher,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'openEdit',
                $category->id,
            )
            ->set(
                'name',
                'Official Publications',
            )
            ->set(
                'description',
                'Official publication documents.',
            )
            ->set(
                'sortOrder',
                25,
            )
            ->set(
                'isActive',
                true,
            )
            ->call(
                'save',
            )
            ->assertHasNoErrors()
            ->assertSet(
                'showForm',
                false,
            )
            ->assertSet(
                'editingCategoryId',
                null,
            )
            ->assertSee(
                'Document category was updated successfully.',
            );

        $this->assertDatabaseHas(
            'document_categories',
            [
                'id' => $category->id,

                'name' => 'Official Publications',

                'slug' => 'official-publications',

                'description' => 'Official publication documents.',

                'sort_order' => 25,

                'is_active' => true,

                'updated_by' => $publisher->id,
            ],
        );
    },
);

test(
    'administrator can deactivate and reactivate a document category',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        $category =
            DocumentCategory::query()
                ->create([
                    'name' => 'Circulars',

                    'slug' => 'circulars',

                    'description' => null,

                    'sort_order' => 10,

                    'is_active' => true,

                    'created_by' => $administrator->id,

                    'updated_by' => $administrator->id,
                ]);

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'toggleActive',
                $category->id,
            )
            ->assertSee(
                'Document category was deactivated successfully.',
            );

        $category->refresh();

        expect(
            $category->is_active,
        )->toBeFalse();

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'toggleActive',
                $category->id,
            )
            ->assertSee(
                'Document category was activated successfully.',
            );

        $category->refresh();

        expect(
            $category->is_active,
        )->toBeTrue();
    },
);

test(
    'document category search filters the visible category list',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        DocumentCategory::query()
            ->create([
                'name' => 'Training Manuals',

                'slug' => 'training-manuals',

                'description' => 'Training related documents.',

                'sort_order' => 10,

                'is_active' => true,

                'created_by' => $administrator->id,

                'updated_by' => $administrator->id,
            ]);

        DocumentCategory::query()
            ->create([
                'name' => 'Annual Reports',

                'slug' => 'annual-reports',

                'description' => 'Annual reporting documents.',

                'sort_order' => 20,

                'is_active' => true,

                'created_by' => $administrator->id,

                'updated_by' => $administrator->id,
            ]);

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->set(
                'search',
                'Training',
            )
            ->assertSee(
                'Training Manuals',
            )
            ->assertDontSee(
                'Annual Reports',
            );
    },
);

test(
    'document category search also matches slug and description',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        DocumentCategory::query()
            ->create([
                'name' => 'Policies',

                'slug' => 'standing-orders',

                'description' => 'Official command publications.',

                'sort_order' => 10,

                'is_active' => true,

                'created_by' => $administrator->id,

                'updated_by' => $administrator->id,
            ]);

        DocumentCategory::query()
            ->create([
                'name' => 'Reports',

                'slug' => 'reports',

                'description' => 'Annual statistical reports.',

                'sort_order' => 20,

                'is_active' => true,

                'created_by' => $administrator->id,

                'updated_by' => $administrator->id,
            ]);

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->set(
                'search',
                'command publications',
            )
            ->assertSee(
                'Policies',
            )
            ->assertDontSee(
                'Reports',
            );
    },
);

test(
    'clear search resets document category search field',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->set(
                'search',
                'Training',
            )
            ->call(
                'clearSearch',
            )
            ->assertSet(
                'search',
                '',
            );
    },
);

test(
    'closing category form resets category form state',
    function (): void {
        $administrator =
            User::factory()->create();

        $administrator->assignRole(
            'Site Administrator',
        );

        Livewire::actingAs(
            $administrator,
        )
            ->test(
                DocumentCategoryIndex::class,
            )
            ->call(
                'openCreate',
            )
            ->set(
                'name',
                'Temporary Category',
            )
            ->set(
                'description',
                'Temporary description.',
            )
            ->set(
                'sortOrder',
                50,
            )
            ->set(
                'isActive',
                false,
            )
            ->call(
                'closeForm',
            )
            ->assertSet(
                'showForm',
                false,
            )
            ->assertSet(
                'editingCategoryId',
                null,
            )
            ->assertSet(
                'name',
                '',
            )
            ->assertSet(
                'description',
                '',
            )
            ->assertSet(
                'sortOrder',
                0,
            )
            ->assertSet(
                'isActive',
                true,
            );
    },
);

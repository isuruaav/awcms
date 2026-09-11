<?php

use App\Enums\NewsStatus;
use App\Livewire\Admin\News\NewsCreate;
use App\Livewire\Admin\News\NewsEdit;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();

    foreach ([
        'admin.access',
        'news.view',
        'news.create',
        'news.update',
    ] as $permission) {
        Permission::findOrCreate(
            $permission,
            'web',
        );
    }
});

function newsUser(
    array $permissions,
): User {
    $user = User::factory()
        ->create();

    $user->givePermissionTo(
        $permissions,
    );

    return $user;
}

test(
    'authorized administrator can open news index',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
        ]);

        $this->actingAs($user)
            ->get(
                route('admin.news.index'),
            )
            ->assertOk()
            ->assertSee('News');
    },
);

test(
    'authorized administrator can open news create page',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.create',
        ]);

        $this->actingAs($user)
            ->get(
                route('admin.news.create'),
            )
            ->assertOk()
            ->assertSee('Create News');
    },
);

test(
    'administrator without news create permission is forbidden',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
        ]);

        $this->actingAs($user)
            ->get(
                route('admin.news.create'),
            )
            ->assertForbidden();
    },
);

test(
    'authorized administrator can create a draft news article',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.create',
            'news.update',
        ]);

        $category = NewsCategory::factory()
            ->create([
                'name' => 'General News',
                'slug' => 'general-news',
                'is_active' => true,
            ]);

        $this->actingAs($user);

        Livewire::test(
            NewsCreate::class,
        )
            ->set(
                'title',
                'AWCMS Test News',
            )
            ->set(
                'summary',
                'This is a test news article.',
            )
            ->set(
                'content',
                '<p>This is the article body.</p>',
            )
            ->set(
                'categoryId',
                (string) $category->id,
            )
            ->call('save')
            ->assertHasNoErrors();

        $news = News::query()
            ->where(
                'title',
                'AWCMS Test News',
            )
            ->first();

        expect($news)
            ->not->toBeNull();

        expect($news?->status)
            ->toBe(
                NewsStatus::Draft,
            );

        expect($news?->category_id)
            ->toBe(
                $category->id,
            );

        expect($news?->slug)
            ->toBe(
                'awcms-test-news',
            );
    },
);

test(
    'news create requires title category and content',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.create',
        ]);

        $this->actingAs($user);

        Livewire::test(
            NewsCreate::class,
        )
            ->set(
                'title',
                '',
            )
            ->set(
                'categoryId',
                '',
            )
            ->set(
                'content',
                '',
            )
            ->call('save')
            ->assertHasErrors([
                'title',
                'categoryId',
                'content',
            ]);
    },
);

test(
    'authorized administrator can update a draft news article',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.update',
        ]);

        $category = NewsCategory::factory()
            ->create([
                'is_active' => true,
            ]);

        $news = News::factory()
            ->create([
                'category_id' => $category->id,
                'title' => 'Original News Title',
                'slug' => 'original-news-title',
                'content' => '<p>Original body.</p>',
                'status' => NewsStatus::Draft,
            ]);

        $this->actingAs($user);

        Livewire::test(
            NewsEdit::class,
            [
                'news' => $news,
            ],
        )
            ->set(
                'title',
                'Updated News Title',
            )
            ->set(
                'content',
                '<p>Updated article body.</p>',
            )
            ->call('save')
            ->assertHasNoErrors();

        $news->refresh();

        expect($news->title)
            ->toBe(
                'Updated News Title',
            );

        expect($news->content)
            ->toContain(
                'Updated article body.',
            );

        expect($news->status)
            ->toBe(
                NewsStatus::Draft,
            );
    },
);

test(
    'selected featured image remains after saving the draft',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.update',
        ]);

        $news = News::factory()->create([
            'status' => NewsStatus::Draft,
            'featured_image_id' => null,
        ]);
        $media = MediaAsset::factory()->create();
        $newsImage = NewsImage::query()->create([
            'news_id' => $news->id,
            'media_asset_id' => $media->id,
            'sort_order' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(NewsEdit::class, ['news' => $news])
            ->call('selectFeaturedImage', $newsImage->id)
            ->call('save')
            ->assertHasNoErrors();

        expect($news->refresh()->featured_image_id)->toBe($media->id);
    },
);

test(
    'published article edit page is read only',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
            'news.update',
        ]);

        $category = NewsCategory::factory()
            ->create([
                'is_active' => true,
            ]);

        $news = News::factory()
            ->published()
            ->create([
                'category_id' => $category->id,
            ]);

        $this->actingAs($user);

        Livewire::test(
            NewsEdit::class,
            [
                'news' => $news,
            ],
        )
            ->assertSet(
                'newsId',
                $news->id,
            )
            ->assertSee(
                'Editing locked',
            )
            ->assertSee(
                'Published',
            );
    },
);

test(
    'administrator without news update permission cannot open edit page',
    function (): void {
        $user = newsUser([
            'admin.access',
            'news.view',
        ]);

        $news = News::factory()
            ->create();

        $this->actingAs($user)
            ->get(
                route(
                    'admin.news.edit',
                    [
                        'news' => $news->id,
                    ],
                ),
            )
            ->assertForbidden();
    },
);

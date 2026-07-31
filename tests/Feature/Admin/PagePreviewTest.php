<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('content editor can preview a draft page', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'title' => 'Draft Preview Page',
        'status' => PageStatus::Draft->value,
    ]);

    $this->actingAs($editor)
        ->get(
            route(
                'admin.pages.preview',
                $page,
            ),
        )
        ->assertOk()
        ->assertSee('Administrative preview')
        ->assertSee('Draft Preview Page')
        ->assertSee('Draft');
});

test('published page can be previewed by an authorised user', function (): void {
    $publisher = User::factory()->create();
    $publisher->assignRole('Publisher');

    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Published Preview Page',
        ]);

    $this->actingAs($publisher)
        ->get(
            route(
                'admin.pages.preview',
                $page,
            ),
        )
        ->assertOk()
        ->assertSee('Published Preview Page')
        ->assertSee('Published');
});

test('user without page view permission cannot access preview', function (): void {
    $user = User::factory()->create();

    $page = Page::factory()->create();

    $this->actingAs($user)
        ->get(
            route(
                'admin.pages.preview',
                $page,
            ),
        )
        ->assertForbidden();
});

test('trashed page cannot be previewed', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $page = Page::factory()->create();
    $page->delete();

    $this->actingAs($administrator)
        ->get(
            route(
                'admin.pages.preview',
                $page->id,
            ),
        )
        ->assertNotFound();
});

test('admin preview content is safely escaped', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'content' => '<script>alert("unsafe")</script>',
    ]);

    $this->actingAs($editor)
        ->get(
            route(
                'admin.pages.preview',
                $page,
            ),
        )
        ->assertOk()
        ->assertSee(
            '&lt;script&gt;',
            false,
        )
        ->assertDontSee(
            '<script>',
            false,
        )
        ->assertSee(
            'noindex,nofollow',
            false,
        );
});

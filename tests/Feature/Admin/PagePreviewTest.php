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

test('admin preview renders only sanitized html', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'content' => '<h2>Preview heading</h2>'
            .'<p onclick="alert(\'unsafe-click\')">Safe text</p>'
            .'<script>alert("unsafe-script")</script>',
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
            '<h2>Preview heading</h2>',
            false,
        )
        ->assertSee(
            '<p>Safe text</p>',
            false,
        )
        ->assertDontSee(
            'unsafe-script',
            false,
        )
        ->assertDontSee(
            'unsafe-click',
            false,
        )
        ->assertDontSee(
            'onclick=',
            false,
        )
        ->assertSee(
            'noindex,nofollow',
            false,
        );
});

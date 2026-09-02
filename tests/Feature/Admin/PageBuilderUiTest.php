<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageCreate;
use App\Livewire\Admin\Pages\PageEdit;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('page create offers visual and html tailwind editors', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->assertSee('Page content')
        ->assertSee('Visual Editor')
        ->assertSee('HTML + Tailwind')
        ->assertSee('Heading 1')
        ->assertSee('Background colour')
        ->assertSee('Image')
        ->assertSee('Table')
        ->assertSee('Full Screen')
        ->assertDontSee('Page Builder')
        ->assertDontSee('SEO & Social Sharing');
});

test('page edit offers visual and html tailwind editors', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,
    ]);

    Livewire::actingAs($editor)
        ->test(
            PageEdit::class,
            [
                'page' => $page,
            ],
        )
        ->assertSee('Page content')
        ->assertSee('Visual Editor')
        ->assertSee('HTML + Tailwind')
        ->assertSee('Heading 1')
        ->assertSee('Background colour')
        ->assertSee('Image')
        ->assertSee('Table')
        ->assertSee('Full Screen')
        ->assertDontSee('Page Builder')
        ->assertDontSee('SEO & Social Sharing');
});

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

test('page create uses simplified html and tailwind editor', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->assertSee('Page content')
        ->assertSee('HTML + Tailwind')
        ->assertDontSee('Page Builder')
        ->assertDontSee('SEO & Social Sharing');
});

test('page edit uses simplified html and tailwind editor', function (): void {
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
        ->assertSee('HTML + Tailwind')
        ->assertDontSee('Page Builder')
        ->assertDontSee('SEO & Social Sharing');
});

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

test('page create displays visual page builder', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->assertSee('Page Builder')
        ->assertSee('Add a Block')
        ->assertSee('Heading')
        ->assertSee('Text')
        ->assertSee('Image')
        ->assertSee('Button')
        ->assertSee('Two Columns')
        ->assertSee('Callout')
        ->assertSee('Divider')
        ->assertSee('Spacer')
        ->assertSee(
            'Start building this page',
        );
});

test('added heading block is displayed in builder ui', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'heading',
        )
        ->assertSet(
            'blocks.0.type',
            'heading',
        )
        ->assertSet(
            'blocks.0.data.level',
            'h2',
        )
        ->assertSee('Heading Level')
        ->assertSee('Heading Text')
        ->assertSee('Move block up');
});

test('added image block displays image configuration fields', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'image',
        )
        ->assertSet(
            'blocks.0.type',
            'image',
        )
        ->assertSet(
            'blocks.0.data.alignment',
            'center',
        )
        ->assertSee('Image URL')
        ->assertSee('Alternative Text')
        ->assertSee('Alignment')
        ->assertSee('Caption');
});

test('added button block displays button configuration fields', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'button',
        )
        ->assertSet(
            'blocks.0.type',
            'button',
        )
        ->assertSet(
            'blocks.0.data.target',
            '_self',
        )
        ->assertSet(
            'blocks.0.data.style',
            'primary',
        )
        ->assertSee('Button Label')
        ->assertSee('Destination URL')
        ->assertSee('Button Style')
        ->assertSee('Open Link');
});

test('added two column block displays both columns', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'two_columns',
        )
        ->assertSet(
            'blocks.0.type',
            'two_columns',
        )
        ->assertSet(
            'blocks.0.data.ratio',
            '50-50',
        )
        ->assertSee('Column Width')
        ->assertSee('Left Column')
        ->assertSee('Right Column');
});

test('added callout block displays callout configuration fields', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'callout',
        )
        ->assertSet(
            'blocks.0.type',
            'callout',
        )
        ->assertSet(
            'blocks.0.data.style',
            'info',
        )
        ->assertSee('Callout Title')
        ->assertSee('Callout Content');
});

test('added spacer block displays spacer configuration', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'spacer',
        )
        ->assertSet(
            'blocks.0.type',
            'spacer',
        )
        ->assertSet(
            'blocks.0.data.size',
            'medium',
        )
        ->assertSee('Space Size');
});

test('page edit loads existing blocks into builder state', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'blocks' => [
            [
                'id' => 'existing_heading_01',

                'type' => 'heading',

                'data' => [
                    'level' => 'h2',

                    'text' => 'Existing Section',
                ],
            ],

            [
                'id' => 'existing_callout_01',

                'type' => 'callout',

                'data' => [
                    'title' => 'Important Notice',

                    'content' => 'Existing notice content.',

                    'style' => 'info',
                ],
            ],
        ],
    ]);

    Livewire::actingAs($editor)
        ->test(
            PageEdit::class,
            [
                'page' => $page,
            ],
        )

        /*
         * Builder UI is rendered.
         */
        ->assertSee('Page Builder')
        ->assertSee('Heading Level')
        ->assertSee('Heading Text')
        ->assertSee('Callout Title')
        ->assertSee('Callout Content')

        /*
         * Existing heading block state.
         */
        ->assertSet(
            'blocks.0.id',
            'existing_heading_01',
        )
        ->assertSet(
            'blocks.0.type',
            'heading',
        )
        ->assertSet(
            'blocks.0.data.level',
            'h2',
        )
        ->assertSet(
            'blocks.0.data.text',
            'Existing Section',
        )

        /*
         * Existing callout block state.
         */
        ->assertSet(
            'blocks.1.id',
            'existing_callout_01',
        )
        ->assertSet(
            'blocks.1.type',
            'callout',
        )
        ->assertSet(
            'blocks.1.data.title',
            'Important Notice',
        )
        ->assertSet(
            'blocks.1.data.content',
            'Existing notice content.',
        )
        ->assertSet(
            'blocks.1.data.style',
            'info',
        );
});

test('builder ui reflects reordered blocks', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'heading',
        )
        ->call(
            'addBlock',
            'text',
        )
        ->call(
            'moveBlockDown',
            0,
        )
        ->assertSet(
            'blocks.0.type',
            'text',
        )
        ->assertSet(
            'blocks.1.type',
            'heading',
        );
});

test('removing final block returns builder to empty state', function (): void {
    $editor = User::factory()->create();

    $editor->assignRole(
        'Content Editor',
    );

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'heading',
        )
        ->assertSet(
            'blocks.0.type',
            'heading',
        )
        ->call(
            'removeBlock',
            0,
        )
        ->assertSet(
            'blocks',
            [],
        )
        ->assertSee(
            'Start building this page',
        );
});

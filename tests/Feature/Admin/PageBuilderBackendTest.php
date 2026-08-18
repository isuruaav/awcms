<?php

use App\Enums\PageStatus;
use App\Livewire\Admin\Pages\PageCreate;
use App\Livewire\Admin\Pages\PageEdit;
use App\Models\Page;
use App\Models\User;
use App\Services\PageRevisionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(
        RolesAndPermissionsSeeder::class,
    );
});

test('page create can add builder blocks', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $component = Livewire::actingAs(
        $editor,
    )->test(
        PageCreate::class,
    );

    $component
        ->call(
            'addBlock',
            'heading',
        )
        ->call(
            'addBlock',
            'text',
        );

    $blocks = $component->get(
        'blocks',
    );

    expect($blocks)
        ->toHaveCount(2)
        ->and($blocks[0]['type'])
        ->toBe('heading')
        ->and($blocks[0]['data']['level'])
        ->toBe('h2')
        ->and($blocks[1]['type'])
        ->toBe('text');
});

test('page builder blocks can be reordered', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $component = Livewire::actingAs(
        $editor,
    )->test(
        PageCreate::class,
    );

    $component
        ->call('addBlock', 'heading')
        ->call('addBlock', 'text')
        ->call('addBlock', 'divider')
        ->call('moveBlockUp', 2);

    $blocks = $component->get(
        'blocks',
    );

    expect($blocks[0]['type'])
        ->toBe('heading')
        ->and($blocks[1]['type'])
        ->toBe('divider')
        ->and($blocks[2]['type'])
        ->toBe('text');

    $component->call(
        'moveBlockDown',
        0,
    );

    $blocks = $component->get(
        'blocks',
    );

    expect($blocks[0]['type'])
        ->toBe('divider')
        ->and($blocks[1]['type'])
        ->toBe('heading');
});

test('page builder block can be removed', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $component = Livewire::actingAs(
        $editor,
    )->test(
        PageCreate::class,
    );

    $component
        ->call('addBlock', 'heading')
        ->call('addBlock', 'text')
        ->call('removeBlock', 0);

    $blocks = $component->get(
        'blocks',
    );

    expect($blocks)
        ->toHaveCount(1)
        ->and($blocks[0]['type'])
        ->toBe('text');
});

test('page create stores sanitized builder blocks', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->set(
            'title',
            'Builder Security Page',
        )
        ->set(
            'slug',
            'builder-security-page',
        )
        ->call(
            'addBlock',
            'heading',
        )
        ->set(
            'blocks.0.data.text',
            '<strong>Welcome</strong>',
        )
        ->call(
            'addBlock',
            'text',
        )
        ->set(
            'blocks.1.data.content',
            '<p>Safe text</p>'.
            '<script>alert("unsafe-builder")</script>',
        )
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::query()
        ->where(
            'slug',
            'builder-security-page',
        )
        ->firstOrFail();

    $blocks = $page->blocks;

    expect($blocks)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($blocks[0]['data']['text'])
        ->toBe('Welcome')
        ->and($blocks[1]['data']['content'])
        ->toContain('Safe text')
        ->and($blocks[1]['data']['content'])
        ->not->toContain('unsafe-builder')
        ->and($blocks[1]['data']['content'])
        ->not->toContain('<script');
});

test('unsupported page builder block cannot be added', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    Livewire::actingAs($editor)
        ->test(PageCreate::class)
        ->call(
            'addBlock',
            'raw_html',
        )
        ->assertHasErrors([
            'blocks',
        ]);
});

test('page edit loads existing builder blocks', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'blocks' => [
            [
                'id' => 'existing_heading_01',

                'type' => 'heading',

                'data' => [
                    'level' => 'h2',

                    'text' => 'Existing Heading',
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
        ->assertSet(
            'blocks.0.type',
            'heading',
        )
        ->assertSet(
            'blocks.0.data.text',
            'Existing Heading',
        );
});

test('page edit stores block changes and creates revision', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    $page = Page::factory()->create([
        'status' => PageStatus::Draft->value,

        'blocks' => [
            [
                'id' => 'existing_heading_01',

                'type' => 'heading',

                'data' => [
                    'level' => 'h2',

                    'text' => 'Original Heading',
                ],
            ],
        ],
    ]);

    app(PageRevisionService::class)
        ->capture(
            page: $page,
            actor: $editor,
            summary: 'Original builder.',
        );

    Livewire::actingAs($editor)
        ->test(
            PageEdit::class,
            [
                'page' => $page,
            ],
        )
        ->set(
            'blocks.0.data.text',
            'Updated Heading',
        )
        ->call(
            'addBlock',
            'divider',
        )
        ->call(
            'save',
        )
        ->assertHasNoErrors();

    $page->refresh();

    expect($page->blocks)
        ->toBeArray()
        ->toHaveCount(2)
        ->and(
            $page->blocks[0]['data']['text'],
        )
        ->toBe('Updated Heading')
        ->and(
            $page->blocks[1]['type'],
        )
        ->toBe('divider')
        ->and(
            $page->revisions()->count(),
        )
        ->toBe(2);
});

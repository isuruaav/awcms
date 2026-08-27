<?php

use App\Models\Menu;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('site administrator can create a menu and custom link item', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $service = app(MenuService::class);
    $menu = $service->createMenu($administrator, 'Primary Navigation', 'primary');
    $item = $service->addItem(
        menu: $menu,
        actor: $administrator,
        label: 'Contact',
        type: 'route',
        url: null,
        routeName: 'contact.create',
        referenceId: null,
        parentId: null,
        openInNewTab: false,
        isActive: true,
    );

    expect($menu)->toBeInstanceOf(Menu::class)
        ->and($menu->location)->toBe('primary')
        ->and($item->label)->toBe('Contact')
        ->and($item->resolvedUrl())->toBe(route('contact.create'));

    $this->assertDatabaseHas('menu_items', [
        'menu_id' => $menu->id,
        'label' => 'Contact',
        'type' => 'route',
        'route_name' => 'contact.create',
    ]);
});

test('content editor cannot manage menus', function (): void {
    $editor = User::factory()->create();
    $editor->assignRole('Content Editor');

    expect(fn () => app(MenuService::class)->createMenu($editor, 'Primary', 'primary'))
        ->toThrow(AuthorizationException::class);
});

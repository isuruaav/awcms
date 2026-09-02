<?php

use App\Models\MenuItem;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('administrator can save three language menu labels and urls', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $service = app(MenuService::class);
    $menu = $service->createMenu($administrator, 'Primary Navigation', 'primary');
    $item = $service->addItem(
        menu: $menu,
        actor: $administrator,
        label: 'About Us',
        type: 'url',
        url: '/pages/about-us',
        routeName: null,
        referenceId: null,
        parentId: null,
        openInNewTab: false,
        isActive: true,
        sinhalaLabel: 'අප ගැන',
        tamilLabel: 'எங்களைப் பற்றி',
        sinhalaUrl: '/si/pages/about-us',
        tamilUrl: '/ta/pages/about-us',
    )->load('translations');

    expect($item->labelForLocale('en'))->toBe('About Us')
        ->and($item->labelForLocale('si'))->toBe('අප ගැන')
        ->and($item->labelForLocale('ta'))->toBe('எங்களைப் பற்றி')
        ->and($item->resolvedUrl('en'))->toBe('/pages/about-us')
        ->and($item->resolvedUrl('si'))->toBe('/si/pages/about-us')
        ->and($item->resolvedUrl('ta'))->toBe('/ta/pages/about-us');

    $this->assertDatabaseHas('menu_item_translations', [
        'menu_item_id' => $item->id,
        'locale' => 'si',
        'label' => 'අප ගැන',
    ]);
});

test('missing menu translation safely falls back to english', function (): void {
    $administrator = User::factory()->create();
    $administrator->assignRole('Site Administrator');

    $menu = app(MenuService::class)->createMenu($administrator, 'Primary Navigation', 'primary');
    $item = app(MenuService::class)->addItem(
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
    )->load('translations');

    expect($item)->toBeInstanceOf(MenuItem::class)
        ->and($item->labelForLocale('si'))->toBe('Contact')
        ->and($item->labelForLocale('ta'))->toBe('Contact');
});

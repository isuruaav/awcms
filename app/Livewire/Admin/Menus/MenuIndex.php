<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Document;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class MenuIndex extends Component
{
    #[Locked]
    public ?int $selectedMenuId = null;

    #[Locked]
    public ?int $editingItemId = null;

    public string $menuName = '';

    public string $menuLocation = '';

    public bool $menuIsActive = true;

    public string $selectedMenuName = '';

    public string $label = '';

    public string $type = 'url';

    public string $url = '';

    public string $routeName = '';

    public ?int $referenceId = null;

    public ?int $parentId = null;

    public bool $openInNewTab = false;

    public bool $itemIsActive = true;

    public function mount(): void
    {
        Gate::authorize('menus.manage');

        $first = Menu::query()
            ->orderBy('name')
            ->first();

        if ($first instanceof Menu) {
            $this->selectedMenuId = $first->id;
            $this->selectedMenuName = $first->name;
        }
    }

    public function selectMenu(int $menuId): void
    {
        Gate::authorize('menus.manage');

        $menu = Menu::query()->findOrFail($menuId);

        $this->selectedMenuId = $menu->id;
        $this->selectedMenuName = $menu->name;
        $this->resetItemForm();
    }

    public function createMenu(): void
    {
        $this->validate([
            'menuName' => ['required', 'string', 'max:150'],
            'menuLocation' => ['required', 'string', 'max:80'],
            'menuIsActive' => ['boolean'],
        ]);

        $menu = app(MenuService::class)->createMenu(
            actor: $this->actor(),
            name: $this->menuName,
            location: $this->menuLocation,
            isActive: $this->menuIsActive,
        );

        $this->selectedMenuId = $menu->id;
        $this->selectedMenuName = $menu->name;
        $this->menuName = '';
        $this->menuLocation = '';
        $this->menuIsActive = true;

        session()->flash('status', 'Menu created successfully.');
    }

    public function renameMenu(): void
    {
        $this->validate([
            'selectedMenuName' => ['required', 'string', 'max:150'],
        ]);

        $menu = $this->selectedMenu();

        $updated = app(MenuService::class)->updateMenu(
            menu: $menu,
            actor: $this->actor(),
            name: $this->selectedMenuName,
            isActive: $menu->is_active,
        );

        $this->selectedMenuName = $updated->name;
        session()->flash('status', 'Menu name updated.');
    }

    public function deleteMenu(): void
    {
        $menu = $this->selectedMenu();
        app(MenuService::class)->deleteMenu($menu, $this->actor());

        $next = Menu::query()->orderBy('name')->first();
        $this->selectedMenuId = $next?->id;
        $this->selectedMenuName = $next->name ?? '';
        $this->resetItemForm();

        session()->flash('status', 'Menu deleted.');
    }

    public function saveItem(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:url,route,page,news,gallery,document'],
            'url' => ['nullable', 'string', 'max:2048'],
            'routeName' => ['nullable', 'string', 'max:150'],
            'referenceId' => ['nullable', 'integer', 'min:1'],
            'parentId' => ['nullable', 'integer', 'min:1'],
            'openInNewTab' => ['boolean'],
            'itemIsActive' => ['boolean'],
        ]);

        $menu = $this->selectedMenu();
        $service = app(MenuService::class);

        if ($this->editingItemId === null) {
            $service->addItem(
                menu: $menu,
                actor: $this->actor(),
                label: $this->label,
                type: $this->type,
                url: $this->url !== '' ? $this->url : null,
                routeName: $this->routeName !== '' ? $this->routeName : null,
                referenceId: $this->referenceId,
                parentId: $this->parentId,
                openInNewTab: $this->openInNewTab,
                isActive: $this->itemIsActive,
            );

            session()->flash('status', 'Menu item added successfully.');
        } else {
            $item = MenuItem::query()
                ->where('menu_id', $menu->id)
                ->findOrFail($this->editingItemId);

            $service->updateItem(
                item: $item,
                actor: $this->actor(),
                label: $this->label,
                type: $this->type,
                url: $this->url !== '' ? $this->url : null,
                routeName: $this->routeName !== '' ? $this->routeName : null,
                referenceId: $this->referenceId,
                parentId: $this->parentId,
                openInNewTab: $this->openInNewTab,
                isActive: $this->itemIsActive,
            );

            session()->flash('status', 'Menu item updated successfully.');
        }

        $this->resetItemForm();
    }

    public function editItem(int $itemId): void
    {
        Gate::authorize('menus.manage');

        $menu = $this->selectedMenu();
        $item = MenuItem::query()
            ->where('menu_id', $menu->id)
            ->findOrFail($itemId);

        $this->editingItemId = $item->id;
        $this->label = $item->label;
        $this->type = $item->type;
        $this->url = $item->url ?? '';
        $this->routeName = $item->route_name ?? '';
        $this->referenceId = $item->reference_id;
        $this->parentId = $item->parent_id;
        $this->openInNewTab = $item->open_in_new_tab;
        $this->itemIsActive = $item->is_active;
        $this->resetValidation();
    }

    public function cancelItemEdit(): void
    {
        $this->resetItemForm();
    }

    public function moveItem(int $itemId, string $direction): void
    {
        $item = MenuItem::query()->findOrFail($itemId);
        app(MenuService::class)->moveItem($item, $this->actor(), $direction);
    }

    public function placeBefore(int $itemId, int $targetId): void
    {
        $item = MenuItem::query()->findOrFail($itemId);
        $target = MenuItem::query()->findOrFail($targetId);

        app(MenuService::class)->placeBefore(
            $item,
            $target,
            $this->actor(),
        );
    }

    public function deleteItem(int $itemId): void
    {
        $item = MenuItem::query()->findOrFail($itemId);
        app(MenuService::class)->deleteItem($item, $this->actor());

        if ($this->editingItemId === $itemId) {
            $this->resetItemForm();
        }

        session()->flash('status', 'Menu item deleted.');
    }

    public function toggleMenu(): void
    {
        $menu = $this->selectedMenu();

        app(MenuService::class)->updateMenu(
            menu: $menu,
            actor: $this->actor(),
            name: $menu->name,
            isActive: ! $menu->is_active,
        );

        session()->flash('status', 'Menu status updated.');
    }

    public function render(): View
    {
        $menus = Menu::query()
            ->withCount('items')
            ->orderBy('name')
            ->get();

        $selected = $this->selectedMenuId !== null
            ? Menu::query()
                ->with(['rootItems.children'])
                ->find($this->selectedMenuId)
            : null;

        return view(
            'livewire.admin.menus.menu-index',
            [
                'menus' => $menus,
                'selectedMenu' => $selected,
                'pages' => Page::query()
                    ->published()
                    ->orderBy('title')
                    ->limit(200)
                    ->get(['id', 'title']),
                'newsItems' => News::query()
                    ->published()
                    ->latest('published_at')
                    ->limit(200)
                    ->get(['id', 'title']),
                'galleries' => Gallery::query()
                    ->published()
                    ->latest('published_at')
                    ->limit(200)
                    ->get(['id', 'title']),
                'documents' => Document::query()
                    ->published()
                    ->latest('published_at')
                    ->limit(200)
                    ->get(['id', 'title']),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Menu Builder',
            ],
        );
    }

    private function selectedMenu(): Menu
    {
        if ($this->selectedMenuId === null) {
            throw ValidationException::withMessages([
                'menu' => 'Create or select a menu first.',
            ]);
        }

        return Menu::query()->findOrFail($this->selectedMenuId);
    }

    private function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->label = '';
        $this->type = 'url';
        $this->url = '';
        $this->routeName = '';
        $this->referenceId = null;
        $this->parentId = null;
        $this->openInNewTab = false;
        $this->itemIsActive = true;
        $this->resetValidation();
    }

    private function actor(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'authorization' => 'An authenticated administrator is required.',
            ]);
        }

        return $user;
    }
}

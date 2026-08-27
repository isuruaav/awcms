<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class MenuService
{
    public function createMenu(User $actor, string $name, string $location, bool $isActive = true): Menu
    {
        Gate::forUser($actor)->authorize('menus.manage');

        $name = $this->cleanText($name, 150, 'name', 'Menu name');
        $location = Str::slug($this->cleanText($location, 80, 'location', 'Menu location'));

        if ($location === '') {
            throw ValidationException::withMessages(['location' => 'The menu location is required.']);
        }

        if (Menu::query()->withTrashed()->where('location', $location)->exists()) {
            throw ValidationException::withMessages(['location' => 'That menu location is already in use.']);
        }

        return DB::transaction(function () use ($actor, $name, $location, $isActive): Menu {
            $menu = Menu::query()->create([
                'name' => $name,
                'location' => $location,
                'is_active' => $isActive,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            app(AuditLogger::class)->log(
                event: 'menus.created',
                description: 'A navigation menu was created.',
                actor: $actor,
                subject: $menu,
                newValues: ['name' => $name, 'location' => $location, 'is_active' => $isActive],
            );

            return $menu->refresh();
        }, 3);
    }

    public function updateMenu(Menu $menu, User $actor, string $name, bool $isActive): Menu
    {
        Gate::forUser($actor)->authorize('menus.manage');
        $name = $this->cleanText($name, 150, 'name', 'Menu name');

        return DB::transaction(function () use ($menu, $actor, $name, $isActive): Menu {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $old = ['name' => $locked->name, 'is_active' => $locked->is_active];
            $locked->forceFill(['name' => $name, 'is_active' => $isActive, 'updated_by' => $actor->id])->save();

            app(AuditLogger::class)->log(
                event: 'menus.updated',
                description: 'A navigation menu was updated.',
                actor: $actor,
                subject: $locked,
                oldValues: $old,
                newValues: ['name' => $name, 'is_active' => $isActive],
            );

            return $locked->refresh();
        }, 3);
    }

    public function addItem(
        Menu $menu,
        User $actor,
        string $label,
        string $type,
        ?string $url,
        ?string $routeName,
        ?int $referenceId,
        ?int $parentId,
        bool $openInNewTab,
        bool $isActive,
    ): MenuItem {
        Gate::forUser($actor)->authorize('menus.manage');
        $label = $this->cleanText($label, 150, 'label', 'Menu item label');
        $type = in_array($type, ['url', 'route', 'page', 'news', 'gallery', 'document'], true) ? $type : 'url';
        $url = $this->nullableUrl($url);
        $routeName = $this->nullableText($routeName, 150);

        if ($type === 'url' && $url === null) {
            throw ValidationException::withMessages(['url' => 'A URL is required for URL menu items.']);
        }

        if ($type === 'route' && $routeName === null) {
            throw ValidationException::withMessages(['routeName' => 'A route name is required for route menu items.']);
        }

        if ($type === 'route' && ! Route::has($routeName)) {
            throw ValidationException::withMessages(['routeName' => 'The selected named route does not exist.']);
        }

        if (in_array($type, ['page', 'news', 'gallery', 'document'], true) && $referenceId === null) {
            throw ValidationException::withMessages(['referenceId' => 'Select the content item to link.']);
        }

        if ($parentId !== null) {
            $parent = MenuItem::query()->where('menu_id', $menu->id)->find($parentId);
            if (! $parent instanceof MenuItem) {
                throw ValidationException::withMessages(['parentId' => 'The selected parent menu item is invalid.']);
            }
        }

        return DB::transaction(function () use ($menu, $actor, $label, $type, $url, $routeName, $referenceId, $parentId, $openInNewTab, $isActive): MenuItem {
            $nextOrder = (int) MenuItem::query()
                ->where('menu_id', $menu->id)
                ->where('parent_id', $parentId)
                ->max('sort_order') + 10;

            $item = MenuItem::query()->create([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'label' => $label,
                'type' => $type,
                'url' => $url,
                'route_name' => $routeName,
                'reference_id' => $referenceId,
                'open_in_new_tab' => $openInNewTab,
                'is_active' => $isActive,
                'sort_order' => $nextOrder,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            app(AuditLogger::class)->log(
                event: 'menus.item-created',
                description: 'A menu item was created.',
                actor: $actor,
                subject: $item,
                newValues: ['menu_id' => $menu->id, 'label' => $label, 'type' => $type, 'parent_id' => $parentId],
            );

            return $item->refresh();
        }, 3);
    }

    public function updateItem(
        MenuItem $item,
        User $actor,
        string $label,
        string $type,
        ?string $url,
        ?string $routeName,
        ?int $referenceId,
        ?int $parentId,
        bool $openInNewTab,
        bool $isActive,
    ): MenuItem {
        Gate::forUser($actor)->authorize('menus.manage');
        $label = $this->cleanText($label, 150, 'label', 'Menu item label');
        $type = in_array($type, ['url', 'route', 'page', 'news', 'gallery', 'document'], true) ? $type : 'url';
        $url = $this->nullableUrl($url);
        $routeName = $this->nullableText($routeName, 150);

        if ($type === 'url' && $url === null) {
            throw ValidationException::withMessages(['url' => 'A URL is required for URL menu items.']);
        }

        if ($type === 'route' && $routeName === null) {
            throw ValidationException::withMessages(['routeName' => 'A route name is required for route menu items.']);
        }

        if ($type === 'route' && ! Route::has($routeName)) {
            throw ValidationException::withMessages(['routeName' => 'The selected named route does not exist.']);
        }

        if (in_array($type, ['page', 'news', 'gallery', 'document'], true) && $referenceId === null) {
            throw ValidationException::withMessages(['referenceId' => 'Select the content item to link.']);
        }

        if ($parentId === $item->id) {
            throw ValidationException::withMessages(['parentId' => 'A menu item cannot be its own parent.']);
        }

        if ($parentId !== null) {
            $parent = MenuItem::query()
                ->where('menu_id', $item->menu_id)
                ->whereNull('parent_id')
                ->find($parentId);

            if (! $parent instanceof MenuItem) {
                throw ValidationException::withMessages(['parentId' => 'The selected parent menu item is invalid.']);
            }

            if ($item->children()->exists()) {
                throw ValidationException::withMessages(['parentId' => 'An item with child links must remain a top-level item.']);
            }
        }

        return DB::transaction(function () use ($item, $actor, $label, $type, $url, $routeName, $referenceId, $parentId, $openInNewTab, $isActive): MenuItem {
            $locked = MenuItem::query()->lockForUpdate()->findOrFail($item->id);
            $old = $locked->only(['label', 'type', 'url', 'route_name', 'reference_id', 'parent_id', 'open_in_new_tab', 'is_active']);

            if ($locked->parent_id !== $parentId) {
                $locked->sort_order = ((int) MenuItem::query()
                    ->where('menu_id', $locked->menu_id)
                    ->where('parent_id', $parentId)
                    ->max('sort_order')) + 10;
            }

            $locked->forceFill([
                'parent_id' => $parentId,
                'label' => $label,
                'type' => $type,
                'url' => $url,
                'route_name' => $routeName,
                'reference_id' => $referenceId,
                'open_in_new_tab' => $openInNewTab,
                'is_active' => $isActive,
                'updated_by' => $actor->id,
            ])->save();

            app(AuditLogger::class)->log(
                event: 'menus.item-updated',
                description: 'A menu item was updated.',
                actor: $actor,
                subject: $locked,
                oldValues: $old,
                newValues: $locked->only(['label', 'type', 'url', 'route_name', 'reference_id', 'parent_id', 'open_in_new_tab', 'is_active']),
            );

            return $locked->refresh();
        }, 3);
    }

    public function deleteMenu(Menu $menu, User $actor): void
    {
        Gate::forUser($actor)->authorize('menus.manage');

        DB::transaction(function () use ($menu, $actor): void {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);

            app(AuditLogger::class)->log(
                event: 'menus.deleted',
                description: 'A navigation menu was deleted.',
                actor: $actor,
                subject: $locked,
                oldValues: ['name' => $locked->name, 'location' => $locked->location],
            );

            $locked->delete();
        }, 3);
    }

    public function placeBefore(MenuItem $item, MenuItem $target, User $actor): void
    {
        Gate::forUser($actor)->authorize('menus.manage');

        if ($item->id === $target->id || $item->menu_id !== $target->menu_id || $item->parent_id !== $target->parent_id) {
            return;
        }

        DB::transaction(function () use ($item, $target, $actor): void {
            $siblings = MenuItem::query()
                ->where('menu_id', $item->menu_id)
                ->where('parent_id', $item->parent_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $ordered = [];
            foreach ($siblings as $sibling) {
                if ($sibling->id === $item->id) {
                    continue;
                }
                if ($sibling->id === $target->id) {
                    $ordered[] = $item->id;
                }
                $ordered[] = $sibling->id;
            }

            foreach ($ordered as $index => $id) {
                MenuItem::query()->whereKey($id)->update([
                    'sort_order' => ($index + 1) * 10,
                    'updated_by' => $actor->id,
                    'updated_at' => now(),
                ]);
            }
        }, 3);
    }

    public function deleteItem(MenuItem $item, User $actor): void
    {
        Gate::forUser($actor)->authorize('menus.manage');

        DB::transaction(function () use ($item, $actor): void {
            $locked = MenuItem::query()->lockForUpdate()->findOrFail($item->id);
            app(AuditLogger::class)->log(
                event: 'menus.item-deleted',
                description: 'A menu item was deleted.',
                actor: $actor,
                subject: $locked,
                oldValues: ['label' => $locked->label, 'menu_id' => $locked->menu_id],
            );
            $locked->delete();
        }, 3);
    }

    public function moveItem(MenuItem $item, User $actor, string $direction): void
    {
        Gate::forUser($actor)->authorize('menus.manage');

        if (! in_array($direction, ['up', 'down'], true)) {
            return;
        }

        DB::transaction(function () use ($item, $actor, $direction): void {
            $locked = MenuItem::query()->lockForUpdate()->findOrFail($item->id);
            $query = MenuItem::query()
                ->where('menu_id', $locked->menu_id)
                ->where('parent_id', $locked->parent_id);

            $sibling = $direction === 'up'
                ? $query->where('sort_order', '<', $locked->sort_order)->orderByDesc('sort_order')->orderByDesc('id')->first()
                : $query->where('sort_order', '>', $locked->sort_order)->orderBy('sort_order')->orderBy('id')->first();

            if (! $sibling instanceof MenuItem) {
                return;
            }

            $oldOrder = $locked->sort_order;
            $locked->sort_order = $sibling->sort_order;
            $locked->updated_by = $actor->id;
            $locked->save();
            $sibling->sort_order = $oldOrder;
            $sibling->updated_by = $actor->id;
            $sibling->save();
        }, 3);
    }

    private function cleanText(string $value, int $max, string $key, string $label): string
    {
        $value = trim(strip_tags($value));
        $normalised = preg_replace('/\s+/u', ' ', $value);
        $value = is_string($normalised) ? trim($normalised) : '';

        if ($value === '') {
            throw ValidationException::withMessages([$key => $label.' is required.']);
        }

        if (mb_strlen($value) > $max) {
            throw ValidationException::withMessages([$key => $label.' may not exceed '.$max.' characters.']);
        }

        return $value;
    }

    private function nullableText(?string $value, int $max): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(strip_tags($value));

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function nullableUrl(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return mb_substr($value, 0, 2048);
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages(['url' => 'Enter a valid absolute URL or a path beginning with /.']);
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (! is_string($scheme) || ! in_array(mb_strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true)) {
            throw ValidationException::withMessages(['url' => 'Only http, https, mailto and tel URLs are allowed.']);
        }

        return mb_substr($value, 0, 2048);
    }
}

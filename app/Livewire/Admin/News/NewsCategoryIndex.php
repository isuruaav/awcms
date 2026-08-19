<?php

namespace App\Livewire\Admin\News;

use App\Models\NewsCategory;
use App\Models\User;
use App\Services\NewsCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class NewsCategoryIndex extends Component
{
    public string $search = '';

    public bool $showForm = false;

    #[Locked]
    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $description = '';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize(
            'news.categories.manage',
        );
    }

    public function openCreate(): void
    {
        Gate::authorize(
            'news.categories.manage',
        );

        $this->resetForm();

        $this->showForm = true;
    }

    public function openEdit(
        int $categoryId,
    ): void {
        Gate::authorize(
            'news.categories.manage',
        );

        $category = NewsCategory::query()
            ->findOrFail(
                $categoryId,
            );

        $this->editingCategoryId =
            (int) $category->getKey();

        $this->name = (string) (
            $category->getAttribute(
                'name',
            )
            ?? ''
        );

        $this->description = (string) (
            $category->getAttribute(
                'description',
            )
            ?? ''
        );

        $sortOrder =
            $category->getAttribute(
                'sort_order',
            );

        $this->sortOrder =
            is_int($sortOrder)
                ? $sortOrder
                : (int) $sortOrder;

        $this->isActive =
            (bool) $category->getAttribute(
                'is_active',
            );

        $this->resetValidation();

        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        Gate::authorize(
            'news.categories.manage',
        );

        $this->normaliseForm();

        $this->validate();

        $actor =
            $this->actor();

        $service = app(
            NewsCategoryService::class,
        );

        if ($this->editingCategoryId === null) {
            $service->create(
                actor: $actor,

                name: $this->name,

                description: $this->description !== ''
                        ? $this->description
                        : null,

                sortOrder: $this->sortOrder,

                isActive: $this->isActive,
            );

            session()->flash(
                'status',
                'News category was created successfully.',
            );

            $this->resetForm();

            return;
        }

        $category = NewsCategory::query()
            ->findOrFail(
                $this->editingCategoryId,
            );

        $service->update(
            category: $category,

            actor: $actor,

            name: $this->name,

            description: $this->description !== ''
                    ? $this->description
                    : null,

            sortOrder: $this->sortOrder,

            isActive: $this->isActive,
        );

        session()->flash(
            'status',
            'News category was updated successfully.',
        );

        $this->resetForm();
    }

    public function toggleActive(
        int $categoryId,
    ): void {
        Gate::authorize(
            'news.categories.manage',
        );

        $category = NewsCategory::query()
            ->findOrFail(
                $categoryId,
            );

        $currentStatus =
            (bool) $category->getAttribute(
                'is_active',
            );

        $updated = app(
            NewsCategoryService::class,
        )->setActive(
            category: $category,

            actor: $this->actor(),

            isActive: ! $currentStatus,
        );

        if (
            $this->editingCategoryId
                === (int) $updated->getKey()
        ) {
            $this->isActive =
                (bool) $updated->getAttribute(
                    'is_active',
                );
        }

        session()->flash(
            'status',
            $currentStatus
                ? 'News category was deactivated successfully.'
                : 'News category was activated successfully.',
        );
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'sortOrder' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],

            'isActive' => [
                'boolean',
            ],
        ];
    }

    public function render(): View
    {
        $query = NewsCategory::query()
            ->withCount(
                'news',
            );

        $search = trim(
            $this->search,
        );

        if ($search !== '') {
            $query->where(
                function (
                    Builder $builder,
                ) use (
                    $search,
                ): void {
                    $builder
                        ->where(
                            'name',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'description',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        $categories = $query
            ->orderBy(
                'sort_order',
            )
            ->orderBy(
                'name',
            )
            ->get();

        return view(
            'livewire.admin.news.news-category-index',
            [
                'categories' => $categories,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'News Categories',
            ],
        );
    }

    private function resetForm(): void
    {
        $this->editingCategoryId =
            null;

        $this->name =
            '';

        $this->description =
            '';

        $this->sortOrder =
            0;

        $this->isActive =
            true;

        $this->showForm =
            false;

        $this->resetValidation();
    }

    private function normaliseForm(): void
    {
        $this->name = trim(
            $this->name,
        );

        $this->description = trim(
            $this->description,
        );

        $this->sortOrder = max(
            0,
            min(
                65535,
                $this->sortOrder,
            ),
        );
    }

    private function actor(): User
    {
        $actor =
            Auth::user();

        if (! $actor instanceof User) {
            throw ValidationException::withMessages([
                'authorization' => 'An authenticated administrator is required.',
            ]);
        }

        return $actor;
    }
}

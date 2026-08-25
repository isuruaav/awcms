<?php

namespace App\Livewire\Admin\Documents;

use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\DocumentCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class DocumentCategoryIndex extends Component
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
            'documents.categories.manage',
        );
    }

    public function openCreate(): void
    {
        Gate::authorize(
            'documents.categories.manage',
        );

        $this->resetForm();

        $this->showForm = true;
    }

    public function openEdit(
        int $categoryId,
    ): void {
        Gate::authorize(
            'documents.categories.manage',
        );

        $category =
            DocumentCategory::query()
                ->findOrFail(
                    $categoryId,
                );

        $this->editingCategoryId =
            (int) $category->getKey();

        $this->name =
            $this->stringValue(
                $category->getAttribute(
                    'name',
                ),
            );

        $this->description =
            $this->stringValue(
                $category->getAttribute(
                    'description',
                ),
            );

        $sortOrder =
            $category->getAttribute(
                'sort_order',
            );

        $this->sortOrder =
            is_numeric($sortOrder)
                ? (int) $sortOrder
                : 0;

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
            'documents.categories.manage',
        );

        $this->normaliseForm();

        $this->validate();

        $service =
            app(
                DocumentCategoryService::class,
            );

        if ($this->editingCategoryId === null) {
            $service->create(
                actor: $this->actor(),

                name: $this->name,

                description: $this->nullableString(
                    $this->description,
                ),

                sortOrder: $this->sortOrder,

                isActive: $this->isActive,
            );

            session()->flash(
                'status',
                'Document category was created successfully.',
            );

            $this->resetForm();

            return;
        }

        $category =
            DocumentCategory::query()
                ->findOrFail(
                    $this->editingCategoryId,
                );

        $service->update(
            category: $category,

            actor: $this->actor(),

            name: $this->name,

            description: $this->nullableString(
                $this->description,
            ),

            sortOrder: $this->sortOrder,

            isActive: $this->isActive,
        );

        session()->flash(
            'status',
            'Document category was updated successfully.',
        );

        $this->resetForm();
    }

    public function toggleActive(
        int $categoryId,
    ): void {
        Gate::authorize(
            'documents.categories.manage',
        );

        $category =
            DocumentCategory::query()
                ->findOrFail(
                    $categoryId,
                );

        $currentStatus =
            (bool) $category->getAttribute(
                'is_active',
            );

        $updated =
            app(
                DocumentCategoryService::class,
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
                ? 'Document category was deactivated successfully.'
                : 'Document category was activated successfully.',
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
        $query =
            DocumentCategory::query()
                ->withCount(
                    'documents',
                );

        $search =
            trim(
                $this->search,
            );

        if ($search !== '') {
            $query->where(
                /**
                 * @param  Builder<DocumentCategory>  $builder
                 */
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

        $categories =
            $query
                ->orderBy(
                    'sort_order',
                )
                ->orderBy(
                    'name',
                )
                ->get();

        return view(
            'livewire.admin.documents.document-category-index',
            [
                'categories' => $categories,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Document Categories',
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
        $this->name =
            trim(
                $this->name,
            );

        $this->description =
            trim(
                $this->description,
            );

        $this->sortOrder =
            max(
                0,
                min(
                    65535,
                    $this->sortOrder,
                ),
            );
    }

    private function nullableString(
        string $value,
    ): ?string {
        $value =
            trim(
                $value,
            );

        return $value !== ''
            ? $value
            : null;
    }

    private function stringValue(
        mixed $value,
    ): string {
        return is_string($value)
            ? $value
            : '';
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

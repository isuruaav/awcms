<?php

namespace App\Livewire\Admin\News;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

final class NewsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $category = '';

    public function mount(): void
    {
        Gate::authorize(
            'news.view',
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';

        $this->status = '';

        $this->category = '';

        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize(
            'news.view',
        );

        $search = trim(
            $this->search,
        );

        $query = News::query()
            ->with([
                'category',
                'creator',
                'featuredImage',
            ]);

        if ($search !== '') {
            $query->where(
                function (
                    Builder $builder,
                ) use (
                    $search,
                ): void {
                    $builder
                        ->where(
                            'title',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            '%'.$search.'%',
                        )
                        ->orWhere(
                            'summary',
                            'like',
                            '%'.$search.'%',
                        );
                },
            );
        }

        $status = NewsStatus::tryFrom(
            $this->status,
        );

        if ($status instanceof NewsStatus) {
            $query->where(
                'status',
                $status->value,
            );
        }

        if (
            $this->category !== ''
            && ctype_digit(
                $this->category,
            )
        ) {
            $query->where(
                'category_id',
                (int) $this->category,
            );
        }

        $news = $query
            ->orderByDesc(
                'updated_at',
            )
            ->orderByDesc(
                'id',
            )
            ->paginate(
                20,
            );

        $categories = NewsCategory::query()
            ->active()
            ->ordered()
            ->get();

        return view(
            'livewire.admin.news.news-index',
            [
                'news' => $news,

                'categories' => $categories,

                'statuses' => NewsStatus::cases(),

                'canCreate' => Gate::allows(
                    'news.create',
                ),

                'canUpdate' => Gate::allows(
                    'news.update',
                ),

                'canManageCategories' => Gate::allows(
                    'news.categories.manage',
                ),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'News',
            ],
        );
    }
}

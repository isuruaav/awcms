<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Services\PageWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class PageIndex extends Component
{
    use WithPagination;

    /**
     * @var list<string>
     */
    private const SORTABLE_FIELDS = [
        'title',
        'status',
        'updated_at',
        'published_at',
        'created_at',
    ];

    /**
     * @var list<int>
     */
    private const PER_PAGE_OPTIONS = [
        10,
        15,
        25,
        50,
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(as: 'sort', except: 'updated_at')]
    public string $sortField = 'updated_at';

    #[Url(as: 'direction', except: 'desc')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public function mount(): void
    {
        Gate::authorize('pages.view');

        $this->normaliseQueryParameters();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->normaliseStatus();
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->normalisePerPage();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->sortField = 'updated_at';
        $this->sortDirection = 'desc';
        $this->perPage = 15;

        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';

            $this->resetPage();

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';

        $this->resetPage();
    }

    public function submitPage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        app(PageWorkflowService::class)->submit(
            $page,
            $this->actor(),
        );

        session()->flash(
            'status',
            "{$page->title} was submitted for review.",
        );
    }

    public function approvePage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        app(PageWorkflowService::class)->approve(
            $page,
            $this->actor(),
        );

        session()->flash(
            'status',
            "{$page->title} was approved.",
        );
    }

    public function publishPage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        app(PageWorkflowService::class)->publish(
            $page,
            $this->actor(),
        );

        session()->flash(
            'status',
            "{$page->title} was published.",
        );
    }

    public function archivePage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        app(PageWorkflowService::class)->archive(
            $page,
            $this->actor(),
        );

        session()->flash(
            'status',
            "{$page->title} was archived.",
        );
    }

    public function returnPageToDraft(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        app(PageWorkflowService::class)->returnToDraft(
            $page,
            $this->actor(),
        );

        session()->flash(
            'status',
            "{$page->title} was returned to Draft.",
        );
    }

    public function render(): View
    {
        $this->normaliseQueryParameters();

        $query = Page::query()
            ->with([
                'creator:id,name',
                'updater:id,name',
                'approver:id,name',
            ]);

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(
                function (Builder $query) use ($search): void {
                    $query
                        ->where(
                            'title',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'excerpt',
                            'like',
                            "%{$search}%",
                        );
                },
            );
        }

        if ($this->status !== 'all') {
            $query->where(
                'status',
                $this->status,
            );
        }

        $pages = $query
            ->orderBy(
                $this->normalisedSortField(),
                $this->normalisedSortDirection(),
            )
            ->paginate($this->perPage);

        $statuses = PageStatus::cases();

        return view(
            'livewire.admin.pages.page-index',
            compact(
                'pages',
                'statuses',
            ),
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Pages',
            ],
        );
    }

    private function normaliseQueryParameters(): void
    {
        $this->sortField = $this->normalisedSortField();
        $this->sortDirection = $this->normalisedSortDirection();

        $this->normaliseStatus();
        $this->normalisePerPage();
    }

    private function normaliseStatus(): void
    {
        if ($this->status === 'all') {
            return;
        }

        $validStatuses = array_map(
            static fn (PageStatus $status): string => $status->value,
            PageStatus::cases(),
        );

        if (! in_array($this->status, $validStatuses, true)) {
            $this->status = 'all';
        }
    }

    private function normalisePerPage(): void
    {
        if (
            ! in_array(
                $this->perPage,
                self::PER_PAGE_OPTIONS,
                true,
            )
        ) {
            $this->perPage = 15;
        }
    }

    private function normalisedSortField(): string
    {
        return in_array(
            $this->sortField,
            self::SORTABLE_FIELDS,
            true,
        )
            ? $this->sortField
            : 'updated_at';
    }

    /**
     * @return 'asc'|'desc'
     */
    private function normalisedSortDirection(): string
    {
        return $this->sortDirection === 'asc'
            ? 'asc'
            : 'desc';
    }

    private function actor(): User
    {
        $actor = Auth::user();

        abort_unless(
            $actor instanceof User,
            403,
        );

        return $actor;
    }
}

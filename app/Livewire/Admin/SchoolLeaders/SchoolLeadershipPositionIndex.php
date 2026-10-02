<?php

namespace App\Livewire\Admin\SchoolLeaders;

use App\Enums\SchoolLeadershipRankGroup;
use App\Models\SchoolLeadershipPosition;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class SchoolLeadershipPositionIndex extends Component
{
    use WithPagination;

    #[Locked]
    public ?int $editingId = null;

    public string $nameEn = '';

    public string $nameSi = '';

    public string $rankGroup = '';

    public int $sortOrder = 100;

    public bool $showOnHome = false;

    public bool $isActive = true;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $homepageFilter = 'all';

    public string $rankGroupFilter = 'all';

    public int $perPage = 10;

    public string $sortField = 'sort_order';

    public string $sortDirection = 'asc';

    public function mount(): void
    {
        Gate::authorize('school-leaders.view');

        $this->resetForm();
    }

    public function startCreate(): void
    {
        Gate::authorize('school-leaders.update');

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        Gate::authorize('school-leaders.view');

        $position = SchoolLeadershipPosition::query()
            ->findOrFail($id);

        $this->editingId = $position->id;
        $this->nameEn = $position->name_en;
        $this->nameSi = $position->name_si;
        $this->rankGroup = (string) $position->getRawOriginal(
            'rank_group',
        );
        $this->sortOrder = $position->sort_order;
        $this->showOnHome = $position->show_on_home;
        $this->isActive = $position->is_active;

        $this->resetValidation();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        if (! in_array(
            $this->statusFilter,
            [
                'all',
                'active',
                'inactive',
            ],
            true,
        )) {
            $this->statusFilter = 'all';
        }

        $this->resetPage();
    }

    public function updatedHomepageFilter(): void
    {
        if (! in_array(
            $this->homepageFilter,
            [
                'all',
                'yes',
                'no',
            ],
            true,
        )) {
            $this->homepageFilter = 'all';
        }

        $this->resetPage();
    }

    public function updatedRankGroupFilter(): void
    {
        if (
            $this->rankGroupFilter !== 'all'
            && SchoolLeadershipRankGroup::tryFrom(
                $this->rankGroupFilter,
            ) === null
        ) {
            $this->rankGroupFilter = 'all';
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array(
            $this->perPage,
            [
                10,
                25,
                50,
            ],
            true,
        )) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array(
            $field,
            [
                'appointment',
                'rank_group',
                'leaders',
                'homepage',
                'status',
                'sort_order',
            ],
            true,
        )) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetTableFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->homepageFilter = 'all';
        $this->rankGroupFilter = 'all';

        $this->resetPage();
    }

    public function save(): void
    {
        Gate::authorize('school-leaders.update');

        $validated = $this->validate([
            'nameEn' => [
                'required',
                'string',
                'max:180',
            ],
            'nameSi' => [
                'required',
                'string',
                'max:220',
            ],
            'rankGroup' => [
                'required',
                'string',
                Rule::enum(SchoolLeadershipRankGroup::class),
            ],
            'sortOrder' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
            ],
            'showOnHome' => [
                'boolean',
            ],
            'isActive' => [
                'boolean',
            ],
        ]);

        $actor = $this->actor();
        $creating = $this->editingId === null;

        $position = DB::transaction(function () use (
            $validated,
        ): SchoolLeadershipPosition {
            $position = $this->editingId === null
                ? new SchoolLeadershipPosition
                : SchoolLeadershipPosition::query()
                    ->findOrFail($this->editingId);

            if (! $position->exists) {
                $position->key = $this->generateUniqueKey(
                    $validated['nameEn'],
                );
            }

            $position->fill([
                'name_en' => trim($validated['nameEn']),
                'name_si' => trim($validated['nameSi']),
                'rank_group' => $validated['rankGroup'],
                'sort_order' => $validated['sortOrder'],
                'show_on_home' => $validated['showOnHome'],
                'is_active' => $validated['isActive'],
            ]);

            $position->save();

            return $position;
        });

        app(AuditLogger::class)->log(
            event: $creating
                ? 'settings.school-leadership-position-created'
                : 'settings.school-leadership-position-updated',
            description: $creating
                ? 'A School Leadership appointment type was created.'
                : 'A School Leadership appointment type was updated.',
            actor: $actor,
            subject: $position,
        );

        session()->flash(
            'status',
            $creating
                ? 'Appointment type created successfully.'
                : 'Appointment type updated successfully.',
        );

        $this->edit($position->id);
    }

    public function render(): View
    {
        Gate::authorize('school-leaders.view');

        $positionsQuery = SchoolLeadershipPosition::query()
            ->withCount('leaders');

        $search = trim(
            $this->search,
        );

        if ($search !== '') {
            $like = '%'.$search.'%';

            $positionsQuery->where(
                function (Builder $query) use ($like): void {
                    $query
                        ->where(
                            'name_en',
                            'like',
                            $like,
                        )
                        ->orWhere(
                            'name_si',
                            'like',
                            $like,
                        )
                        ->orWhere(
                            'key',
                            'like',
                            $like,
                        );
                },
            );
        }

        if ($this->statusFilter === 'active') {
            $positionsQuery->where(
                'is_active',
                true,
            );
        } elseif ($this->statusFilter === 'inactive') {
            $positionsQuery->where(
                'is_active',
                false,
            );
        }

        if ($this->homepageFilter === 'yes') {
            $positionsQuery->where(
                'show_on_home',
                true,
            );
        } elseif ($this->homepageFilter === 'no') {
            $positionsQuery->where(
                'show_on_home',
                false,
            );
        }

        if (
            $this->rankGroupFilter !== 'all'
            && SchoolLeadershipRankGroup::tryFrom(
                $this->rankGroupFilter,
            ) instanceof SchoolLeadershipRankGroup
        ) {
            $positionsQuery->where(
                'rank_group',
                $this->rankGroupFilter,
            );
        }

        $sortDirection = $this->sortDirection === 'desc'
            ? 'desc'
            : 'asc';

        match ($this->sortField) {
            'appointment' => $positionsQuery->orderBy(
                'name_en',
                $sortDirection,
            ),
            'rank_group' => $positionsQuery->orderBy(
                'rank_group',
                $sortDirection,
            ),
            'leaders' => $positionsQuery->orderBy(
                'leaders_count',
                $sortDirection,
            ),
            'homepage' => $positionsQuery->orderBy(
                'show_on_home',
                $sortDirection,
            ),
            'status' => $positionsQuery->orderBy(
                'is_active',
                $sortDirection,
            ),
            default => $positionsQuery->orderBy(
                'sort_order',
                $sortDirection,
            ),
        };

        if ($this->sortField !== 'sort_order') {
            $positionsQuery->orderBy(
                'sort_order',
            );
        }

        $positionsQuery->orderBy('id');

        if (! in_array(
            $this->perPage,
            [
                10,
                25,
                50,
            ],
            true,
        )) {
            $this->perPage = 10;
        }

        /** @var view-string $viewName */
        $viewName = 'livewire.admin.school-leaders.school-leadership-position-index';

        return view(
            $viewName,
            [
                'positions' => $positionsQuery->paginate(
                    $this->perPage,
                ),
                'rankGroups' => SchoolLeadershipRankGroup::cases(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Leadership Appointment Types',
            ],
        );
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nameEn = '';
        $this->nameSi = '';
        $this->rankGroup = '';
        $this->sortOrder = 100;
        $this->showOnHome = false;
        $this->isActive = true;

        $this->resetValidation();
    }

    private function generateUniqueKey(string $name): string
    {
        $base = str_replace(
            '-',
            '_',
            Str::slug($name),
        );

        if ($base === '') {
            $base = 'position';
        }

        $key = $base;
        $number = 2;

        while (
            SchoolLeadershipPosition::query()
                ->where('key', $key)
                ->exists()
        ) {
            $key = $base.'_'.$number;
            $number++;
        }

        return $key;
    }

    private function actor(): User
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof User,
            403,
        );

        return $user;
    }
}

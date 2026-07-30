<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Models\AuditLog;
use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
final class AuditLogIndex extends Component
{
    use WithPagination;

    /**
     * @var list<int>
     */
    private const PER_PAGE_OPTIONS = [
        25,
        50,
        100,
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $event = 'all';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    public int $perPage = 25;

    #[Locked]
    public ?int $selectedLogId = null;

    public function mount(): void
    {
        Gate::authorize('audit.view');

        $this->normalisePerPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
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
        $this->event = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->perPage = 25;
        $this->selectedLogId = null;

        $this->resetPage();
    }

    public function viewLog(int $auditLogId): void
    {
        Gate::authorize('audit.view');

        AuditLog::query()->findOrFail($auditLogId);

        $this->selectedLogId = $auditLogId;
    }

    public function closeDetails(): void
    {
        $this->selectedLogId = null;
    }

    public function render(): View
    {
        $this->normalisePerPage();

        $query = AuditLog::query();

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(
                function (Builder $query) use ($search): void {
                    $query
                        ->where('event', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('actor_name', 'like', "%{$search}%")
                        ->orWhere('actor_email', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%")
                        ->orWhere('request_path', 'like', "%{$search}%");
                },
            );
        }

        if ($this->event !== 'all') {
            $query->where('event', $this->event);
        }

        $dateFrom = $this->normalisedDate($this->dateFrom);
        $dateTo = $this->normalisedDate($this->dateTo);

        if ($dateFrom !== null) {
            $query->whereDate(
                'created_at',
                '>=',
                $dateFrom,
            );
        }

        if ($dateTo !== null) {
            $query->whereDate(
                'created_at',
                '<=',
                $dateTo,
            );
        }

        $logs = $query
            ->latest('id')
            ->paginate($this->perPage);

        $events = AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->map(
                static fn (mixed $event): string => (string) $event,
            )
            ->values();

        $selectedLog = $this->selectedLogId === null
            ? null
            : AuditLog::query()
                ->find($this->selectedLogId);

        return view(
            'livewire.admin.audit-logs.audit-log-index',
            compact(
                'logs',
                'events',
                'selectedLog',
            ),
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Audit Logs',
            ],
        );
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
            $this->perPage = 25;
        }
    }

    private function normalisedDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value,
        );

        if (
            $date === false
            || $date->format('Y-m-d') !== $value
        ) {
            return null;
        }

        return $value;
    }
}

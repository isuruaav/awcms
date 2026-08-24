<?php

namespace App\Livewire\Admin\News;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsRevision;
use App\Models\User;
use App\Services\NewsRevisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class NewsRevisionHistory extends Component
{
    use WithPagination;

    #[Locked]
    public int $newsId;

    public ?int $selectedRevisionId = null;

    public function mount(
        News $news,
    ): void {
        Gate::authorize(
            'news.view',
        );

        if ($news->trashed()) {
            abort(
                404,
            );
        }

        $this->newsId =
            (int) $news->getKey();
    }

    public function selectRevision(
        int $revisionId,
    ): void {
        Gate::authorize(
            'news.view',
        );

        $revision = NewsRevision::query()
            ->where(
                'news_id',
                $this->newsId,
            )
            ->findOrFail(
                $revisionId,
            );

        $this->selectedRevisionId =
            (int) $revision->getKey();

        $this->resetValidation();
    }

    public function clearSelection(): void
    {
        $this->selectedRevisionId =
            null;

        $this->resetValidation();
    }

    public function restoreSelectedRevision(): void
    {
        Gate::authorize(
            'news.update',
        );

        if ($this->selectedRevisionId === null) {
            throw ValidationException::withMessages([
                'revision' => 'Please select a revision to restore.',
            ]);
        }

        $news =
            $this->news();

        $revision = NewsRevision::query()
            ->where(
                'news_id',
                $news->id,
            )
            ->findOrFail(
                $this->selectedRevisionId,
            );

        app(
            NewsRevisionService::class,
        )->restore(
            news: $news,
            revision: $revision,
            actor: $this->actor(),
        );

        session()->flash(
            'status',
            sprintf(
                'Revision %d was restored successfully. The previous current version was saved as a backup revision.',
                (int) $revision->revision_number,
            ),
        );

        $this->redirectRoute(
            'admin.news.edit',
            [
                'news' => $news->id,
            ],
            navigate: true,
        );
    }

    public function render(): View
    {
        Gate::authorize(
            'news.view',
        );

        $news =
            $this->news();

        $status =
            $news->getAttribute(
                'status',
            );

        $revisions = NewsRevision::query()
            ->with([
                'creator',
                'category',
            ])
            ->where(
                'news_id',
                $news->id,
            )
            ->orderByDesc(
                'revision_number',
            )
            ->paginate(
                15,
            );

        $selectedRevision =
            $this->selectedRevision();

        return view(
            'livewire.admin.news.news-revision-history',
            [
                'news' => $news,

                'revisions' => $revisions,

                'selectedRevision' => $selectedRevision,

                'canRestore' => Gate::allows(
                    'news.update',
                )
                    && $status instanceof NewsStatus
                    && $status->isEditable(),
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'News Revision History',
            ],
        );
    }

    private function news(): News
    {
        return News::query()
            ->with([
                'category',
                'creator',
            ])
            ->findOrFail(
                $this->newsId,
            );
    }

    private function selectedRevision(): ?NewsRevision
    {
        if ($this->selectedRevisionId === null) {
            return null;
        }

        return NewsRevision::query()
            ->with([
                'creator',
                'category',
                'featuredImage',
            ])
            ->where(
                'news_id',
                $this->newsId,
            )
            ->findOrFail(
                $this->selectedRevisionId,
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

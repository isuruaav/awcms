<?php

namespace App\Livewire\Admin\News;

use App\Models\News;
use App\Models\NewsRevision;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
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
    }

    public function clearSelection(): void
    {
        $this->selectedRevisionId =
            null;
    }

    public function render(): View
    {
        Gate::authorize(
            'news.view',
        );

        $news = News::query()
            ->with([
                'category',
                'creator',
            ])
            ->findOrFail(
                $this->newsId,
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
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'News Revision History',
            ],
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
}

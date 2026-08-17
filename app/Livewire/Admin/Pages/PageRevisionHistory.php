<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Services\ContentSanitizer;
use App\Services\PageRevisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PageRevisionHistory extends Component
{
    #[Locked]
    public int $pageId;

    public ?int $selectedRevisionId = null;

    public function mount(Page $page): void
    {
        Gate::authorize(
            'pages.revisions.view',
        );

        $this->pageId = (int) $page->id;

        $latestRevision = $page
            ->revisions()
            ->first();

        if ($latestRevision instanceof PageRevision) {
            $this->selectedRevisionId =
                (int) $latestRevision->id;
        }
    }

    public function selectRevision(
        int $revisionId,
    ): void {
        Gate::authorize(
            'pages.revisions.view',
        );

        $revision = $this->revision(
            $revisionId,
        );

        $this->selectedRevisionId =
            (int) $revision->id;
    }

    public function restoreRevision(
        int $revisionId,
    ): void {
        Gate::authorize(
            'pages.revisions.restore',
        );

        $revision = $this->revision(
            $revisionId,
        );

        $page = $this->page();

        app(PageRevisionService::class)
            ->restore(
                $revision,
                $this->actor(),
            );

        $latestRevision = $page
            ->revisions()
            ->first();

        $this->selectedRevisionId =
            $latestRevision instanceof PageRevision
            ? (int) $latestRevision->id
            : null;

        session()->flash(
            'status',
            sprintf(
                'Revision #%d was restored successfully.',
                (int) $revision->revision_number,
            ),
        );
    }

    public function render(): View
    {
        Gate::authorize(
            'pages.revisions.view',
        );

        $page = $this->page();

        $revisions = $page
            ->revisions()
            ->with([
                'creator:id,name',
            ])
            ->get();

        $selectedRevision = null;

        if ($this->selectedRevisionId !== null) {
            $selectedRevision = $revisions
                ->firstWhere(
                    'id',
                    $this->selectedRevisionId,
                );
        }

        if (
            ! $selectedRevision instanceof PageRevision
            && $revisions->isNotEmpty()
        ) {
            $selectedRevision = $revisions->first();

            $this->selectedRevisionId =
                (int) $selectedRevision->id;
        }

        $sanitizer = app(
            ContentSanitizer::class,
        );

        $currentContent = $sanitizer->sanitize(
            is_string($page->content)
                ? $page->content
                : null,
        );

        $revisionContent = '';

        if ($selectedRevision instanceof PageRevision) {
            $revisionContent = $sanitizer->sanitize(
                is_string(
                    $selectedRevision->content,
                )
                    ? $selectedRevision->content
                    : null,
            );
        }

        return view(
            'livewire.admin.pages.page-revision-history',
            [
                'page' => $page,
                'revisions' => $revisions,
                'selectedRevision' => $selectedRevision,

                'currentContent' => $currentContent,

                'revisionContent' => $revisionContent,
            ],
        )->layout(
            'components.layouts.admin',
            [
                'title' => 'Page Revision History',
            ],
        );
    }

    private function page(): Page
    {
        return Page::query()
            ->findOrFail(
                $this->pageId,
            );
    }

    private function revision(
        int $revisionId,
    ): PageRevision {
        return PageRevision::query()
            ->where(
                'page_id',
                $this->pageId,
            )
            ->findOrFail(
                $revisionId,
            );
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

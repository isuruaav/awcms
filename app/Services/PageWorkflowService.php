<?php

namespace App\Services;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class PageWorkflowService
{
    public function submit(Page $page, User $actor): Page
    {
        return $this->transition(
            page: $page,
            actor: $actor,
            targetStatus: PageStatus::Submitted,
        );
    }

    public function approve(Page $page, User $actor): Page
    {
        return $this->transition(
            page: $page,
            actor: $actor,
            targetStatus: PageStatus::Approved,
        );
    }

    public function publish(Page $page, User $actor): Page
    {
        return $this->transition(
            page: $page,
            actor: $actor,
            targetStatus: PageStatus::Published,
        );
    }

    public function publishImmediately(Page $page, User $actor): Page
    {
        Gate::forUser($actor)->authorize(
            'pages.publish',
        );

        $pageId = (int) $page->getKey();

        return DB::transaction(
            function () use (
                $pageId,
                $actor,
            ): Page {
                $lockedPage = Page::query()
                    ->lockForUpdate()
                    ->findOrFail($pageId);

                $currentStatus = $this->statusOf(
                    $lockedPage,
                );

                if ($currentStatus === PageStatus::Published) {
                    return $lockedPage;
                }

                $lockedPage->forceFill([
                    'status' => PageStatus::Published->value,
                    'published_at' => now(),
                    'archived_at' => null,
                    'updated_by' => $actor->id,
                ])->save();

                app(AuditLogger::class)->log(
                    event: 'pages.published',
                    description: 'Website page published.',
                    actor: $actor,
                    subject: $lockedPage,
                    oldValues: [
                        'status' => $currentStatus->value,
                    ],
                    newValues: [
                        'status' => PageStatus::Published->value,
                    ],
                );

                app(PageRevisionService::class)->capture(
                    page: $lockedPage,
                    actor: $actor,
                    summary: sprintf(
                        'Page published from %s.',
                        $currentStatus->label(),
                    ),
                );

                return $lockedPage->refresh();
            },
        );
    }

    public function unpublish(Page $page, User $actor): Page
    {
        Gate::forUser($actor)->authorize(
            'pages.publish',
        );

        $pageId = (int) $page->getKey();

        return DB::transaction(
            function () use (
                $pageId,
                $actor,
            ): Page {
                $lockedPage = Page::query()
                    ->lockForUpdate()
                    ->findOrFail($pageId);

                $currentStatus = $this->statusOf(
                    $lockedPage,
                );

                if ($currentStatus !== PageStatus::Published) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only a published page may be unpublished.',
                    ]);
                }

                $lockedPage->forceFill([
                    'status' => PageStatus::Draft->value,
                    'submitted_at' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                    'published_at' => null,
                    'archived_at' => null,
                    'updated_by' => $actor->id,
                ])->save();

                app(AuditLogger::class)->log(
                    event: 'pages.unpublished',
                    description: 'Website page unpublished and returned to draft status.',
                    actor: $actor,
                    subject: $lockedPage,
                    oldValues: [
                        'status' => PageStatus::Published->value,
                    ],
                    newValues: [
                        'status' => PageStatus::Draft->value,
                    ],
                );

                app(PageRevisionService::class)->capture(
                    page: $lockedPage,
                    actor: $actor,
                    summary: 'Page unpublished and returned to Draft.',
                );

                return $lockedPage->refresh();
            },
        );
    }

    public function archive(Page $page, User $actor): Page
    {
        return $this->transition(
            page: $page,
            actor: $actor,
            targetStatus: PageStatus::Archived,
        );
    }

    public function returnToDraft(Page $page, User $actor): Page
    {
        return $this->transition(
            page: $page,
            actor: $actor,
            targetStatus: PageStatus::Draft,
        );
    }

    private function transition(
        Page $page,
        User $actor,
        PageStatus $targetStatus,
    ): Page {
        $pageId = (int) $page->getKey();

        return DB::transaction(
            function () use (
                $pageId,
                $actor,
                $targetStatus,
            ): Page {
                $lockedPage = Page::query()
                    ->lockForUpdate()
                    ->findOrFail($pageId);

                $currentStatus = $this->statusOf(
                    $lockedPage,
                );

                $permission = $this->requiredPermission(
                    $currentStatus,
                    $targetStatus,
                );

                Gate::forUser($actor)->authorize(
                    $permission,
                );

                if (
                    ! $currentStatus->canTransitionTo(
                        $targetStatus,
                    )
                ) {
                    throw ValidationException::withMessages([
                        'workflow' => sprintf(
                            'The page cannot move from %s to %s.',
                            $currentStatus->label(),
                            $targetStatus->label(),
                        ),
                    ]);
                }

                $attributes = array_merge(
                    [
                        'status' => $targetStatus->value,
                        'updated_by' => $actor->id,
                    ],
                    $this->workflowAttributes(
                        $targetStatus,
                        $actor,
                    ),
                );

                $lockedPage
                    ->forceFill($attributes)
                    ->save();

                app(AuditLogger::class)->log(
                    event: $this->auditEvent(
                        $targetStatus,
                    ),
                    description: $this->auditDescription(
                        $targetStatus,
                    ),
                    actor: $actor,
                    subject: $lockedPage,
                    oldValues: [
                        'status' => $currentStatus->value,
                    ],
                    newValues: [
                        'status' => $targetStatus->value,
                    ],
                );

                app(PageRevisionService::class)->capture(
                    page: $lockedPage,
                    actor: $actor,
                    summary: sprintf(
                        'Workflow changed from %s to %s.',
                        $currentStatus->label(),
                        $targetStatus->label(),
                    ),
                );

                return $lockedPage->refresh();
            },
        );
    }

    private function statusOf(Page $page): PageStatus
    {
        $status = $page->getRawOriginal('status');

        if (! is_string($status)) {
            throw ValidationException::withMessages([
                'workflow' => 'The page workflow status is invalid.',
            ]);
        }

        $pageStatus = PageStatus::tryFrom($status);

        if (! $pageStatus instanceof PageStatus) {
            throw ValidationException::withMessages([
                'workflow' => 'The page workflow status is invalid.',
            ]);
        }

        return $pageStatus;
    }

    private function requiredPermission(
        PageStatus $currentStatus,
        PageStatus $targetStatus,
    ): string {
        return match ($targetStatus) {
            PageStatus::Submitted => 'pages.submit',
            PageStatus::Approved => 'pages.approve',
            PageStatus::Published => 'pages.publish',
            PageStatus::Archived => 'pages.archive',

            PageStatus::Draft => match ($currentStatus) {
                PageStatus::Submitted,
                PageStatus::Approved => 'pages.approve',

                PageStatus::Published => 'pages.publish',
                PageStatus::Archived => 'pages.archive',
                PageStatus::Draft => 'pages.update',
            },
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowAttributes(
        PageStatus $targetStatus,
        User $actor,
    ): array {
        return match ($targetStatus) {
            PageStatus::Draft => [
                'submitted_at' => null,
                'approved_at' => null,
                'approved_by' => null,
                'published_at' => null,
                'archived_at' => null,
            ],

            PageStatus::Submitted => [
                'submitted_at' => now(),
                'approved_at' => null,
                'approved_by' => null,
                'published_at' => null,
                'archived_at' => null,
            ],

            PageStatus::Approved => [
                'approved_at' => now(),
                'approved_by' => $actor->id,
                'published_at' => null,
                'archived_at' => null,
            ],

            PageStatus::Published => [
                'published_at' => now(),
                'archived_at' => null,
            ],

            PageStatus::Archived => [
                'archived_at' => now(),
            ],
        };
    }

    private function auditEvent(
        PageStatus $targetStatus,
    ): string {
        return match ($targetStatus) {
            PageStatus::Draft => 'pages.returned-to-draft',
            PageStatus::Submitted => 'pages.submitted',
            PageStatus::Approved => 'pages.approved',
            PageStatus::Published => 'pages.published',
            PageStatus::Archived => 'pages.archived',
        };
    }

    private function auditDescription(
        PageStatus $targetStatus,
    ): string {
        return match ($targetStatus) {
            PageStatus::Draft => 'Website page returned to draft status.',

            PageStatus::Submitted => 'Website page submitted for review.',

            PageStatus::Approved => 'Website page approved for publication.',

            PageStatus::Published => 'Website page published.',

            PageStatus::Archived => 'Website page archived.',
        };
    }
}

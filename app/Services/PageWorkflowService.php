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

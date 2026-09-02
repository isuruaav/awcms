<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class NewsWorkflowService
{
    public function __construct(
        private readonly NewsRevisionService $revisionService,
    ) {}

    public function submit(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.submit',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news =
                    $this->lockedNews(
                        $news,
                    );

                $status =
                    $this->status(
                        $news,
                    );

                if (
                    ! in_array(
                        $status,
                        [
                            NewsStatus::Draft,
                            NewsStatus::ChangesRequested,
                        ],
                        true,
                    )
                ) {
                    $this->invalidTransition(
                        'Only Draft or Changes Requested news can be submitted for review.',
                    );
                }

                $this->revisionService
                    ->createSnapshot(
                        news: $news,
                        actor: $actor,
                        reason: 'Snapshot before submission for review.',
                    );

                $news->forceFill([
                    'status' => NewsStatus::Submitted->value,

                    'submitted_at' => now(),

                    'submitted_by' => $actor->id,

                    'changes_requested_at' => null,

                    'changes_requested_by' => null,

                    'change_request_note' => null,

                    'approved_at' => null,

                    'approved_by' => null,

                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.submitted',
                    description: 'A news article was submitted for review.',
                    actor: $actor,
                    news: $news,
                    oldStatus: $status,
                    newStatus: NewsStatus::Submitted,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function requestChanges(
        News $news,
        User $actor,
        string $note,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.request-changes',
        );

        $note =
            trim(
                $note,
            );

        if ($note === '') {
            throw ValidationException::withMessages([
                'changeRequestNote' => 'Please explain the changes required.',
            ]);
        }

        if (mb_strlen($note) > 1000) {
            throw ValidationException::withMessages([
                'changeRequestNote' => 'The change request note may not exceed 1000 characters.',
            ]);
        }

        return DB::transaction(
            function () use (
                $news,
                $actor,
                $note,
            ): News {
                $news =
                    $this->lockedNews(
                        $news,
                    );

                $status =
                    $this->status(
                        $news,
                    );

                if (
                    $status !==
                    NewsStatus::Submitted
                ) {
                    $this->invalidTransition(
                        'Only Submitted news can have changes requested.',
                    );
                }

                $news->forceFill([
                    'status' => NewsStatus::ChangesRequested->value,

                    'changes_requested_at' => now(),

                    'changes_requested_by' => $actor->id,

                    'change_request_note' => $note,

                    'approved_at' => null,

                    'approved_by' => null,

                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.changes-requested',

                    description: 'Changes were requested for a news article.',

                    actor: $actor,

                    news: $news,

                    oldStatus: $status,

                    newStatus: NewsStatus::ChangesRequested,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function approve(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.approve',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news =
                    $this->lockedNews(
                        $news,
                    );

                $status =
                    $this->status(
                        $news,
                    );

                if (
                    $status !==
                    NewsStatus::Submitted
                ) {
                    $this->invalidTransition(
                        'Only Submitted news can be approved.',
                    );
                }

                $news->forceFill([
                    'status' => NewsStatus::Approved->value,

                    'approved_at' => now(),

                    'approved_by' => $actor->id,

                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.approved',

                    description: 'A news article was approved.',

                    actor: $actor,

                    news: $news,

                    oldStatus: $status,

                    newStatus: NewsStatus::Approved,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function publish(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.publish',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news =
                    $this->lockedNews(
                        $news,
                    );

                $status =
                    $this->status(
                        $news,
                    );

                if (
                    $status !==
                    NewsStatus::Approved
                ) {
                    $this->invalidTransition(
                        'Only Approved news can be published.',
                    );
                }

                $publicationDate =
                    $news->published_at
                        ?? now();

                $news->forceFill([
                    'status' => NewsStatus::Published->value,

                    'published_at' => $publicationDate,

                    'published_by' => $actor->id,

                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.published',

                    description: 'A news article was published.',

                    actor: $actor,

                    news: $news,

                    oldStatus: $status,

                    newStatus: NewsStatus::Published,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function publishImmediately(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.publish',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news = $this->lockedNews(
                    $news,
                );

                $status = $this->status(
                    $news,
                );

                if ($status === NewsStatus::Published) {
                    return $news;
                }

                $this->revisionService->createSnapshot(
                    news: $news,
                    actor: $actor,
                    reason: sprintf(
                        'Snapshot before direct publication from %s.',
                        $status->label(),
                    ),
                );

                $publicationDate = $news->published_at ?? now();

                $news->forceFill([
                    'status' => NewsStatus::Published->value,
                    'published_at' => $publicationDate,
                    'published_by' => $actor->id,
                    'archived_at' => null,
                    'archived_by' => null,
                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.published',
                    description: 'A news article was published.',
                    actor: $actor,
                    news: $news,
                    oldStatus: $status,
                    newStatus: NewsStatus::Published,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function unpublish(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.publish',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news = $this->lockedNews(
                    $news,
                );

                $status = $this->status(
                    $news,
                );

                if ($status !== NewsStatus::Published) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only a published news article may be unpublished.',
                    ]);
                }

                $this->revisionService->createSnapshot(
                    news: $news,
                    actor: $actor,
                    reason: 'Snapshot before unpublishing.',
                );

                $news->forceFill([
                    'status' => NewsStatus::Draft->value,
                    'published_at' => null,
                    'published_by' => null,
                    'submitted_at' => null,
                    'submitted_by' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                    'archived_at' => null,
                    'archived_by' => null,
                    'changes_requested_at' => null,
                    'changes_requested_by' => null,
                    'change_request_note' => null,
                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.unpublished',
                    description: 'A news article was unpublished and returned to Draft.',
                    actor: $actor,
                    news: $news,
                    oldStatus: NewsStatus::Published,
                    newStatus: NewsStatus::Draft,
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function archive(
        News $news,
        User $actor,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.archive',
        );

        return DB::transaction(
            function () use (
                $news,
                $actor,
            ): News {
                $news =
                    $this->lockedNews(
                        $news,
                    );

                $status =
                    $this->status(
                        $news,
                    );

                if (
                    $status !==
                    NewsStatus::Published
                ) {
                    $this->invalidTransition(
                        'Only Published news can be archived.',
                    );
                }

                $news->forceFill([
                    'status' => NewsStatus::Archived->value,

                    'archived_at' => now(),

                    'archived_by' => $actor->id,

                    'updated_by' => $actor->id,
                ])->save();

                $this->audit(
                    event: 'news.archived',

                    description: 'A news article was archived.',

                    actor: $actor,

                    news: $news,

                    oldStatus: $status,

                    newStatus: NewsStatus::Archived,
                );

                return $news->refresh();
            },
            3,
        );
    }

    private function lockedNews(
        News $news,
    ): News {
        if (
            ! $news->exists
            || $news->trashed()
        ) {
            throw ValidationException::withMessages([
                'news' => 'The selected news article is unavailable.',
            ]);
        }

        return News::query()
            ->whereKey(
                $news->getKey(),
            )
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function status(
        News $news,
    ): NewsStatus {
        $status =
            $news->getAttribute(
                'status',
            );

        if (! $status instanceof NewsStatus) {
            throw ValidationException::withMessages([
                'news' => 'The news article has an invalid workflow status.',
            ]);
        }

        return $status;
    }

    private function invalidTransition(
        string $message,
    ): never {
        throw ValidationException::withMessages([
            'workflow' => $message,
        ]);
    }

    private function audit(
        string $event,
        string $description,
        User $actor,
        News $news,
        NewsStatus $oldStatus,
        NewsStatus $newStatus,
    ): void {
        app(
            AuditLogger::class,
        )->log(
            event: $event,

            description: $description,

            actor: $actor,

            subject: $news,

            oldValues: [
                'status' => $oldStatus->value,
            ],

            newValues: [
                'status' => $newStatus->value,
            ],
        );
    }
}

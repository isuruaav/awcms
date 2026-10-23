<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class NewsDeletionService
{
    public function delete(
        News $news,
        User $actor,
    ): void {
        Gate::forUser($actor)->authorize(
            'news.delete',
        );

        DB::transaction(
            function () use (
                $news,
                $actor,
            ): void {
                $lockedNews = News::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $news->getKey());

                $status = $lockedNews->getRawOriginal('status');

                if ($status === NewsStatus::Published->value) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Unpublish the news article before deleting it.',
                    ]);
                }

                app(NewsRevisionService::class)->createSnapshot(
                    news: $lockedNews,
                    actor: $actor,
                    reason: 'Snapshot before moving article to Trash.',
                );

                app(AuditLogger::class)->log(
                    event: 'news.deleted',
                    description: 'News article moved to Trash.',
                    actor: $actor,
                    subject: $lockedNews,
                    oldValues: [
                        'deleted_at' => null,
                    ],
                    newValues: [
                        'deleted_at' => now()->toIso8601String(),
                    ],
                );

                $lockedNews->delete();
            },
            3,
        );
    }

    public function restore(
        int $newsId,
        User $actor,
    ): News {
        Gate::forUser($actor)->authorize(
            'news.delete',
        );

        return DB::transaction(
            function () use (
                $newsId,
                $actor,
            ): News {
                $news = News::withTrashed()
                    ->lockForUpdate()
                    ->findOrFail($newsId);

                if (! $news->trashed()) {
                    return $news;
                }

                $news->restore();

                app(AuditLogger::class)->log(
                    event: 'news.restored',
                    description: 'News article restored from Trash.',
                    actor: $actor,
                    subject: $news,
                    oldValues: [
                        'deleted_at' => 'set',
                    ],
                    newValues: [
                        'deleted_at' => null,
                    ],
                );

                return $news->refresh();
            },
            3,
        );
    }

    public function forceDelete(int $newsId, User $actor): void
    {
        Gate::forUser($actor)->authorize('news.delete');

        DB::transaction(function () use ($newsId, $actor): void {
            $news = News::withTrashed()
                ->lockForUpdate()
                ->findOrFail($newsId);

            if (! $news->trashed()) {
                throw ValidationException::withMessages([
                    'workflow' => 'Only news articles in Trash may be permanently deleted.',
                ]);
            }

            app(AuditLogger::class)->log(
                event: 'news.force_deleted',
                description: 'News article was permanently deleted from Trash.',
                actor: $actor,
                subject: $news,
                oldValues: [
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'locale' => $news->getRawOriginal('locale'),
                    'deleted' => true,
                ],
                newValues: ['permanently_deleted' => true],
            );

            // Related revisions/image links cascade; shared media stays in the library.
            if ($news->forceDelete() !== true) {
                throw new RuntimeException('The news article could not be permanently deleted.');
            }
        }, 3);
    }
}

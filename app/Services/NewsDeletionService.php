<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

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
}

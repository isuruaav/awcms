<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class NewsRevisionService
{
    public function createSnapshot(
        News $news,
        User $actor,
        ?string $reason = null,
    ): NewsRevision {
        if (! $news->exists) {
            throw new RuntimeException(
                'A persisted news article is required to create a revision.',
            );
        }

        if ($news->trashed()) {
            throw new RuntimeException(
                'A deleted news article cannot create a revision.',
            );
        }

        return DB::transaction(
            function () use (
                $news,
                $actor,
                $reason,
            ): NewsRevision {
                /*
                 * Lock the parent News row so two simultaneous
                 * saves cannot receive the same revision number.
                 */
                $lockedNews = News::query()
                    ->whereKey(
                        $news->getKey(),
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $revisionNumber =
                    $this->nextRevisionNumber(
                        $lockedNews,
                    );

                $status =
                    $lockedNews->getAttribute(
                        'status',
                    );

                if (! $status instanceof NewsStatus) {
                    throw new RuntimeException(
                        'News article has an invalid workflow status.',
                    );
                }

                return NewsRevision::query()
                    ->create([
                        'news_id' => $lockedNews->getKey(),

                        'revision_number' => $revisionNumber,

                        'category_id' => $lockedNews->getAttribute(
                            'category_id',
                        ),

                        'title' => (string) $lockedNews->getAttribute(
                            'title',
                        ),

                        'slug' => (string) $lockedNews->getAttribute(
                            'slug',
                        ),

                        'summary' => $this->nullableString(
                            $lockedNews->getAttribute(
                                'summary',
                            ),
                        ),

                        'content' => (string) $lockedNews->getAttribute(
                            'content',
                        ),

                        'featured_image_id' => $lockedNews->getAttribute(
                            'featured_image_id',
                        ),

                        'is_featured' => (bool) $lockedNews->getAttribute(
                            'is_featured',
                        ),

                        'status' => $status,

                        'published_at' => $lockedNews->getAttribute(
                            'published_at',
                        ),

                        'seo_title' => $this->nullableString(
                            $lockedNews->getAttribute(
                                'seo_title',
                            ),
                        ),

                        'seo_description' => $this->nullableString(
                            $lockedNews->getAttribute(
                                'seo_description',
                            ),
                        ),

                        'created_by' => $actor->getKey(),

                        'reason' => $this->normaliseReason(
                            $reason,
                        ),
                    ]);
            },
            3,
        );
    }

    private function nextRevisionNumber(
        News $news,
    ): int {
        $maximum = NewsRevision::query()
            ->where(
                'news_id',
                $news->getKey(),
            )
            ->max(
                'revision_number',
            );

        if (! is_numeric($maximum)) {
            return 1;
        }

        return ((int) $maximum) + 1;
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(
            $value,
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function normaliseReason(
        ?string $reason,
    ): ?string {
        if ($reason === null) {
            return null;
        }

        $reason = trim(
            $reason,
        );

        if ($reason === '') {
            return null;
        }

        return mb_substr(
            $reason,
            0,
            255,
        );
    }
}

<?php

namespace App\Services;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Support\PageSlugger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PageRevisionService
{
    public function capture(
        Page $page,
        ?User $actor,
        string $summary,
        ?int $restoredFromRevisionNumber = null,
    ): PageRevision {
        $pageId = (int) $page->getKey();

        return DB::transaction(
            function () use (
                $pageId,
                $actor,
                $summary,
                $restoredFromRevisionNumber,
            ): PageRevision {
                $lockedPage = Page::withTrashed()
                    ->lockForUpdate()
                    ->findOrFail($pageId);

                return $this->createSnapshot(
                    page: $lockedPage,
                    actor: $actor,
                    summary: $summary,
                    restoredFromRevisionNumber: $restoredFromRevisionNumber,
                );
            },
        );
    }

    public function restore(
        PageRevision $revision,
        User $actor,
    ): Page {
        Gate::forUser($actor)->authorize(
            'pages.revisions.restore',
        );

        $revisionId = (int) $revision->getKey();

        return DB::transaction(
            function () use (
                $revisionId,
                $actor,
            ): Page {
                $lockedRevision = PageRevision::query()
                    ->lockForUpdate()
                    ->findOrFail($revisionId);

                $page = Page::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        (int) $lockedRevision->page_id,
                    );

                $currentStatus = $this->statusOf(
                    $page,
                );

                if ($currentStatus !== PageStatus::Draft) {
                    throw ValidationException::withMessages([
                        'revision' => 'Only Draft pages may restore an older revision.',
                    ]);
                }

                $revisionTitle = (string) $lockedRevision->title;
                $revisionSlug = (string) $lockedRevision->slug;

                $restoredSlug = PageSlugger::unique(
                    $revisionSlug,
                    (int) $page->id,
                );

                $oldTitle = (string) $page->title;
                $oldSlug = (string) $page->slug;

                $oldExcerpt = is_string($page->excerpt)
                    ? $page->excerpt
                    : '';

                $oldContent = is_string($page->content)
                    ? $page->content
                    : '';

                $oldBlocks = $this->blocksOf(
                    $page,
                );

                $revisionExcerpt = is_string(
                    $lockedRevision->excerpt,
                )
                    ? $lockedRevision->excerpt
                    : '';

                $revisionContent = is_string(
                    $lockedRevision->content,
                )
                    ? $lockedRevision->content
                    : '';

                $revisionBlocks = $this->blocksOf(
                    $lockedRevision,
                );

                $hasChanges =
                    $oldTitle !== $revisionTitle
                    || $oldSlug !== $restoredSlug
                    || $oldExcerpt !== $revisionExcerpt
                    || $oldContent !== $revisionContent
                    || $oldBlocks !== $revisionBlocks;

                if (! $hasChanges) {
                    throw ValidationException::withMessages([
                        'revision' => 'This revision already matches the current page.',
                    ]);
                }

                $page->forceFill([
                    'title' => $revisionTitle,
                    'slug' => $restoredSlug,

                    'excerpt' => $revisionExcerpt !== ''
                        ? $revisionExcerpt
                        : null,

                    'content' => $revisionContent !== ''
                        ? $revisionContent
                        : null,

                    'blocks' => $revisionBlocks,
                    'updated_by' => $actor->id,
                ])->save();

                $newRevision = $this->createSnapshot(
                    page: $page,
                    actor: $actor,
                    summary: sprintf(
                        'Restored from revision #%d.',
                        (int) $lockedRevision->revision_number,
                    ),
                    restoredFromRevisionNumber: (int) $lockedRevision->revision_number,
                );

                app(AuditLogger::class)->log(
                    event: 'pages.revision-restored',
                    description: 'An earlier website page revision was restored.',
                    actor: $actor,
                    subject: $page,
                    oldValues: [
                        'title' => $oldTitle,
                        'slug' => $oldSlug,

                        'excerpt_length' => mb_strlen($oldExcerpt),

                        'content_length' => mb_strlen($oldContent),
                    ],
                    newValues: [
                        'title' => (string) $page->title,
                        'slug' => (string) $page->slug,

                        'excerpt_length' => mb_strlen($revisionExcerpt),

                        'content_length' => mb_strlen($revisionContent),

                        'restored_from_revision' => (int) $lockedRevision->revision_number,

                        'created_revision' => (int) $newRevision->revision_number,

                        'slug_adjusted' => $restoredSlug !== $revisionSlug,
                    ],
                );

                return $page->refresh();
            },
        );
    }

    private function createSnapshot(
        Page $page,
        ?User $actor,
        string $summary,
        ?int $restoredFromRevisionNumber,
    ): PageRevision {
        $status = $this->statusOf(
            $page,
        );

        $blocks = $this->blocksOf(
            $page,
        );

        /**
         * @var array{
         *     title: string,
         *     slug: string,
         *     excerpt: string|null,
         *     content: string|null,
         *     blocks: array<array-key, mixed>|null,
         *     status: string
         * } $snapshot
         */
        $snapshot = [
            'title' => (string) $page->title,
            'slug' => (string) $page->slug,

            'excerpt' => is_string($page->excerpt)
                ? $page->excerpt
                : null,

            'content' => is_string($page->content)
                ? $page->content
                : null,

            'blocks' => $blocks,
            'status' => $status->value,
        ];

        $snapshotHash = hash(
            'sha256',
            serialize($snapshot),
        );

        $latestRevision = PageRevision::query()
            ->where(
                'page_id',
                $page->id,
            )
            ->orderByDesc(
                'revision_number',
            )
            ->first();

        /*
         * Do not create duplicate revisions when
         * the page snapshot has not changed.
         */
        if (
            $latestRevision instanceof PageRevision
            && (string) $latestRevision->snapshot_hash
                === $snapshotHash
        ) {
            return $latestRevision;
        }

        $latestRevisionNumber =
            $latestRevision instanceof PageRevision
                ? (int) $latestRevision->revision_number
                : 0;

        $normalisedSummary = trim(
            $summary,
        );

        return PageRevision::query()->create([
            'page_id' => $page->id,

            'revision_number' => $latestRevisionNumber + 1,

            'title' => $snapshot['title'],
            'slug' => $snapshot['slug'],
            'excerpt' => $snapshot['excerpt'],
            'content' => $snapshot['content'],
            'blocks' => $snapshot['blocks'],
            'status' => $snapshot['status'],

            'change_summary' => $normalisedSummary !== ''
                    ? Str::limit(
                        $normalisedSummary,
                        255,
                        '',
                    )
                    : null,

            'snapshot_hash' => $snapshotHash,

            'restored_from_revision_number' => $restoredFromRevisionNumber,

            'created_by' => $actor?->id,
        ]);
    }

    /**
     * Read and normalise the JSON blocks attribute without
     * relying on PHPStan's inferred database property type.
     *
     * @return array<array-key, mixed>|null
     */
    private function blocksOf(
        Page|PageRevision $model,
    ): ?array {
        $blocks = $model->getAttribute(
            'blocks',
        );

        return is_array($blocks)
            ? $blocks
            : null;
    }

    private function statusOf(
        Page $page,
    ): PageStatus {
        $status = $page->getRawOriginal(
            'status',
        );

        if (! is_string($status)) {
            throw ValidationException::withMessages([
                'revision' => 'The page workflow status is invalid.',
            ]);
        }

        $pageStatus = PageStatus::tryFrom(
            $status,
        );

        if (! $pageStatus instanceof PageStatus) {
            throw ValidationException::withMessages([
                'revision' => 'The page workflow status is invalid.',
            ]);
        }

        return $pageStatus;
    }
}

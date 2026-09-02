<?php

namespace App\Services;

use App\Enums\PageEditorMode;
use App\Enums\PageLocale;
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

                $revisionTitle =
                    (string) $lockedRevision->title;

                $revisionSlug =
                    (string) $lockedRevision->slug;

                $rawPageLocale = $page->getRawOriginal('locale');
                $pageLocaleEnum = is_string($rawPageLocale)
                    ? PageLocale::tryFrom($rawPageLocale)
                    : null;
                $pageLocale = ($pageLocaleEnum ?? PageLocale::English)->value;

                $restoredSlug = PageSlugger::unique(
                    $revisionSlug,
                    (int) $page->id,
                    $pageLocale,
                );

                /*
                 * Current page values.
                 */
                $oldTitle =
                    (string) $page->title;

                $oldSlug =
                    (string) $page->slug;

                $oldExcerpt = is_string(
                    $page->excerpt,
                )
                    ? $page->excerpt
                    : '';

                $oldContent = is_string(
                    $page->content,
                )
                    ? $page->content
                    : '';

                $oldEditorMode =
                    $this->editorModeOf($page);

                $oldShowTitle =
                    (bool) $page->getAttribute('show_title');

                $oldBlocks = $this->blocksOf(
                    $page,
                );

                $oldSeoTitle = $this->nullableString(
                    $page->seo_title,
                );

                $oldMetaDescription =
                    $this->nullableString(
                        $page->meta_description,
                    );

                $oldCanonicalUrl =
                    $this->nullableString(
                        $page->canonical_url,
                    );

                $oldRobotsIndex =
                    (bool) $page->robots_index;

                $oldOgTitle = $this->nullableString(
                    $page->og_title,
                );

                $oldOgDescription =
                    $this->nullableString(
                        $page->og_description,
                    );

                $oldOgImage = $this->nullableString(
                    $page->og_image,
                );

                /*
                 * Selected revision values.
                 */
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

                $revisionEditorMode =
                    $this->editorModeOf($lockedRevision);

                $revisionShowTitle =
                    (bool) $lockedRevision->getAttribute('show_title');

                $revisionBlocks = $this->blocksOf(
                    $lockedRevision,
                );

                $revisionSeoTitle =
                    $this->nullableString(
                        $lockedRevision->seo_title,
                    );

                $revisionMetaDescription =
                    $this->nullableString(
                        $lockedRevision->meta_description,
                    );

                $revisionCanonicalUrl =
                    $this->nullableString(
                        $lockedRevision->canonical_url,
                    );

                $revisionRobotsIndex =
                    (bool) $lockedRevision->robots_index;

                $revisionOgTitle =
                    $this->nullableString(
                        $lockedRevision->og_title,
                    );

                $revisionOgDescription =
                    $this->nullableString(
                        $lockedRevision->og_description,
                    );

                $revisionOgImage =
                    $this->nullableString(
                        $lockedRevision->og_image,
                    );

                /*
                 * Check whether the selected revision differs
                 * from the current Draft page.
                 */
                $hasChanges =
                    $oldTitle !== $revisionTitle
                    || $oldSlug !== $restoredSlug
                    || $oldExcerpt !== $revisionExcerpt
                    || $oldContent !== $revisionContent
                    || $oldEditorMode !== $revisionEditorMode
                    || $oldShowTitle !== $revisionShowTitle
                    || $oldBlocks !== $revisionBlocks
                    || $oldSeoTitle !== $revisionSeoTitle
                    || $oldMetaDescription
                    !== $revisionMetaDescription
                    || $oldCanonicalUrl
                    !== $revisionCanonicalUrl
                    || $oldRobotsIndex
                    !== $revisionRobotsIndex
                    || $oldOgTitle
                    !== $revisionOgTitle
                    || $oldOgDescription
                    !== $revisionOgDescription
                    || $oldOgImage
                    !== $revisionOgImage;

                if (! $hasChanges) {
                    throw ValidationException::withMessages([
                        'revision' => 'This revision already matches the current page.',
                    ]);
                }

                /*
                 * Restore editable page content and SEO metadata.
                 *
                 * Workflow status is intentionally NOT restored.
                 * The page remains Draft.
                 */
                $page->forceFill([
                    'title' => $revisionTitle,
                    'slug' => $restoredSlug,

                    'excerpt' => $revisionExcerpt !== ''
                        ? $revisionExcerpt
                        : null,

                    'content' => $revisionContent !== ''
                        ? $revisionContent
                        : null,

                    'editor_mode' => $revisionEditorMode,

                    'show_title' => $revisionShowTitle,

                    'blocks' => $revisionBlocks,

                    'seo_title' => $revisionSeoTitle,

                    'meta_description' => $revisionMetaDescription,

                    'canonical_url' => $revisionCanonicalUrl,

                    'robots_index' => $revisionRobotsIndex,

                    'og_title' => $revisionOgTitle,

                    'og_description' => $revisionOgDescription,

                    'og_image' => $revisionOgImage,

                    'updated_by' => $actor->id,
                ])->save();

                /*
                 * Restoring an old revision itself creates
                 * a new revision, preserving the full history.
                 */
                $newRevision = $this->createSnapshot(
                    page: $page,
                    actor: $actor,
                    summary: sprintf(
                        'Restored from revision #%d.',
                        (int) $lockedRevision
                            ->revision_number,
                    ),
                    restoredFromRevisionNumber: (int) $lockedRevision
                        ->revision_number,
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

                        'editor_mode' => $oldEditorMode,

                        'show_title' => $oldShowTitle,

                        'robots_index' => $oldRobotsIndex,

                        'seo_title_present' => $oldSeoTitle !== null,

                        'meta_description_present' => $oldMetaDescription !== null,

                        'canonical_url_present' => $oldCanonicalUrl !== null,

                        'og_title_present' => $oldOgTitle !== null,

                        'og_description_present' => $oldOgDescription !== null,

                        'og_image_present' => $oldOgImage !== null,
                    ],
                    newValues: [
                        'title' => (string) $page->title,

                        'slug' => (string) $page->slug,

                        'excerpt_length' => mb_strlen(
                            $revisionExcerpt,
                        ),

                        'content_length' => mb_strlen(
                            $revisionContent,
                        ),

                        'editor_mode' => $revisionEditorMode,

                        'show_title' => $revisionShowTitle,

                        'robots_index' => $revisionRobotsIndex,

                        'seo_title_present' => $revisionSeoTitle !== null,

                        'meta_description_present' => $revisionMetaDescription
                            !== null,

                        'canonical_url_present' => $revisionCanonicalUrl
                            !== null,

                        'og_title_present' => $revisionOgTitle !== null,

                        'og_description_present' => $revisionOgDescription
                            !== null,

                        'og_image_present' => $revisionOgImage !== null,

                        'restored_from_revision' => (int) $lockedRevision
                            ->revision_number,

                        'created_revision' => (int) $newRevision
                            ->revision_number,

                        'slug_adjusted' => $restoredSlug
                            !== $revisionSlug,
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
         *     editor_mode: string,
         *     show_title: bool,
         *     seo_title: string|null,
         *     meta_description: string|null,
         *     canonical_url: string|null,
         *     robots_index: bool,
         *     og_title: string|null,
         *     og_description: string|null,
         *     og_image: string|null,
         *     blocks: array<array-key, mixed>|null,
         *     status: string
         * } $snapshot
         */
        $snapshot = [
            'title' => (string) $page->title,

            'slug' => (string) $page->slug,

            'excerpt' => $this->nullableString(
                $page->excerpt,
            ),

            'content' => $this->nullableString(
                $page->content,
            ),

            'editor_mode' => $this->editorModeOf($page),

            'show_title' => (bool) $page->getAttribute('show_title'),

            'seo_title' => $this->nullableString(
                $page->seo_title,
            ),

            'meta_description' => $this->nullableString(
                $page->meta_description,
            ),

            'canonical_url' => $this->nullableString(
                $page->canonical_url,
            ),

            'robots_index' => (bool) $page->robots_index,

            'og_title' => $this->nullableString(
                $page->og_title,
            ),

            'og_description' => $this->nullableString(
                $page->og_description,
            ),

            'og_image' => $this->nullableString(
                $page->og_image,
            ),

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
         * the snapshot has not changed.
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
            ? (int) $latestRevision
                ->revision_number
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

            'editor_mode' => $snapshot['editor_mode'],

            'show_title' => $snapshot['show_title'],

            'seo_title' => $snapshot['seo_title'],

            'meta_description' => $snapshot['meta_description'],

            'canonical_url' => $snapshot['canonical_url'],

            'robots_index' => $snapshot['robots_index'],

            'og_title' => $snapshot['og_title'],

            'og_description' => $snapshot['og_description'],

            'og_image' => $snapshot['og_image'],

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
     * Read and normalise the JSON blocks attribute
     * without depending on generated model property types.
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

    private function editorModeOf(
        Page|PageRevision $model,
    ): string {
        $editorMode = $model->getRawOriginal(
            'editor_mode',
        );

        if (! is_string($editorMode)) {
            return PageEditorMode::Html->value;
        }

        return match ($editorMode) {
            PageEditorMode::Visual->value => PageEditorMode::Visual->value,
            PageEditorMode::Html->value => PageEditorMode::Html->value,
            default => PageEditorMode::Html->value,
        };
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        return is_string($value)
            ? $value
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

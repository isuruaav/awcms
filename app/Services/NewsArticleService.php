<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class NewsArticleService
{
    public function __construct(
        private readonly ContentSanitizer $contentSanitizer,
        private readonly NewsRevisionService $newsRevisionService,
    ) {}

    public function create(
        User $actor,
        NewsCategory $category,
        string $title,
        string $content,
        ?string $summary = null,
        ?string $slug = null,
        ?MediaAsset $featuredImage = null,
        bool $isFeatured = false,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.create',
        );

        $this->assertCategoryAvailable(
            $category,
        );

        $this->assertFeaturedImageAllowed(
            $featuredImage,
        );

        $safeTitle = $this->plainText(
            value: $title,
            field: 'title',
            maximumLength: 255,
            required: true,
        );

        $safeSummary = $this->plainText(
            value: $summary,
            field: 'summary',
            maximumLength: 2000,
        );

        $safeContent = $this->richContent(
            $content,
        );

        $safeSeoTitle = $this->plainText(
            value: $seoTitle,
            field: 'seo_title',
            maximumLength: 255,
        );

        $safeSeoDescription = $this->plainText(
            value: $seoDescription,
            field: 'seo_description',
            maximumLength: 320,
        );

        $safeSlug = $this->uniqueSlug(
            $slug !== null && trim($slug) !== ''
                ? $slug
                : $safeTitle,
        );

        $featuredImageId =
            $featuredImage instanceof MediaAsset
                ? (int) $featuredImage->getKey()
                : null;

        return DB::transaction(
            function () use (
                $actor,
                $category,
                $safeTitle,
                $safeSlug,
                $safeSummary,
                $safeContent,
                $featuredImageId,
                $isFeatured,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): News {
                $news = News::query()
                    ->create([
                        'category_id' => (int) $category->getKey(),

                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'summary' => $safeSummary,

                        'content' => $safeContent,

                        'featured_image_id' => $featuredImageId,

                        /*
                         * Every newly created article begins
                         * in the Draft workflow state.
                         *
                         * Submit / approve / publish actions
                         * are handled separately by the
                         * workflow layer.
                         */
                        'status' => NewsStatus::Draft->value,

                        'is_featured' => $isFeatured,

                        /*
                         * At Draft stage this represents the
                         * planned publication date/time.
                         *
                         * Setting this field does not publish
                         * the article.
                         */
                        'published_at' => $publishedAt,

                        'submitted_at' => null,

                        'approved_at' => null,

                        'archived_at' => null,

                        'seo_title' => $safeSeoTitle,

                        'seo_description' => $safeSeoDescription,

                        'created_by' => $actor->id,

                        'updated_by' => $actor->id,

                        'submitted_by' => null,

                        'approved_by' => null,

                        'published_by' => null,

                        'archived_by' => null,
                    ]);

                app(
                    AuditLogger::class,
                )->log(
                    event: 'news.created',

                    description: 'A news article was created.',

                    actor: $actor,

                    subject: $news,

                    oldValues: [],

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'category_id' => (int) $category->getKey(),

                        'status' => NewsStatus::Draft->value,

                        'featured_image_id' => $featuredImageId,

                        'is_featured' => $isFeatured,

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),

                        /*
                         * Do not store full article content
                         * inside audit metadata.
                         */
                        'summary_length' => $safeSummary !== null
                                ? mb_strlen(
                                    $safeSummary,
                                )
                                : 0,

                        'content_length' => mb_strlen(
                            $safeContent,
                        ),

                        'has_seo_title' => $safeSeoTitle !== null,

                        'has_seo_description' => $safeSeoDescription !== null,
                    ],
                );

                return $news->refresh();
            },
        );
    }

    public function update(
        News $news,
        User $actor,
        NewsCategory $category,
        string $title,
        string $content,
        ?string $summary = null,
        ?string $slug = null,
        ?MediaAsset $featuredImage = null,
        bool $isFeatured = false,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): News {
        Gate::forUser(
            $actor,
        )->authorize(
            'news.update',
        );

        if ($news->trashed()) {
            throw ValidationException::withMessages([
                'news' => 'A deleted news article cannot be updated.',
            ]);
        }

        $status =
            $news->getAttribute(
                'status',
            );

        /*
         * Normal article editing is only allowed while
         * the workflow considers the article editable.
         */
        if (
            ! $status instanceof NewsStatus
            || ! $status->isEditable()
        ) {
            throw ValidationException::withMessages([
                'news' => 'Only editable news workflow states may be changed.',
            ]);
        }

        /*
         * Validate all incoming relationships and content
         * before creating a revision.
         *
         * This prevents failed/invalid form submissions
         * from creating unnecessary revision records.
         */
        $this->assertCategoryAvailable(
            $category,
        );

        $this->assertFeaturedImageAllowed(
            $featuredImage,
        );

        $safeTitle = $this->plainText(
            value: $title,
            field: 'title',
            maximumLength: 255,
            required: true,
        );

        $safeSummary = $this->plainText(
            value: $summary,
            field: 'summary',
            maximumLength: 2000,
        );

        $safeContent = $this->richContent(
            $content,
        );

        $safeSeoTitle = $this->plainText(
            value: $seoTitle,
            field: 'seo_title',
            maximumLength: 255,
        );

        $safeSeoDescription = $this->plainText(
            value: $seoDescription,
            field: 'seo_description',
            maximumLength: 320,
        );

        $newsId =
            (int) $news->getKey();

        /*
         * When no new slug is supplied during editing,
         * preserve the article's existing URL.
         */
        $currentSlug =
            $news->getAttribute(
                'slug',
            );

        if (
            ! is_string($currentSlug)
            || trim($currentSlug) === ''
        ) {
            $currentSlug =
                $safeTitle;
        }

        $safeSlug = $this->uniqueSlug(
            candidate: $slug !== null
                && trim($slug) !== ''
                    ? $slug
                    : $currentSlug,

            ignoreId: $newsId,
        );

        $featuredImageId =
            $featuredImage instanceof MediaAsset
                ? (int) $featuredImage->getKey()
                : null;

        return DB::transaction(
            function () use (
                $news,
                $actor,
                $category,
                $safeTitle,
                $safeSlug,
                $safeSummary,
                $safeContent,
                $featuredImageId,
                $isFeatured,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): News {
                /*
                 * Preserve bounded old values for audit
                 * logging before modifying the article.
                 */
                $oldCategoryId =
                    $news->getAttribute(
                        'category_id',
                    );

                $oldFeaturedImageId =
                    $news->getAttribute(
                        'featured_image_id',
                    );

                $oldTitle =
                    $news->getAttribute(
                        'title',
                    );

                $oldSlug =
                    $news->getAttribute(
                        'slug',
                    );

                $oldSummary =
                    $news->getAttribute(
                        'summary',
                    );

                $oldContent =
                    $news->getAttribute(
                        'content',
                    );

                $oldIsFeatured =
                    (bool) $news->getAttribute(
                        'is_featured',
                    );

                $oldPublishedAt =
                    $news->getAttribute(
                        'published_at',
                    );

                /*
                 * Store the complete current article state
                 * before overwriting it.
                 *
                 * Revision numbers are controlled by the
                 * NewsRevisionService.
                 */
                $this->newsRevisionService
                    ->createSnapshot(
                        news: $news,
                        actor: $actor,
                        reason: 'Article content updated.',
                    );

                /*
                 * Apply the validated and sanitized values
                 * to the current News record.
                 */
                $news->forceFill([
                    'category_id' => (int) $category->getKey(),

                    'title' => $safeTitle,

                    'slug' => $safeSlug,

                    'summary' => $safeSummary,

                    'content' => $safeContent,

                    'featured_image_id' => $featuredImageId,

                    'is_featured' => $isFeatured,

                    'published_at' => $publishedAt,

                    'seo_title' => $safeSeoTitle,

                    'seo_description' => $safeSeoDescription,

                    'updated_by' => $actor->id,
                ])->save();

                /*
                 * Audit metadata remains intentionally
                 * bounded.
                 *
                 * Full summary/content bodies are stored
                 * by the revision system instead.
                 */
                app(
                    AuditLogger::class,
                )->log(
                    event: 'news.updated',

                    description: 'A news article was updated.',

                    actor: $actor,

                    subject: $news,

                    oldValues: [
                        'title' => is_string(
                            $oldTitle,
                        )
                                ? $oldTitle
                                : null,

                        'slug' => is_string(
                            $oldSlug,
                        )
                                ? $oldSlug
                                : null,

                        'category_id' => is_numeric(
                            $oldCategoryId,
                        )
                                ? (int) $oldCategoryId
                                : null,

                        'featured_image_id' => is_numeric(
                            $oldFeaturedImageId,
                        )
                                ? (int) $oldFeaturedImageId
                                : null,

                        'is_featured' => $oldIsFeatured,

                        'published_at' => $this->dateValue(
                            $oldPublishedAt,
                        ),

                        'summary_length' => is_string(
                            $oldSummary,
                        )
                                ? mb_strlen(
                                    $oldSummary,
                                )
                                : 0,

                        'content_length' => is_string(
                            $oldContent,
                        )
                                ? mb_strlen(
                                    $oldContent,
                                )
                                : 0,
                    ],

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'category_id' => (int) $category->getKey(),

                        'featured_image_id' => $featuredImageId,

                        'is_featured' => $isFeatured,

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),

                        'summary_length' => $safeSummary !== null
                                ? mb_strlen(
                                    $safeSummary,
                                )
                                : 0,

                        'content_length' => mb_strlen(
                            $safeContent,
                        ),

                        'has_seo_title' => $safeSeoTitle !== null,

                        'has_seo_description' => $safeSeoDescription !== null,
                    ],
                );

                return $news->refresh();
            },
        );
    }

    private function assertCategoryAvailable(
        NewsCategory $category,
    ): void {
        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category_id' => 'The selected news category has been deleted.',
            ]);
        }

        $isActive =
            (bool) $category->getAttribute(
                'is_active',
            );

        if (! $isActive) {
            throw ValidationException::withMessages([
                'category_id' => 'Please select an active news category.',
            ]);
        }
    }

    private function assertFeaturedImageAllowed(
        ?MediaAsset $media,
    ): void {
        if (! $media instanceof MediaAsset) {
            return;
        }

        if ($media->trashed()) {
            throw ValidationException::withMessages([
                'featured_image_id' => 'The selected featured image has been deleted.',
            ]);
        }

        if (! $media->isImage()) {
            throw ValidationException::withMessages([
                'featured_image_id' => 'The featured media must be an image.',
            ]);
        }

        /*
         * News is intended for eventual public release.
         *
         * Internal or Restricted media must never
         * accidentally become the public article image.
         */
        if (! $media->isPublic()) {
            throw ValidationException::withMessages([
                'featured_image_id' => 'The featured image must be Public media.',
            ]);
        }
    }

    private function richContent(
        string $content,
    ): string {
        $content = trim(
            $content,
        );

        if ($content === '') {
            throw ValidationException::withMessages([
                'content' => 'The news body is required.',
            ]);
        }

        /*
         * Protect the sanitizer and application from
         * excessively large rich-text submissions.
         */
        if (
            mb_strlen(
                $content,
            ) > 250000
        ) {
            throw ValidationException::withMessages([
                'content' => 'The news body is too large.',
            ]);
        }

        /*
         * HTML received from the editor must never
         * be trusted directly.
         */
        $safeContent =
            $this->contentSanitizer
                ->sanitize(
                    $content,
                );

        if ($safeContent === '') {
            throw ValidationException::withMessages([
                'content' => 'The news body is required.',
            ]);
        }

        /*
         * HTML containing only empty elements is not
         * considered valid article content.
         */
        $plainText =
            $this->contentSanitizer
                ->plainText(
                    $safeContent,
                    2,
                );

        if (
            trim(
                $plainText,
            ) === ''
        ) {
            throw ValidationException::withMessages([
                'content' => 'The news body must contain readable text.',
            ]);
        }

        return $safeContent;
    }

    private function plainText(
        ?string $value,
        string $field,
        int $maximumLength,
        bool $required = false,
    ): ?string {
        if ($value === null) {
            if ($required) {
                throw ValidationException::withMessages([
                    $field => 'This field is required.',
                ]);
            }

            return null;
        }

        $value = trim(
            $value,
        );

        if ($value === '') {
            if ($required) {
                throw ValidationException::withMessages([
                    $field => 'This field is required.',
                ]);
            }

            return null;
        }

        /*
         * Sanitize any unexpected markup and then
         * convert the result into plain text.
         */
        $safeHtml =
            $this->contentSanitizer
                ->sanitize(
                    $value,
                );

        $text =
            $this->contentSanitizer
                ->plainText(
                    $safeHtml,
                    $maximumLength + 1,
                );

        $text = trim(
            $text,
        );

        if ($text === '') {
            if ($required) {
                throw ValidationException::withMessages([
                    $field => 'This field is required.',
                ]);
            }

            return null;
        }

        if (
            mb_strlen(
                $text,
            ) > $maximumLength
        ) {
            throw ValidationException::withMessages([
                $field => sprintf(
                    'This field may not exceed %d characters.',
                    $maximumLength,
                ),
            ]);
        }

        return $text;
    }

    private function uniqueSlug(
        string $candidate,
        ?int $ignoreId = null,
    ): string {
        $baseSlug =
            Str::slug(
                $candidate,
            );

        if ($baseSlug === '') {
            $baseSlug =
                'news';
        }

        /*
         * news.slug is varchar(255).
         *
         * Keep space for duplicate suffixes such as:
         *
         * article-title-2
         * article-title-3
         */
        $baseSlug =
            mb_substr(
                $baseSlug,
                0,
                240,
            );

        $slug =
            $baseSlug;

        $counter =
            2;

        while (
            $this->slugExists(
                slug: $slug,
                ignoreId: $ignoreId,
            )
        ) {
            $suffix =
                '-'.$counter;

            $slug =
                mb_substr(
                    $baseSlug,
                    0,
                    255 - mb_strlen(
                        $suffix,
                    ),
                )
                .$suffix;

            $counter++;
        }

        return $slug;
    }

    private function slugExists(
        string $slug,
        ?int $ignoreId,
    ): bool {
        /*
         * Soft-deleted articles are intentionally included
         * so their previous public URLs are not reused.
         */
        $query =
            News::query()
                ->withTrashed()
                ->where(
                    'slug',
                    $slug,
                );

        if ($ignoreId !== null) {
            $query->where(
                'id',
                '!=',
                $ignoreId,
            );
        }

        return $query->exists();
    }

    private function dateValue(
        mixed $value,
    ): ?string {
        if (
            $value instanceof DateTimeInterface
        ) {
            return $value->format(
                DATE_ATOM,
            );
        }

        if (is_string($value)) {
            $value = trim(
                $value,
            );

            return $value !== ''
                ? $value
                : null;
        }

        return null;
    }
}

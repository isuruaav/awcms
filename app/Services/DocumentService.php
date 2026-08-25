<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\MediaAsset;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DocumentService
{
    public function __construct(
        private readonly ContentSanitizer $contentSanitizer,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Create Document
    |--------------------------------------------------------------------------
    */

    public function create(
        User $actor,
        string $title,
        ?DocumentCategory $category = null,
        ?string $description = null,
        ?DateTimeInterface $documentDate = null,
        ?string $slug = null,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): Document {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.create',
        );

        $this->assertCategoryAllowed(
            $category,
            'document_category_id',
        );

        $safeTitle =
            $this->plainText(
                value: $title,
                field: 'title',
                maximumLength: 255,
                required: true,
            );

        if (! is_string($safeTitle)) {
            throw ValidationException::withMessages([
                'title' => 'The document title is required.',
            ]);
        }

        $safeDescription =
            $this->plainText(
                value: $description,
                field: 'description',
                maximumLength: 10000,
            );

        $safeSeoTitle =
            $this->plainText(
                value: $seoTitle,
                field: 'seo_title',
                maximumLength: 255,
            );

        $safeSeoDescription =
            $this->plainText(
                value: $seoDescription,
                field: 'seo_description',
                maximumLength: 320,
            );

        $safeSlug =
            $this->uniqueSlug(
                candidate: $slug !== null
                    && trim($slug) !== ''
                    ? $slug
                    : $safeTitle,
            );

        $categoryId =
            $category instanceof DocumentCategory
                ? (int) $category->getKey()
                : null;

        return DB::transaction(
            function () use (
                $actor,
                $safeTitle,
                $safeSlug,
                $categoryId,
                $safeDescription,
                $documentDate,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): Document {
                if ($categoryId !== null) {
                    $category =
                        DocumentCategory::withTrashed()
                            ->find(
                                $categoryId,
                            );

                    $this->assertCategoryAllowed(
                        $category,
                        'document_category_id',
                    );
                }

                $document =
                    Document::query()->create([
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'document_category_id' => $categoryId,

                        'description' => $safeDescription,

                        'document_date' => $documentDate?->format(
                            'Y-m-d',
                        ),

                        'current_version' => 0,

                        'status' => DocumentStatus::Draft->value,

                        'published_at' => $publishedAt,

                        'archived_at' => null,

                        'seo_title' => $safeSeoTitle,

                        'seo_description' => $safeSeoDescription,

                        'created_by' => $actor->id,

                        'updated_by' => $actor->id,

                        'published_by' => null,

                        'archived_by' => null,
                    ]);

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.created',

                    description: 'A document was created.',

                    actor: $actor,

                    subject: $document,

                    oldValues: [],

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'document_category_id' => $categoryId,

                        'document_date' => $documentDate?->format(
                            'Y-m-d',
                        ),

                        'current_version' => 0,

                        'status' => DocumentStatus::Draft->value,

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),
                    ],
                );

                return $document->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Document Metadata
    |--------------------------------------------------------------------------
    */

    public function update(
        Document $document,
        User $actor,
        string $title,
        ?DocumentCategory $category = null,
        ?string $description = null,
        ?DateTimeInterface $documentDate = null,
        ?string $slug = null,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): Document {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.update',
        );

        $this->assertCategoryAllowed(
            $category,
            'document_category_id',
        );

        $safeTitle =
            $this->plainText(
                value: $title,
                field: 'title',
                maximumLength: 255,
                required: true,
            );

        if (! is_string($safeTitle)) {
            throw ValidationException::withMessages([
                'title' => 'The document title is required.',
            ]);
        }

        $safeDescription =
            $this->plainText(
                value: $description,
                field: 'description',
                maximumLength: 10000,
            );

        $safeSeoTitle =
            $this->plainText(
                value: $seoTitle,
                field: 'seo_title',
                maximumLength: 255,
            );

        $safeSeoDescription =
            $this->plainText(
                value: $seoDescription,
                field: 'seo_description',
                maximumLength: 320,
            );

        $documentId =
            (int) $document->getKey();

        $currentSlug =
            $this->stringValue(
                $document->getAttribute(
                    'slug',
                ),
            );

        $safeSlug =
            $this->uniqueSlug(
                candidate: $slug !== null
                    && trim($slug) !== ''
                    ? $slug
                    : (
                        is_string($currentSlug)
                        && trim($currentSlug) !== ''
                            ? $currentSlug
                            : $safeTitle
                    ),
                ignoreId: $documentId,
            );

        $categoryId =
            $category instanceof DocumentCategory
                ? (int) $category->getKey()
                : null;

        return DB::transaction(
            function () use (
                $documentId,
                $actor,
                $safeTitle,
                $safeSlug,
                $categoryId,
                $safeDescription,
                $documentDate,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): Document {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $this->assertEditable(
                    $document,
                );

                if ($categoryId !== null) {
                    $category =
                        DocumentCategory::withTrashed()
                            ->find(
                                $categoryId,
                            );

                    $this->assertCategoryAllowed(
                        $category,
                        'document_category_id',
                    );
                }

                $oldValues = [
                    'title' => $this->stringValue(
                        $document->getAttribute(
                            'title',
                        ),
                    ),

                    'slug' => $this->stringValue(
                        $document->getAttribute(
                            'slug',
                        ),
                    ),

                    'document_category_id' => $this->integerValue(
                        $document->getAttribute(
                            'document_category_id',
                        ),
                    ),

                    'description' => $this->stringValue(
                        $document->getAttribute(
                            'description',
                        ),
                    ),

                    'document_date' => $this->dateValue(
                        $document->getAttribute(
                            'document_date',
                        ),
                    ),

                    'published_at' => $this->dateValue(
                        $document->getAttribute(
                            'published_at',
                        ),
                    ),

                    'seo_title' => $this->stringValue(
                        $document->getAttribute(
                            'seo_title',
                        ),
                    ),

                    'seo_description' => $this->stringValue(
                        $document->getAttribute(
                            'seo_description',
                        ),
                    ),
                ];

                $document->forceFill([
                    'title' => $safeTitle,

                    'slug' => $safeSlug,

                    'document_category_id' => $categoryId,

                    'description' => $safeDescription,

                    'document_date' => $documentDate?->format(
                        'Y-m-d',
                    ),

                    'published_at' => $publishedAt,

                    'seo_title' => $safeSeoTitle,

                    'seo_description' => $safeSeoDescription,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.updated',

                    description: 'Document metadata was updated.',

                    actor: $actor,

                    subject: $document,

                    oldValues: $oldValues,

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'document_category_id' => $categoryId,

                        'description' => $safeDescription,

                        'document_date' => $documentDate?->format(
                            'Y-m-d',
                        ),

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),

                        'seo_title' => $safeSeoTitle,

                        'seo_description' => $safeSeoDescription,
                    ],
                );

                return $document->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Add / Replace PDF Version
    |--------------------------------------------------------------------------
    */

    public function addVersion(
        Document $document,
        User $actor,
        MediaAsset $media,
        ?string $versionLabel = null,
        ?string $changeNote = null,
    ): DocumentVersion {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.update',
        );

        $this->assertPdfAllowed(
            $media,
            'media_asset_id',
        );

        $safeVersionLabel =
            $this->plainText(
                value: $versionLabel,
                field: 'version_label',
                maximumLength: 100,
            );

        $safeChangeNote =
            $this->plainText(
                value: $changeNote,
                field: 'change_note',
                maximumLength: 2000,
            );

        $documentId =
            (int) $document->getKey();

        $mediaId =
            (int) $media->getKey();

        return DB::transaction(
            function () use (
                $documentId,
                $actor,
                $mediaId,
                $safeVersionLabel,
                $safeChangeNote,
            ): DocumentVersion {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $status =
                    $this->status(
                        $document,
                    );

                if ($status === DocumentStatus::Archived) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Archived documents cannot receive new versions.',
                    ]);
                }

                /*
                 * Replacing the file of a live document is a
                 * publishing action, not only an edit action.
                 */
                if ($status === DocumentStatus::Published) {
                    Gate::forUser(
                        $actor,
                    )->authorize(
                        'documents.publish',
                    );
                }

                $media =
                    MediaAsset::withTrashed()
                        ->find(
                            $mediaId,
                        );

                $this->assertPdfAllowed(
                    $media,
                    'media_asset_id',
                );

                if (! $media instanceof MediaAsset) {
                    throw ValidationException::withMessages([
                        'media_asset_id' => 'The selected PDF could not be found.',
                    ]);
                }

                $latestVersion =
                    DocumentVersion::query()
                        ->where(
                            'document_id',
                            $documentId,
                        )
                        ->lockForUpdate()
                        ->max(
                            'version',
                        );

                $currentMaximum =
                    is_numeric($latestVersion)
                        ? (int) $latestVersion
                        : 0;

                $nextVersion =
                    $currentMaximum + 1;

                $version =
                    DocumentVersion::query()->create([
                        'document_id' => $documentId,

                        'media_asset_id' => $mediaId,

                        'version' => $nextVersion,

                        'version_label' => $safeVersionLabel,

                        'change_note' => $safeChangeNote,

                        'uploaded_by' => $actor->id,
                    ]);

                $oldCurrentVersion =
                    $this->integerValue(
                        $document->getAttribute(
                            'current_version',
                        ),
                    );

                $document->forceFill([
                    'current_version' => $nextVersion,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.version-added',

                    description: 'A new PDF version was added to a document.',

                    actor: $actor,

                    subject: $document,

                    oldValues: [
                        'current_version' => $oldCurrentVersion,
                    ],

                    newValues: [
                        'current_version' => $nextVersion,

                        'document_version_id' => (int) $version->getKey(),

                        'media_asset_id' => $mediaId,

                        'version_label' => $safeVersionLabel,
                    ],
                );

                return $version->refresh();
            },
            3,
        );
    }

    /*
|--------------------------------------------------------------------------
| Restore Previous PDF Version
|--------------------------------------------------------------------------
*/

    public function restoreVersion(
        Document $document,
        DocumentVersion $version,
        User $actor,
    ): Document {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.update',
        );

        $documentId =
            (int) $document->getKey();

        $versionId =
            (int) $version->getKey();

        return DB::transaction(
            function () use (
                $documentId,
                $versionId,
                $actor,
            ): Document {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $status =
                    $this->status(
                        $document,
                    );

                if ($status === DocumentStatus::Archived) {
                    throw ValidationException::withMessages([
                        'version' => 'Archived documents cannot restore previous versions.',
                    ]);
                }

                /*
                 * Restoring a different PDF on a live document changes
                 * the publicly served file, so publishing permission
                 * is required.
                 */
                if ($status === DocumentStatus::Published) {
                    Gate::forUser(
                        $actor,
                    )->authorize(
                        'documents.publish',
                    );
                }

                $version =
                    DocumentVersion::query()
                        ->whereKey(
                            $versionId,
                        )
                        ->where(
                            'document_id',
                            $documentId,
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $version instanceof DocumentVersion) {
                    throw ValidationException::withMessages([
                        'version' => 'The selected version does not belong to this document.',
                    ]);
                }

                $restoreVersion =
                    $this->integerValue(
                        $version->getAttribute(
                            'version',
                        ),
                    );

                if (
                    $restoreVersion === null
                    || $restoreVersion < 1
                ) {
                    throw ValidationException::withMessages([
                        'version' => 'The selected document version is invalid.',
                    ]);
                }

                $currentVersion =
                    $this->integerValue(
                        $document->getAttribute(
                            'current_version',
                        ),
                    );

                if ($currentVersion === $restoreVersion) {
                    throw ValidationException::withMessages([
                        'version' => 'The selected version is already the current version.',
                    ]);
                }

                $mediaId =
                    $version->getAttribute(
                        'media_asset_id',
                    );

                if (! is_numeric($mediaId)) {
                    throw ValidationException::withMessages([
                        'version' => 'The selected version does not contain a valid PDF.',
                    ]);
                }

                $media =
                    MediaAsset::withTrashed()
                        ->find(
                            (int) $mediaId,
                        );

                /*
                 * A historic version is never restored blindly.
                 * It must still satisfy the current public PDF rules.
                 */
                $this->assertPdfAllowed(
                    $media,
                    'version',
                );

                $document->forceFill([
                    'current_version' => $restoreVersion,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.version-restored',

                    description: 'A previous PDF version was restored as the current document version.',

                    actor: $actor,

                    subject: $document,

                    oldValues: [
                        'current_version' => $currentVersion,
                    ],

                    newValues: [
                        'current_version' => $restoreVersion,

                        'document_version_id' => (int) $version->getKey(),

                        'media_asset_id' => (int) $mediaId,
                    ],
                );

                return $document->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Publish
    |--------------------------------------------------------------------------
    */

    public function publish(
        Document $document,
        User $actor,
    ): Document {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.publish',
        );

        $documentId =
            (int) $document->getKey();

        return DB::transaction(
            function () use (
                $documentId,
                $actor,
            ): Document {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $status =
                    $this->status(
                        $document,
                    );

                if ($status !== DocumentStatus::Draft) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only Draft documents can be published.',
                    ]);
                }

                $currentVersion =
                    $this->integerValue(
                        $document->getAttribute(
                            'current_version',
                        ),
                    );

                if (
                    $currentVersion === null
                    || $currentVersion < 1
                ) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Add a PDF version before publishing the document.',
                    ]);
                }

                $version =
                    DocumentVersion::query()
                        ->where(
                            'document_id',
                            $documentId,
                        )
                        ->where(
                            'version',
                            $currentVersion,
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $version instanceof DocumentVersion) {
                    throw ValidationException::withMessages([
                        'workflow' => 'The current document version could not be found.',
                    ]);
                }

                $mediaId =
                    $version->getAttribute(
                        'media_asset_id',
                    );

                if (! is_numeric($mediaId)) {
                    throw ValidationException::withMessages([
                        'workflow' => 'The current PDF version is invalid.',
                    ]);
                }

                $media =
                    MediaAsset::withTrashed()
                        ->find(
                            (int) $mediaId,
                        );

                $this->assertPdfAllowed(
                    $media,
                    'workflow',
                );

                $publishedAt =
                    $document->getAttribute(
                        'published_at',
                    );

                if (! $publishedAt instanceof DateTimeInterface) {
                    $publishedAt =
                        now();
                }

                $document->forceFill([
                    'status' => DocumentStatus::Published->value,

                    'published_at' => $publishedAt,

                    'published_by' => $actor->id,

                    'archived_at' => null,

                    'archived_by' => null,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.published',

                    description: 'A document was published.',

                    actor: $actor,

                    subject: $document,

                    oldValues: [
                        'status' => DocumentStatus::Draft->value,
                    ],

                    newValues: [
                        'status' => DocumentStatus::Published->value,

                        'current_version' => $currentVersion,

                        'published_at' => $publishedAt->format(
                            DATE_ATOM,
                        ),
                    ],
                );

                return $document->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Archive
    |--------------------------------------------------------------------------
    */

    public function archive(
        Document $document,
        User $actor,
    ): Document {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.archive',
        );

        $documentId =
            (int) $document->getKey();

        return DB::transaction(
            function () use (
                $documentId,
                $actor,
            ): Document {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $status =
                    $this->status(
                        $document,
                    );

                if ($status !== DocumentStatus::Published) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only Published documents can be archived.',
                    ]);
                }

                $document->forceFill([
                    'status' => DocumentStatus::Archived->value,

                    'archived_at' => now(),

                    'archived_by' => $actor->id,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.archived',

                    description: 'A document was archived.',

                    actor: $actor,

                    subject: $document,

                    oldValues: [
                        'status' => DocumentStatus::Published->value,
                    ],

                    newValues: [
                        'status' => DocumentStatus::Archived->value,
                    ],
                );

                return $document->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        Document $document,
        User $actor,
    ): void {
        Gate::forUser(
            $actor,
        )->authorize(
            'documents.delete',
        );

        $documentId =
            (int) $document->getKey();

        DB::transaction(
            function () use (
                $documentId,
                $actor,
            ): void {
                $document =
                    Document::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $documentId,
                        );

                $status =
                    $this->status(
                        $document,
                    );

                if ($status === DocumentStatus::Published) {
                    throw ValidationException::withMessages([
                        'document' => 'Archive the document before deleting it.',
                    ]);
                }

                $oldValues = [
                    'title' => $this->stringValue(
                        $document->getAttribute(
                            'title',
                        ),
                    ),

                    'slug' => $this->stringValue(
                        $document->getAttribute(
                            'slug',
                        ),
                    ),

                    'status' => $status->value,

                    'current_version' => $this->integerValue(
                        $document->getAttribute(
                            'current_version',
                        ),
                    ),
                ];

                $document->delete();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'documents.deleted',

                    description: 'A document was deleted.',

                    actor: $actor,

                    subject: $document,

                    oldValues: $oldValues,

                    newValues: [
                        'deleted' => true,
                    ],
                );
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Helpers
    |--------------------------------------------------------------------------
    */

    private function assertEditable(
        Document $document,
    ): void {
        $status =
            $this->status(
                $document,
            );

        if (! $status->isEditable()) {
            throw ValidationException::withMessages([
                'document' => 'Only Draft documents may be edited.',
            ]);
        }
    }

    private function status(
        Document $document,
    ): DocumentStatus {
        $status =
            $document->getAttribute(
                'status',
            );

        if (! $status instanceof DocumentStatus) {
            throw ValidationException::withMessages([
                'document' => 'The document has an invalid workflow status.',
            ]);
        }

        return $status;
    }

    private function assertCategoryAllowed(
        ?DocumentCategory $category,
        string $field,
    ): void {
        if (! $category instanceof DocumentCategory) {
            return;
        }

        if ($category->trashed()) {
            throw ValidationException::withMessages([
                $field => 'The selected document category has been deleted.',
            ]);
        }

        $isActive =
            $category->getAttribute(
                'is_active',
            );

        if ($isActive !== true) {
            throw ValidationException::withMessages([
                $field => 'The selected document category is inactive.',
            ]);
        }
    }

    private function assertPdfAllowed(
        ?MediaAsset $media,
        string $field,
    ): void {
        if (! $media instanceof MediaAsset) {
            throw ValidationException::withMessages([
                $field => 'The selected PDF could not be found.',
            ]);
        }

        if ($media->trashed()) {
            throw ValidationException::withMessages([
                $field => 'The selected PDF has been deleted.',
            ]);
        }

        if (! $media->isDocument()) {
            throw ValidationException::withMessages([
                $field => 'The selected media must be a document.',
            ]);
        }

        if (! $media->isPublic()) {
            throw ValidationException::withMessages([
                $field => 'Documents must use Public media.',
            ]);
        }

        if ($media->isExternal()) {
            throw ValidationException::withMessages([
                $field => 'The selected PDF must be an uploaded file.',
            ]);
        }

        $mimeType =
            $this->stringValue(
                $media->getAttribute(
                    'mime_type',
                ),
            );

        $extension =
            $this->stringValue(
                $media->getAttribute(
                    'extension',
                ),
            );

        if (
            $mimeType !== 'application/pdf'
            || ! is_string($extension)
            || strtolower($extension) !== 'pdf'
        ) {
            throw ValidationException::withMessages([
                $field => 'The selected document must be a valid PDF.',
            ]);
        }

        $disk =
            $this->stringValue(
                $media->getAttribute(
                    'disk',
                ),
            );

        $path =
            $this->stringValue(
                $media->getAttribute(
                    'path',
                ),
            );

        if (
            ! is_string($disk)
            || trim($disk) === ''
            || ! is_string($path)
            || trim($path) === ''
        ) {
            throw ValidationException::withMessages([
                $field => 'The selected PDF does not have a valid stored file.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Text Sanitisation
    |--------------------------------------------------------------------------
    */

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

        $value =
            trim(
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

        $text =
            trim(
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

    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */

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
                'document';
        }

        $baseSlug =
            Str::limit(
                $baseSlug,
                230,
                '',
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
                Str::limit(
                    $baseSlug,
                    255 - strlen(
                        $suffix,
                    ),
                    '',
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
        $query =
            Document::withTrashed()
                ->where(
                    'slug',
                    $slug,
                );

        if ($ignoreId !== null) {
            $query->whereKeyNot(
                $ignoreId,
            );
        }

        return $query->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Value Helpers
    |--------------------------------------------------------------------------
    */

    private function stringValue(
        mixed $value,
    ): ?string {
        return is_string(
            $value,
        )
            ? $value
            : null;
    }

    private function integerValue(
        mixed $value,
    ): ?int {
        return is_numeric(
            $value,
        )
            ? (int) $value
            : null;
    }

    private function dateValue(
        mixed $value,
    ): ?string {
        return $value instanceof DateTimeInterface
            ? $value->format(
                DATE_ATOM,
            )
            : null;
    }
}

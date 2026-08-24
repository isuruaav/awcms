<?php

namespace App\Services;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\MediaAsset;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GalleryService
{
    public function __construct(
        private readonly ContentSanitizer $contentSanitizer,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Create Gallery
    |--------------------------------------------------------------------------
    */

    public function create(
        User $actor,
        string $title,
        ?DateTimeInterface $eventDate = null,
        ?string $description = null,
        ?string $slug = null,
        ?MediaAsset $coverMedia = null,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): Gallery {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.create',
        );

        $this->assertMediaAllowed(
            $coverMedia,
            'cover_media_id',
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
                'title' => 'The gallery title is required.',
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

        $coverMediaId =
            $coverMedia instanceof MediaAsset
            ? (int) $coverMedia->getKey()
            : null;

        return DB::transaction(
            function () use (
                $actor,
                $safeTitle,
                $safeSlug,
                $eventDate,
                $safeDescription,
                $coverMediaId,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): Gallery {
                $gallery =
                    Gallery::query()->create([
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'event_date' => $eventDate?->format(
                            'Y-m-d',
                        ),

                        'description' => $safeDescription,

                        'cover_media_id' => $coverMediaId,

                        'status' => GalleryStatus::Draft->value,

                        /*
                         * This may contain an optional planned
                         * publication date.
                         *
                         * Public visibility is still blocked
                         * until status becomes Published.
                         */
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
                    event: 'galleries.created',

                    description: 'A gallery was created.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [],

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'event_date' => $eventDate?->format(
                            'Y-m-d',
                        ),

                        'cover_media_id' => $coverMediaId,

                        'status' => GalleryStatus::Draft->value,

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),
                    ],
                );

                return $gallery->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Gallery
    |--------------------------------------------------------------------------
    */

    public function update(
        Gallery $gallery,
        User $actor,
        string $title,
        ?DateTimeInterface $eventDate = null,
        ?string $description = null,
        ?string $slug = null,
        ?MediaAsset $coverMedia = null,
        ?DateTimeInterface $publishedAt = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): Gallery {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.update',
        );

        $this->assertMediaAllowed(
            $coverMedia,
            'cover_media_id',
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
                'title' => 'The gallery title is required.',
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

        $galleryId =
            (int) $gallery->getKey();

        $currentSlug =
            $gallery->getAttribute(
                'slug',
            );

        if (
            ! is_string(
                $currentSlug,
            )
            || trim(
                $currentSlug,
            ) === ''
        ) {
            $currentSlug =
                $safeTitle;
        }

        $safeSlug =
            $this->uniqueSlug(
                candidate: $slug !== null
                    && trim($slug) !== ''
                    ? $slug
                    : $currentSlug,

                ignoreId: $galleryId,
            );

        $coverMediaId =
            $coverMedia instanceof MediaAsset
            ? (int) $coverMedia->getKey()
            : null;

        return DB::transaction(
            function () use (
                $galleryId,
                $actor,
                $safeTitle,
                $safeSlug,
                $eventDate,
                $safeDescription,
                $coverMediaId,
                $publishedAt,
                $safeSeoTitle,
                $safeSeoDescription,
            ): Gallery {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $this->assertEditable(
                    $gallery,
                );

                $oldValues = [
                    'title' => $this->stringValue(
                        $gallery->getAttribute(
                            'title',
                        ),
                    ),

                    'slug' => $this->stringValue(
                        $gallery->getAttribute(
                            'slug',
                        ),
                    ),

                    'event_date' => $this->dateValue(
                        $gallery->getAttribute(
                            'event_date',
                        ),
                    ),

                    'cover_media_id' => $this->integerValue(
                        $gallery->getAttribute(
                            'cover_media_id',
                        ),
                    ),

                    'published_at' => $this->dateValue(
                        $gallery->getAttribute(
                            'published_at',
                        ),
                    ),
                ];

                $gallery->forceFill([
                    'title' => $safeTitle,

                    'slug' => $safeSlug,

                    'event_date' => $eventDate?->format(
                        'Y-m-d',
                    ),

                    'description' => $safeDescription,

                    'cover_media_id' => $coverMediaId,

                    'published_at' => $publishedAt,

                    'seo_title' => $safeSeoTitle,

                    'seo_description' => $safeSeoDescription,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.updated',

                    description: 'A gallery was updated.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: $oldValues,

                    newValues: [
                        'title' => $safeTitle,

                        'slug' => $safeSlug,

                        'event_date' => $eventDate?->format(
                            'Y-m-d',
                        ),

                        'cover_media_id' => $coverMediaId,

                        'published_at' => $publishedAt?->format(
                            DATE_ATOM,
                        ),
                    ],
                );

                return $gallery->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Add Image
    |--------------------------------------------------------------------------
    */

    public function addImage(
        Gallery $gallery,
        MediaAsset $media,
        User $actor,
        ?string $caption = null,
        ?string $altText = null,
    ): GalleryImage {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.update',
        );

        $this->assertMediaAllowed(
            $media,
            'media_asset_id',
        );

        $safeCaption =
            $this->plainText(
                value: $caption,
                field: 'caption',
                maximumLength: 1000,
            );

        $safeAltText =
            $this->plainText(
                value: $altText,
                field: 'alt_text',
                maximumLength: 255,
            );

        $galleryId =
            (int) $gallery->getKey();

        $mediaId =
            (int) $media->getKey();

        return DB::transaction(
            function () use (
                $galleryId,
                $mediaId,
                $actor,
                $safeCaption,
                $safeAltText,
            ): GalleryImage {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $this->assertEditable(
                    $gallery,
                );

                $alreadyAttached =
                    GalleryImage::query()
                        ->where(
                            'gallery_id',
                            $galleryId,
                        )
                        ->where(
                            'media_asset_id',
                            $mediaId,
                        )
                        ->exists();

                if ($alreadyAttached) {
                    throw ValidationException::withMessages([
                        'media_asset_id' => 'This image is already attached to the gallery.',
                    ]);
                }

                $maximumSortOrder =
                    GalleryImage::query()
                        ->where(
                            'gallery_id',
                            $galleryId,
                        )
                        ->max(
                            'sort_order',
                        );

                $nextSortOrder =
                    is_numeric(
                        $maximumSortOrder,
                    )
                    ? (int) $maximumSortOrder + 1
                    : 0;

                $galleryImage =
                    GalleryImage::query()->create([
                        'gallery_id' => $galleryId,

                        'media_asset_id' => $mediaId,

                        'caption' => $safeCaption,

                        'alt_text' => $safeAltText,

                        'sort_order' => $nextSortOrder,

                        'created_by' => $actor->id,

                        'updated_by' => $actor->id,
                    ]);

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.image-added',

                    description: 'An image was added to a gallery.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [],

                    newValues: [
                        'gallery_image_id' => (int) $galleryImage->getKey(),

                        'media_asset_id' => $mediaId,

                        'sort_order' => $nextSortOrder,
                    ],
                );

                return $galleryImage->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Image Metadata
    |--------------------------------------------------------------------------
    */

    public function updateImage(
        GalleryImage $galleryImage,
        User $actor,
        ?string $caption = null,
        ?string $altText = null,
    ): GalleryImage {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.update',
        );

        $safeCaption =
            $this->plainText(
                value: $caption,
                field: 'caption',
                maximumLength: 1000,
            );

        $safeAltText =
            $this->plainText(
                value: $altText,
                field: 'alt_text',
                maximumLength: 255,
            );

        $galleryImageId =
            (int) $galleryImage->getKey();

        return DB::transaction(
            function () use (
                $galleryImageId,
                $actor,
                $safeCaption,
                $safeAltText,
            ): GalleryImage {
                $galleryImage =
                    GalleryImage::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryImageId,
                        );

                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            (int) $galleryImage->gallery_id,
                        );

                $this->assertEditable(
                    $gallery,
                );

                $oldCaption =
                    $this->stringValue(
                        $galleryImage->getAttribute(
                            'caption',
                        ),
                    );

                $oldAltText =
                    $this->stringValue(
                        $galleryImage->getAttribute(
                            'alt_text',
                        ),
                    );

                $galleryImage->forceFill([
                    'caption' => $safeCaption,

                    'alt_text' => $safeAltText,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.image-updated',

                    description: 'Gallery image metadata was updated.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [
                        'gallery_image_id' => $galleryImageId,

                        'caption' => $oldCaption,

                        'alt_text' => $oldAltText,
                    ],

                    newValues: [
                        'gallery_image_id' => $galleryImageId,

                        'caption' => $safeCaption,

                        'alt_text' => $safeAltText,
                    ],
                );

                return $galleryImage->refresh();
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Image
    |--------------------------------------------------------------------------
    */

    public function removeImage(
        GalleryImage $galleryImage,
        User $actor,
    ): void {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.update',
        );

        $galleryImageId =
            (int) $galleryImage->getKey();

        DB::transaction(
            function () use (
                $galleryImageId,
                $actor,
            ): void {
                $galleryImage =
                    GalleryImage::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryImageId,
                        );

                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            (int) $galleryImage->gallery_id,
                        );

                $this->assertEditable(
                    $gallery,
                );

                $mediaId =
                    (int) $galleryImage->media_asset_id;

                $galleryImage->delete();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.image-removed',

                    description: 'An image was removed from a gallery.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [
                        'gallery_image_id' => $galleryImageId,

                        'media_asset_id' => $mediaId,
                    ],

                    newValues: [],
                );
            },
            3,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reorder Images
    |--------------------------------------------------------------------------
    */

    /**
     * @param  list<int>  $orderedImageIds
     */
    public function reorderImages(
        Gallery $gallery,
        User $actor,
        array $orderedImageIds,
    ): Gallery {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.update',
        );

        $galleryId =
            (int) $gallery->getKey();

        $orderedImageIds =
            array_values(
                array_unique(
                    array_map(
                        static fn (int $id): int => $id,

                        $orderedImageIds,
                    ),
                ),
            );

        return DB::transaction(
            function () use (
                $galleryId,
                $actor,
                $orderedImageIds,
            ): Gallery {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $this->assertEditable(
                    $gallery,
                );

                $galleryImages =
                    GalleryImage::query()
                        ->where(
                            'gallery_id',
                            $galleryId,
                        )
                        ->lockForUpdate()
                        ->orderBy(
                            'sort_order',
                        )
                        ->orderBy(
                            'id',
                        )
                        ->get();

                /**
                 * @var list<int> $currentIds
                 */
                $currentIds = [];

                foreach (
                    $galleryImages as $galleryImage
                ) {
                    $currentIds[] =
                        (int) $galleryImage->getKey();
                }

                $expected =
                    $currentIds;

                $provided =
                    $orderedImageIds;

                sort(
                    $expected,
                );

                sort(
                    $provided,
                );

                if ($expected !== $provided) {
                    throw ValidationException::withMessages([
                        'imageOrder' => 'The image order must contain every gallery image exactly once.',
                    ]);
                }

                foreach (
                    $orderedImageIds as $position => $imageId
                ) {
                    GalleryImage::query()
                        ->where(
                            'gallery_id',
                            $galleryId,
                        )
                        ->whereKey(
                            $imageId,
                        )
                        ->update([
                            'sort_order' => $position,

                            'updated_by' => $actor->id,

                            'updated_at' => now(),
                        ]);
                }

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.images-reordered',

                    description: 'Gallery images were reordered.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [
                        'image_order' => $currentIds,
                    ],

                    newValues: [
                        'image_order' => $orderedImageIds,
                    ],
                );

                return $gallery->refresh();
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
        Gallery $gallery,
        User $actor,
    ): Gallery {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.publish',
        );

        $galleryId =
            (int) $gallery->getKey();

        return DB::transaction(
            function () use (
                $galleryId,
                $actor,
            ): Gallery {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $status =
                    $this->status(
                        $gallery,
                    );

                if (
                    $status !==
                    GalleryStatus::Draft
                ) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only Draft galleries can be published.',
                    ]);
                }

                $galleryImages =
                    GalleryImage::query()
                        ->where(
                            'gallery_id',
                            $galleryId,
                        )
                        ->lockForUpdate()
                        ->get();

                if ($galleryImages->isEmpty()) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Add at least one image before publishing the gallery.',
                    ]);
                }

                /*
                 * Revalidate every attached image at publication
                 * time.
                 *
                 * A Media Library asset may have been deleted or
                 * changed from Public visibility after it was
                 * originally attached to the gallery.
                 */
                foreach ($galleryImages as $galleryImage) {
                    $mediaId =
                        $galleryImage->getAttribute(
                            'media_asset_id',
                        );

                    if (! is_numeric($mediaId)) {
                        throw ValidationException::withMessages([
                            'workflow' => 'One or more gallery images are invalid.',
                        ]);
                    }

                    $media =
                        MediaAsset::withTrashed()
                            ->find(
                                (int) $mediaId,
                            );

                    if (! $media instanceof MediaAsset) {
                        throw ValidationException::withMessages([
                            'workflow' => 'One or more gallery images no longer exist in the Media Library.',
                        ]);
                    }

                    $this->assertMediaAllowed(
                        $media,
                        'workflow',
                    );
                }

                /*
                 * Revalidate the optional cover image as well.
                 */
                $coverMediaId =
                    $gallery->getAttribute(
                        'cover_media_id',
                    );

                if (is_numeric($coverMediaId)) {
                    $coverMedia =
                        MediaAsset::withTrashed()
                            ->find(
                                (int) $coverMediaId,
                            );

                    if (! $coverMedia instanceof MediaAsset) {
                        throw ValidationException::withMessages([
                            'workflow' => 'The gallery cover image no longer exists.',
                        ]);
                    }

                    $this->assertMediaAllowed(
                        $coverMedia,
                        'workflow',
                    );
                }

                $publishedAt =
                    $gallery->getAttribute(
                        'published_at',
                    );

                if (! $publishedAt instanceof DateTimeInterface) {
                    $publishedAt =
                        now();
                }

                $gallery->forceFill([
                    'status' => GalleryStatus::Published->value,

                    'published_at' => $publishedAt,

                    'published_by' => $actor->id,

                    'archived_at' => null,

                    'archived_by' => null,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.published',

                    description: 'A gallery was published.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [
                        'status' => GalleryStatus::Draft->value,
                    ],

                    newValues: [
                        'status' => GalleryStatus::Published->value,

                        'published_at' => $publishedAt->format(
                            DATE_ATOM,
                        ),
                    ],
                );

                return $gallery->refresh();
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
        Gallery $gallery,
        User $actor,
    ): Gallery {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.archive',
        );

        $galleryId =
            (int) $gallery->getKey();

        return DB::transaction(
            function () use (
                $galleryId,
                $actor,
            ): Gallery {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $status =
                    $this->status(
                        $gallery,
                    );

                if (
                    $status !==
                    GalleryStatus::Published
                ) {
                    throw ValidationException::withMessages([
                        'workflow' => 'Only Published galleries can be archived.',
                    ]);
                }

                $gallery->forceFill([
                    'status' => GalleryStatus::Archived->value,

                    'archived_at' => now(),

                    'archived_by' => $actor->id,

                    'updated_by' => $actor->id,
                ])->save();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.archived',

                    description: 'A gallery was archived.',

                    actor: $actor,

                    subject: $gallery,

                    oldValues: [
                        'status' => GalleryStatus::Published->value,
                    ],

                    newValues: [
                        'status' => GalleryStatus::Archived->value,
                    ],
                );

                return $gallery->refresh();
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
        Gallery $gallery,
        User $actor,
    ): void {
        Gate::forUser(
            $actor,
        )->authorize(
            'galleries.delete',
        );

        $galleryId =
            (int) $gallery->getKey();

        DB::transaction(
            function () use (
                $galleryId,
                $actor,
            ): void {
                $gallery =
                    Gallery::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $galleryId,
                        );

                $status =
                    $this->status(
                        $gallery,
                    );

                if (
                    $status ===
                    GalleryStatus::Published
                ) {
                    throw ValidationException::withMessages([
                        'gallery' => 'Archive the gallery before deleting it.',
                    ]);
                }

                $oldValues = [
                    'title' => $this->stringValue(
                        $gallery->getAttribute(
                            'title',
                        ),
                    ),

                    'slug' => $this->stringValue(
                        $gallery->getAttribute(
                            'slug',
                        ),
                    ),

                    'status' => $status->value,
                ];

                $gallery->delete();

                app(
                    AuditLogger::class,
                )->log(
                    event: 'galleries.deleted',

                    description: 'A gallery was deleted.',

                    actor: $actor,

                    subject: $gallery,

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
        Gallery $gallery,
    ): void {
        $status =
            $this->status(
                $gallery,
            );

        if (! $status->isEditable()) {
            throw ValidationException::withMessages([
                'gallery' => 'Only Draft galleries may be edited.',
            ]);
        }
    }

    private function status(
        Gallery $gallery,
    ): GalleryStatus {
        $status =
            $gallery->getAttribute(
                'status',
            );

        if (! $status instanceof GalleryStatus) {
            throw ValidationException::withMessages([
                'gallery' => 'The gallery has an invalid workflow status.',
            ]);
        }

        return $status;
    }

    private function assertMediaAllowed(
        ?MediaAsset $media,
        string $field,
    ): void {
        if (! $media instanceof MediaAsset) {
            return;
        }

        if ($media->trashed()) {
            throw ValidationException::withMessages([
                $field => 'The selected image has been deleted.',
            ]);
        }

        if (! $media->isImage()) {
            throw ValidationException::withMessages([
                $field => 'The selected media must be an image.',
            ]);
        }

        if (! $media->isPublic()) {
            throw ValidationException::withMessages([
                $field => 'Gallery images must use Public media.',
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
                'gallery';
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
        return Gallery::withTrashed()
            ->where(
                'slug',
                $slug,
            )
            ->when(
                $ignoreId !== null,
                static fn ($query) => $query->whereKeyNot(
                    $ignoreId,
                ),
            )
            ->exists();
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

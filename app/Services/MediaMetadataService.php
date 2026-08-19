<?php

namespace App\Services;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class MediaMetadataService
{
    public function __construct(
        private readonly ContentSanitizer $contentSanitizer,
    ) {}

    public function update(
        MediaAsset $media,
        User $actor,
        string $title,
        ?string $altText,
        ?string $caption,
        MediaVisibility $visibility,
    ): MediaAsset {
        Gate::forUser($actor)->authorize(
            'media.update',
        );

        $safeTitle = $this->plainText(
            $title,
            255,
        );

        if ($safeTitle === '') {
            throw ValidationException::withMessages([
                'title' => 'The media title is required.',
            ]);
        }

        $safeAltText = $this->plainText(
            $altText,
            255,
        );

        $safeCaption = $this->plainText(
            $caption,
            2000,
        );

        $type = $this->typeOf(
            $media,
        );

        /*
         * Alternative text only applies to images.
         */
        if ($type !== MediaType::Image) {
            $safeAltText = '';
        }

        $oldVisibility = $this->visibilityOf(
            $media,
        );

        $source = $this->sourceOf(
            $media,
        );

        $oldDisk = $this->nullableString(
            $media->getAttribute(
                'disk',
            ),
        );

        $path = $this->nullableString(
            $media->getAttribute(
                'path',
            ),
        );

        /*
         * For external media this value is not used.
         *
         * For uploaded media it becomes the authoritative
         * destination storage disk.
         */
        $newDisk =
            $oldDisk ?? '';

        /**
         * Paths copied to the new disk.
         *
         * Includes:
         * - original
         * - thumbnail
         * - medium
         *
         * @var list<string> $copiedPaths
         */
        $copiedPaths = [];

        /**
         * Original disk used when files are physically moved.
         */
        $sourceDiskForMove = null;

        if ($source === MediaSource::Upload) {
            if (
                $oldDisk === null
                || $path === null
            ) {
                throw new RuntimeException(
                    'The stored media location is incomplete.',
                );
            }

            $newDisk = $this->diskFor(
                $visibility,
            );

            /*
             * Public <-> Private requires a real
             * physical storage move.
             *
             * Internal <-> Restricted both use the
             * private disk, so no file copy is needed.
             */
            if ($oldDisk !== $newDisk) {
                $storedPaths =
                    $this->storedPaths(
                        media: $media,
                        originalPath: $path,
                        expectedDisk: $oldDisk,
                    );

                $copiedPaths =
                    $this->copyPathsBetweenDisks(
                        sourceDisk: $oldDisk,
                        destinationDisk: $newDisk,
                        paths: $storedPaths,
                    );

                $sourceDiskForMove =
                    $oldDisk;
            }
        }

        $oldValues = [
            'title_length' => mb_strlen(
                (string) (
                    $media->getAttribute(
                        'title',
                    )
                    ?? ''
                ),
            ),

            'alt_text_length' => mb_strlen(
                (string) (
                    $media->getAttribute(
                        'alt_text',
                    )
                    ?? ''
                ),
            ),

            'caption_length' => mb_strlen(
                (string) (
                    $media->getAttribute(
                        'caption',
                    )
                    ?? ''
                ),
            ),

            'visibility' => $oldVisibility->value,

            'disk' => $oldDisk,
        ];

        try {
            $updatedMedia = DB::transaction(
                function () use (
                    $media,
                    $actor,
                    $safeTitle,
                    $safeAltText,
                    $safeCaption,
                    $visibility,
                    $source,
                    $newDisk,
                    $copiedPaths,
                    $oldValues,
                ): MediaAsset {
                    $attributes = [
                        'title' => $safeTitle,

                        'alt_text' => $safeAltText !== ''
                            ? $safeAltText
                            : null,

                        'caption' => $safeCaption !== ''
                            ? $safeCaption
                            : null,

                        'visibility' => $visibility->value,
                    ];

                    if (
                        $source ===
                        MediaSource::Upload
                    ) {
                        $attributes['disk'] =
                            $newDisk;
                    }

                    $media->fill(
                        $attributes,
                    );

                    $media->save();

                    /*
                     * Critical 08D-2 step:
                     *
                     * When original + variants were copied
                     * to another disk, update every variant
                     * DB record inside the same transaction.
                     */
                    if (
                        $source ===
                        MediaSource::Upload
                        && $copiedPaths !== []
                    ) {
                        $media
                            ->variants()
                            ->update([
                                'disk' => $newDisk,
                            ]);
                    }

                    app(
                        AuditLogger::class,
                    )->log(
                        event: 'media.updated',

                        description: 'Media metadata was updated.',

                        actor: $actor,

                        subject: $media,

                        oldValues: $oldValues,

                        newValues: [
                            'title_length' => mb_strlen(
                                $safeTitle,
                            ),

                            'alt_text_length' => mb_strlen(
                                $safeAltText,
                            ),

                            'caption_length' => mb_strlen(
                                $safeCaption,
                            ),

                            'visibility' => $visibility->value,

                            'disk' => $source ===
                                MediaSource::Upload
                                ? $newDisk
                                : null,

                            'moved_files_count' => count(
                                $copiedPaths,
                            ),
                        ],
                    );

                    return $media->refresh();
                },
            );
        } catch (Throwable $exception) {
            /*
             * DB update failed.
             *
             * Remove the copies from the destination
             * disk so the original disk remains
             * authoritative.
             */
            foreach (
                $copiedPaths as $copiedPath
            ) {
                try {
                    Storage::disk(
                        $newDisk,
                    )->delete(
                        $copiedPath,
                    );
                } catch (Throwable) {
                    /*
                     * Preserve the original exception.
                     */
                }
            }

            throw $exception;
        }

        /*
         * Database commit succeeded.
         *
         * The new disk is now authoritative, so remove
         * original + variant copies from the old disk.
         */
        if ($sourceDiskForMove !== null) {
            foreach (
                $copiedPaths as $copiedPath
            ) {
                try {
                    Storage::disk(
                        $sourceDiskForMove,
                    )->delete(
                        $copiedPath,
                    );
                } catch (Throwable) {
                    /*
                     * The database and destination copy
                     * are valid.
                     *
                     * Any remaining old orphan can be
                     * cleaned by maintenance tooling.
                     */
                }
            }
        }

        return $updatedMedia;
    }

    /**
     * Build the complete list of stored physical files.
     *
     * @return list<string>
     */
    private function storedPaths(
        MediaAsset $media,
        string $originalPath,
        string $expectedDisk,
    ): array {
        $paths = [
            $originalPath,
        ];

        foreach (
            $media->variants()->get() as $variant
        ) {
            $variantDisk =
                $this->nullableString(
                    $variant->getAttribute(
                        'disk',
                    ),
                );

            $variantPath =
                $this->nullableString(
                    $variant->getAttribute(
                        'path',
                    ),
                );

            if (
                $variantDisk === null
                || $variantPath === null
            ) {
                throw new RuntimeException(
                    'A media variant storage location is incomplete.',
                );
            }

            /*
             * A media asset and all its variants must
             * live on the same storage disk.
             */
            if (
                $variantDisk !==
                $expectedDisk
            ) {
                throw new RuntimeException(
                    'A media variant is stored on an unexpected disk.',
                );
            }

            if (
                ! in_array(
                    $variantPath,
                    $paths,
                    true,
                )
            ) {
                $paths[] =
                    $variantPath;
            }
        }

        return $paths;
    }

    /**
     * Copy original + variants between disks.
     *
     * The old copies are NOT removed here.
     * Removal happens only after the database commit.
     *
     * @param  list<string>  $paths
     * @return list<string>
     */
    private function copyPathsBetweenDisks(
        string $sourceDisk,
        string $destinationDisk,
        array $paths,
    ): array {
        $source = Storage::disk(
            $sourceDisk,
        );

        $destination = Storage::disk(
            $destinationDisk,
        );

        /**
         * @var list<string> $copied
         */
        $copied = [];

        try {
            foreach ($paths as $path) {
                if (
                    ! $source->exists(
                        $path,
                    )
                ) {
                    throw new RuntimeException(
                        'A media file required for the storage move is missing.',
                    );
                }

                if (
                    $destination->exists(
                        $path,
                    )
                ) {
                    throw new RuntimeException(
                        'A media file already exists at the destination location.',
                    );
                }

                $stream =
                    $source->readStream(
                        $path,
                    );

                if (! is_resource($stream)) {
                    throw new RuntimeException(
                        'A media file could not be read.',
                    );
                }

                try {
                    $stored =
                        $destination
                            ->writeStream(
                                $path,
                                $stream,
                            );
                } finally {
                    fclose(
                        $stream,
                    );
                }

                if ($stored !== true) {
                    throw new RuntimeException(
                        'A media file could not be copied to the new storage location.',
                    );
                }

                $copied[] =
                    $path;
            }
        } catch (Throwable $exception) {
            /*
             * A partial copy failed.
             *
             * Remove anything already copied to the
             * destination disk.
             */
            foreach (
                $copied as $copiedPath
            ) {
                try {
                    $destination->delete(
                        $copiedPath,
                    );
                } catch (Throwable) {
                    /*
                     * Preserve original exception.
                     */
                }
            }

            throw $exception;
        }

        return $copied;
    }

    private function diskFor(
        MediaVisibility $visibility,
    ): string {
        $configKey =
            $visibility ===
            MediaVisibility::Public
            ? 'media.disks.public'
            : 'media.disks.private';

        $disk = config(
            $configKey,
        );

        if (
            ! is_string($disk)
            || trim($disk) === ''
        ) {
            throw new RuntimeException(
                'The media storage disk is not configured.',
            );
        }

        return trim(
            $disk,
        );
    }

    private function typeOf(
        MediaAsset $media,
    ): MediaType {
        $type = $media->getAttribute(
            'type',
        );

        if (! $type instanceof MediaType) {
            throw new RuntimeException(
                'The media type is invalid.',
            );
        }

        return $type;
    }

    private function sourceOf(
        MediaAsset $media,
    ): MediaSource {
        $source = $media->getAttribute(
            'source',
        );

        if (
            ! $source
                instanceof MediaSource
        ) {
            throw new RuntimeException(
                'The media source is invalid.',
            );
        }

        return $source;
    }

    private function visibilityOf(
        MediaAsset $media,
    ): MediaVisibility {
        $visibility =
            $media->getAttribute(
                'visibility',
            );

        if (
            ! $visibility
                instanceof MediaVisibility
        ) {
            throw new RuntimeException(
                'The media visibility is invalid.',
            );
        }

        return $visibility;
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

    private function plainText(
        ?string $value,
        int $maximumLength,
    ): string {
        if ($value === null) {
            return '';
        }

        $value = trim(
            $value,
        );

        if ($value === '') {
            return '';
        }

        $safeHtml =
            $this->contentSanitizer
                ->sanitize(
                    $value,
                );

        if ($safeHtml === '') {
            return '';
        }

        return $this
            ->contentSanitizer
            ->plainText(
                $safeHtml,
                $maximumLength,
            );
    }
}

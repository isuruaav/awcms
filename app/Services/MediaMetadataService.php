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

        if ($type !== MediaType::Image) {
            $safeAltText = '';
        }

        $oldVisibility =
            $this->visibilityOf(
                $media,
            );

        $source = $this->sourceOf(
            $media,
        );

        $oldDisk = $this->nullableString(
            $media->getAttribute('disk'),
        );

        $path = $this->nullableString(
            $media->getAttribute('path'),
        );

        $newDisk = $source === MediaSource::Upload
            ? $this->diskFor($visibility)
            : $oldDisk;

        $copiedToNewDisk = false;

        if (
            $source === MediaSource::Upload
            && $oldVisibility !== $visibility
            && $oldDisk !== $newDisk
        ) {
            if (
                $oldDisk === null
                || $newDisk === null
                || $path === null
            ) {
                throw new RuntimeException(
                    'The stored media location is incomplete.',
                );
            }

            $this->copyBetweenDisks(
                sourceDisk: $oldDisk,
                destinationDisk: $newDisk,
                path: $path,
            );

            $copiedToNewDisk = true;
        }

        $oldValues = [
            'title_length' => mb_strlen(
                (string) $media->getAttribute(
                    'title',
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

                    app(AuditLogger::class)->log(
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
                        ],
                    );

                    return $media->refresh();
                },
            );
        } catch (Throwable $exception) {
            if ($copiedToNewDisk) {
                try {
                    Storage::disk(
                        $newDisk,
                    )->delete(
                        $path,
                    );
                } catch (Throwable) {
                    /*
             * Preserve original exception.
             */
                }
            }

            throw $exception;
        }

        /*
         * Database now points to the new disk.
         * The old copy can be removed.
         *
         * If this cleanup fails, the authoritative
         * database/file remains valid and only an
         * orphaned old copy may remain.
         */
        if ($copiedToNewDisk) {
            try {
                Storage::disk(
                    $oldDisk,
                )->delete(
                    $path,
                );
            } catch (Throwable) {
                /*
         * The database already points to the
         * authoritative new copy.
         *
         * Any old orphaned copy can be handled
         * by maintenance tooling later.
         */
            }
        }

        return $updatedMedia;
    }

    private function copyBetweenDisks(
        string $sourceDisk,
        string $destinationDisk,
        string $path,
    ): void {
        $source = Storage::disk(
            $sourceDisk,
        );

        $destination = Storage::disk(
            $destinationDisk,
        );

        if (! $source->exists($path)) {
            throw new RuntimeException(
                'The original media file is missing.',
            );
        }

        if ($destination->exists($path)) {
            throw new RuntimeException(
                'A file already exists at the destination location.',
            );
        }

        $stream = $source->readStream(
            $path,
        );

        if (! is_resource($stream)) {
            throw new RuntimeException(
                'The media file could not be read.',
            );
        }

        try {
            $stored = $destination->put(
                $path,
                $stream,
            );
        } finally {
            fclose($stream);
        }

        if ($stored !== true) {
            throw new RuntimeException(
                'The media file could not be copied to the new storage location.',
            );
        }
    }

    private function diskFor(
        MediaVisibility $visibility,
    ): string {
        $configKey = $visibility
            === MediaVisibility::Public
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

        return trim($disk);
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

        if (! $source instanceof MediaSource) {
            throw new RuntimeException(
                'The media source is invalid.',
            );
        }

        return $source;
    }

    private function visibilityOf(
        MediaAsset $media,
    ): MediaVisibility {
        $visibility = $media->getAttribute(
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

        $value = trim($value);

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

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $safeHtml = $this
            ->contentSanitizer
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

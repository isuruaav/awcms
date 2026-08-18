<?php

namespace App\Services;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\User;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class MediaUploadService
{
    public function __construct(
        private readonly MediaUploadSecurity $security,
        private readonly ContentSanitizer $contentSanitizer,
    ) {}

    public function upload(
        UploadedFile $file,
        MediaType $type,
        MediaVisibility $visibility,
        User $actor,
        ?string $title = null,
        ?string $altText = null,
        ?string $caption = null,
    ): MediaAsset {
        Gate::forUser($actor)->authorize(
            'media.upload',
        );

        if (! $this->security->uploadsAllowed($type)) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    '%s uploads are not enabled.',
                    $type->label(),
                ),
            ]);
        }

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file is invalid or incomplete.',
            ]);
        }

        $realPath = $file->getRealPath();

        if (
            ! is_string($realPath)
            || $realPath === ''
            || ! is_file($realPath)
        ) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be inspected.',
            ]);
        }

        $sizeBytes = $file->getSize();

        if (! is_int($sizeBytes)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file size could not be determined.',
            ]);
        }

        $this->validateSize(
            type: $type,
            sizeBytes: $sizeBytes,
        );

        /*
         * Never trust the MIME value submitted by
         * the browser. Detect it from file contents.
         */
        $mimeType = $this->detectMimeType(
            $realPath,
        );

        if (
            ! $this->security->allowsMimeType(
                $type,
                $mimeType,
            )
        ) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file type is not allowed.',
            ]);
        }

        /*
         * Client extension is not trusted either,
         * but checking it against the detected MIME
         * prevents confusing or deceptive filenames.
         */
        $originalExtension = strtolower(
            ltrim(
                trim(
                    $file->getClientOriginalExtension(),
                ),
                '.',
            ),
        );

        if (
            $originalExtension === ''
            || $this->security
                ->isForbiddenExtension(
                    $originalExtension,
                )
        ) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file extension is not allowed.',
            ]);
        }

        if (
            ! $this->security
                ->allowsExtensionForMimeType(
                    type: $type,
                    mimeType: $mimeType,
                    extension: $originalExtension,
                )
        ) {
            throw ValidationException::withMessages([
                'file' => 'The filename extension does not match the detected file type.',
            ]);
        }

        $storageExtension = $this->security
            ->preferredExtensionForMimeType(
                $type,
                $mimeType,
            );

        if ($storageExtension === null) {
            throw ValidationException::withMessages([
                'file' => 'A safe storage extension could not be determined.',
            ]);
        }

        $checksum = hash_file(
            'sha256',
            $realPath,
        );

        if (! is_string($checksum)) {
            throw new RuntimeException(
                'Unable to calculate the media file checksum.',
            );
        }

        [
            $width,
            $height,
        ] = $this->imageDimensions(
            type: $type,
            path: $realPath,
        );

        $disk = $this->diskFor(
            $visibility,
        );

        $directory = $this->directoryFor(
            $type,
        );

        /*
         * Original user filename is NEVER used
         * as the physical stored filename.
         */
        $storedName =
            Str::uuid()->toString()
            .'.'
            .$storageExtension;

        $originalName = $this->safeOriginalName(
            $file->getClientOriginalName(),
            $storageExtension,
        );

        $mediaTitle = $this->mediaTitle(
            title: $title,
            originalName: $originalName,
        );

        $safeAltText = $this->plainText(
            $altText,
            255,
        );

        $safeCaption = $this->plainText(
            $caption,
            2000,
        );

        $storedPath = Storage::disk(
            $disk,
        )->putFileAs(
            $directory,
            $file,
            $storedName,
        );

        if (
            ! is_string($storedPath)
            || $storedPath === ''
        ) {
            throw new RuntimeException(
                'The media file could not be stored.',
            );
        }

        try {
            return DB::transaction(
                function () use (
                    $actor,
                    $type,
                    $visibility,
                    $mediaTitle,
                    $safeAltText,
                    $safeCaption,
                    $disk,
                    $directory,
                    $storedName,
                    $originalName,
                    $storedPath,
                    $mimeType,
                    $storageExtension,
                    $sizeBytes,
                    $width,
                    $height,
                    $checksum,
                ): MediaAsset {
                    $media = MediaAsset::query()->create([
                        'type' => $type->value,

                        'source' => MediaSource::Upload->value,

                        'visibility' => $visibility->value,

                        'title' => $mediaTitle,

                        'alt_text' => $safeAltText !== ''
                            ? $safeAltText
                            : null,

                        'caption' => $safeCaption !== ''
                            ? $safeCaption
                            : null,

                        'disk' => $disk,

                        'directory' => $directory,

                        'stored_name' => $storedName,

                        'original_name' => $originalName,

                        'path' => $storedPath,

                        'external_url' => null,

                        'mime_type' => $mimeType,

                        'extension' => $storageExtension,

                        'size_bytes' => $sizeBytes,

                        'width' => $width,

                        'height' => $height,

                        'checksum' => $checksum,

                        /*
                         * Raw EXIF data is intentionally
                         * not persisted.
                         */
                        'metadata' => null,

                        'uploaded_by' => $actor->id,
                    ]);

                    app(AuditLogger::class)->log(
                        event: 'media.uploaded',
                        description: 'A media asset was uploaded.',
                        actor: $actor,
                        subject: $media,
                        oldValues: [],
                        newValues: [
                            'type' => $type->value,

                            'visibility' => $visibility->value,

                            'mime_type' => $mimeType,

                            'extension' => $storageExtension,

                            'size_bytes' => $sizeBytes,

                            'width' => $width,

                            'height' => $height,

                            'checksum' => $checksum,
                        ],
                    );

                    return $media;
                },
            );
        } catch (Throwable $exception) {
            /*
             * Filesystem and database transactions are
             * separate systems. If database/audit work
             * fails after storage, remove the orphaned
             * physical file before rethrowing.
             */
            try {
                Storage::disk(
                    $disk,
                )->delete(
                    $storedPath,
                );
            } catch (Throwable) {
                /*
                 * Preserve the original exception.
                 * Orphan cleanup can later also be
                 * handled by maintenance tooling.
                 */
            }

            throw $exception;
        }
    }

    private function validateSize(
        MediaType $type,
        int $sizeBytes,
    ): void {
        if ($sizeBytes <= 0) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file is empty.',
            ]);
        }

        $maximumKilobytes =
            $this->security->maximumKilobytes(
                $type,
            );

        if ($maximumKilobytes <= 0) {
            throw ValidationException::withMessages([
                'file' => 'Uploads for this media type are not configured.',
            ]);
        }

        $maximumBytes =
            $maximumKilobytes * 1024;

        if ($sizeBytes > $maximumBytes) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    'The uploaded file may not be larger than %d KB.',
                    $maximumKilobytes,
                ),
            ]);
        }
    }

    private function detectMimeType(
        string $path,
    ): string {
        $fileInfo = new finfo(
            FILEINFO_MIME_TYPE,
        );

        $mimeType = $fileInfo->file(
            $path,
        );

        if (
            ! is_string($mimeType)
            || trim($mimeType) === ''
        ) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file type could not be detected.',
            ]);
        }

        return strtolower(
            trim($mimeType),
        );
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function imageDimensions(
        MediaType $type,
        string $path,
    ): array {
        if ($type !== MediaType::Image) {
            return [
                null,
                null,
            ];
        }

        $dimensions = @getimagesize(
            $path,
        );

        if ($dimensions === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image could not be decoded safely.',
            ]);
        }

        $width = $dimensions[0];
        $height = $dimensions[1];

        if (
            $width <= 0
            || $height <= 0
        ) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image dimensions are invalid.',
            ]);
        }

        return [
            $width,
            $height,
        ];
    }

    private function diskFor(
        MediaVisibility $visibility,
    ): string {
        $key = $visibility
            === MediaVisibility::Public
            ? 'media.disks.public'
            : 'media.disks.private';

        $disk = config(
            $key,
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

    private function directoryFor(
        MediaType $type,
    ): string {
        $baseDirectory = config(
            'media.directory',
            'media',
        );

        if (
            ! is_string($baseDirectory)
            || trim($baseDirectory, "/\\ \t\n\r\0\x0B")
            === ''
        ) {
            throw new RuntimeException(
                'The media storage directory is not configured.',
            );
        }

        $baseDirectory = trim(
            $baseDirectory,
            "/\\ \t\n\r\0\x0B",
        );

        return sprintf(
            '%s/%s/%s/%s',
            $baseDirectory,
            $type->value,
            now()->format('Y'),
            now()->format('m'),
        );
    }

    private function safeOriginalName(
        string $originalName,
        string $fallbackExtension,
    ): string {
        $originalName = str_replace(
            '\\',
            '/',
            $originalName,
        );

        $originalName = basename(
            $originalName,
        );

        $originalName = $this->plainText(
            $originalName,
            255,
        );

        if ($originalName !== '') {
            return $originalName;
        }

        return 'upload.'
            .$fallbackExtension;
    }

    private function mediaTitle(
        ?string $title,
        string $originalName,
    ): string {
        $title = $this->plainText(
            $title,
            255,
        );

        if ($title !== '') {
            return $title;
        }

        $filenameTitle = pathinfo(
            $originalName,
            PATHINFO_FILENAME,
        );

        $filenameTitle = $this->plainText(
            $filenameTitle,
            255,
        );

        return $filenameTitle !== ''
            ? $filenameTitle
            : 'Untitled media';
    }

    private function plainText(
        mixed $value,
        int $maximumLength,
    ): string {
        if (! is_string($value)) {
            return '';
        }

        $value = trim(
            $value,
        );

        if ($value === '') {
            return '';
        }

        /*
         * Remove forbidden active markup first,
         * then convert the remaining safe value
         * to plain text.
         */
        $safeHtml = $this->contentSanitizer
            ->sanitize(
                $value,
            );

        if ($safeHtml === '') {
            return '';
        }

        return $this->contentSanitizer
            ->plainText(
                $safeHtml,
                $maximumLength,
            );
    }
}

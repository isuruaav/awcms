<?php

namespace App\Services;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
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

final class MediaReplacementService
{
    public function __construct(
        private readonly MediaUploadSecurity $security,
        private readonly ContentSanitizer $contentSanitizer,
        private readonly MediaImageVariantService $imageVariantService,
    ) {}

    public function replace(
        MediaAsset $media,
        UploadedFile $file,
        User $actor,
    ): MediaAsset {
        Gate::forUser($actor)->authorize(
            'media.replace',
        );

        if ($media->trashed()) {
            throw new RuntimeException(
                'A deleted media asset cannot be replaced.',
            );
        }

        $source = $media->getAttribute(
            'source',
        );

        if (
            ! $source instanceof MediaSource
            || $source !== MediaSource::Upload
        ) {
            throw new RuntimeException(
                'Only uploaded media files can be replaced.',
            );
        }

        $type = $media->getAttribute(
            'type',
        );

        if (! $type instanceof MediaType) {
            throw new RuntimeException(
                'The media type is invalid.',
            );
        }

        if (! $this->security->uploadsAllowed($type)) {
            throw ValidationException::withMessages([
                'replacementFile' => sprintf(
                    '%s uploads are not enabled.',
                    $type->label(),
                ),
            ]);
        }

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file is invalid or incomplete.',
            ]);
        }

        $realPath = $file->getRealPath();

        if (
            ! is_string($realPath)
            || $realPath === ''
            || ! is_file($realPath)
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file could not be inspected.',
            ]);
        }

        $sizeBytes = $file->getSize();

        if (! is_int($sizeBytes)) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file size could not be determined.',
            ]);
        }

        $this->validateSize(
            type: $type,
            sizeBytes: $sizeBytes,
        );

        /*
         * Never trust the MIME type supplied
         * by the browser.
         */
        $mimeType = $this->detectMimeType(
            $realPath,
        );

        if (
            ! $this->security->allowsMimeType(
                type: $type,
                mimeType: $mimeType,
            )
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file type is not allowed for this media asset.',
            ]);
        }

        /*
         * Never trust the client filename extension.
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
            || $this->security->isForbiddenExtension(
                $originalExtension,
            )
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file extension is not allowed.',
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
                'replacementFile' => 'The replacement filename extension does not match the detected file type.',
            ]);
        }

        /*
         * Physical extension comes only from
         * trusted server-side configuration.
         */
        $storageExtension = $this->security
            ->preferredExtensionForMimeType(
                type: $type,
                mimeType: $mimeType,
            );

        if ($storageExtension === null) {
            throw ValidationException::withMessages([
                'replacementFile' => 'A safe storage extension could not be determined.',
            ]);
        }

        $checksum = hash_file(
            'sha256',
            $realPath,
        );

        if (! is_string($checksum)) {
            throw new RuntimeException(
                'Unable to calculate the replacement file checksum.',
            );
        }

        [
            $width,
            $height,
        ] = $this->imageDimensions(
            type: $type,
            path: $realPath,
        );

        /*
         * Replacement remains on the media asset's
         * existing approved storage disk.
         */
        $disk = $this->approvedDisk(
            $media,
        );

        $directory = $this->directoryFor(
            $media,
        );

        $storedName =
            Str::uuid()->toString()
            .'.'
            .$storageExtension;

        $originalName = $this->safeOriginalName(
            originalName: $file
                ->getClientOriginalName(),

            fallbackExtension: $storageExtension,
        );

        /*
         * Snapshot the old physical files before
         * any database records are changed.
         */
        $oldPhysicalFiles =
            $this->oldPhysicalFiles(
                $media,
            );

        /*
         * Only bounded, non-sensitive metadata is
         * retained in the replacement audit event.
         */
        $oldAuditValues = [
            'original_name' => $this->nullableString(
                $media->getAttribute(
                    'original_name',
                ),
            ),

            'mime_type' => $this->nullableString(
                $media->getAttribute(
                    'mime_type',
                ),
            ),

            'extension' => $this->nullableString(
                $media->getAttribute(
                    'extension',
                ),
            ),

            'size_bytes' => $this->nullableInteger(
                $media->getAttribute(
                    'size_bytes',
                ),
            ),

            'width' => $this->nullableInteger(
                $media->getAttribute(
                    'width',
                ),
            ),

            'height' => $this->nullableInteger(
                $media->getAttribute(
                    'height',
                ),
            ),

            'checksum' => $this->nullableString(
                $media->getAttribute(
                    'checksum',
                ),
            ),
        ];

        /*
         * Store the new original first.
         *
         * The existing file remains untouched until
         * the complete replacement transaction commits.
         */
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
                'The replacement media file could not be stored.',
            );
        }

        /**
         * If image variant generation succeeds but
         * something later in the outer transaction
         * fails, these newly-created physical variants
         * must also be removed.
         *
         * @var list<MediaVariant> $newVariants
         */
        $newVariants = [];

        try {
            $replacedMedia = DB::transaction(
                function () use (
                    $media,
                    $actor,
                    $type,
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
                    $oldAuditValues,
                    &$newVariants,
                ): MediaAsset {
                    $media->forceFill([
                        'directory' => $directory,

                        'stored_name' => $storedName,

                        'original_name' => $originalName,

                        'path' => $storedPath,

                        'mime_type' => $mimeType,

                        'extension' => $storageExtension,

                        'size_bytes' => $sizeBytes,

                        'width' => $width,

                        'height' => $height,

                        'checksum' => $checksum,

                        /*
                         * File-specific metadata must
                         * never remain stale after a
                         * physical file replacement.
                         */
                        'metadata' => null,
                    ])->save();

                    $media->refresh();

                    /*
                     * Generate image variants while the
                     * outer DB transaction is still active.
                     *
                     * The variant service must NOT delete
                     * previous physical variants yet.
                     */
                    if ($type === MediaType::Image) {
                        $newVariants =
                            $this
                                ->imageVariantService
                                ->generate(
                                    media: $media,

                                    actor: $actor,

                                    deleteOldPhysicalFiles: false,
                                );
                    }

                    app(
                        AuditLogger::class,
                    )->log(
                        event: 'media.replaced',

                        description: 'A media file was replaced.',

                        actor: $actor,

                        subject: $media,

                        oldValues: $oldAuditValues,

                        newValues: [
                            'original_name' => $originalName,

                            'mime_type' => $mimeType,

                            'extension' => $storageExtension,

                            'size_bytes' => $sizeBytes,

                            'width' => $width,

                            'height' => $height,

                            'checksum' => $checksum,

                            'variants_regenerated' => $type ===
                                MediaType::Image,
                        ],
                    );

                    return $media->refresh();
                },
            );
        } catch (Throwable $exception) {
            /*
             * SQL transaction has rolled back.
             *
             * Delete the new original because the
             * database still points to the old file.
             */
            $this->deletePhysicalFile(
                disk: $disk,
                path: $storedPath,
            );

            /*
             * If variant generation completed before a
             * later transaction failure, the variant DB
             * rows have also rolled back.
             *
             * Remove only those new physical files.
             */
            foreach (
                $newVariants as $variant
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
                    continue;
                }

                $this->deletePhysicalFile(
                    disk: $variantDisk,
                    path: $variantPath,
                );
            }

            throw $exception;
        }

        /*
         * Database + audit + new variants have committed.
         *
         * The new file set is now authoritative.
         * Old files can finally be removed.
         */
        foreach (
            $oldPhysicalFiles as $physicalFile
        ) {
            if (
                $physicalFile['disk'] === $disk
                && $physicalFile['path']
                    === $storedPath
            ) {
                continue;
            }

            $this->deletePhysicalFile(
                disk: $physicalFile['disk'],

                path: $physicalFile['path'],
            );
        }

        $replacedMedia->unsetRelation(
            'variants',
        );

        return $replacedMedia->refresh();
    }

    private function validateSize(
        MediaType $type,
        int $sizeBytes,
    ): void {
        if ($sizeBytes <= 0) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement file is empty.',
            ]);
        }

        $maximumKilobytes =
            $this->security
                ->maximumKilobytes(
                    $type,
                );

        if ($maximumKilobytes <= 0) {
            throw ValidationException::withMessages([
                'replacementFile' => 'Uploads for this media type are not configured.',
            ]);
        }

        $maximumBytes =
            $maximumKilobytes
            * 1024;

        if ($sizeBytes > $maximumBytes) {
            throw ValidationException::withMessages([
                'replacementFile' => sprintf(
                    'The replacement file may not be larger than %d KB.',
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
                'replacementFile' => 'The replacement file type could not be detected.',
            ]);
        }

        return strtolower(
            trim(
                $mimeType,
            ),
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
                'replacementFile' => 'The replacement image could not be decoded safely.',
            ]);
        }

        $width =
            $dimensions[0];

        $height =
            $dimensions[1];

        if (
            $width <= 0
            || $height <= 0
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => 'The replacement image dimensions are invalid.',
            ]);
        }

        $maximumWidth =
            $this->security
                ->maximumImageWidth();

        $maximumHeight =
            $this->security
                ->maximumImageHeight();

        if (
            $width > $maximumWidth
            || $height > $maximumHeight
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => sprintf(
                    'The image dimensions may not exceed %d × %d pixels.',
                    $maximumWidth,
                    $maximumHeight,
                ),
            ]);
        }

        $maximumPixels =
            $this->security
                ->maximumImagePixels();

        if (
            $width > intdiv(
                $maximumPixels,
                $height,
            )
        ) {
            throw ValidationException::withMessages([
                'replacementFile' => sprintf(
                    'The image may not exceed %d total pixels.',
                    $maximumPixels,
                ),
            ]);
        }

        return [
            $width,
            $height,
        ];
    }

    private function approvedDisk(
        MediaAsset $media,
    ): string {
        $disk = $this->requiredString(
            $media->getAttribute(
                'disk',
            ),
            'The media storage disk is missing.',
        );

        $publicDisk = config(
            'media.disks.public',
        );

        $privateDisk = config(
            'media.disks.private',
        );

        $approvedDisks = [];

        foreach (
            [
                $publicDisk,
                $privateDisk,
            ] as $configuredDisk
        ) {
            if (
                ! is_string($configuredDisk)
                || trim($configuredDisk) === ''
            ) {
                continue;
            }

            $approvedDisks[] =
                trim(
                    $configuredDisk,
                );
        }

        if (
            ! in_array(
                $disk,
                $approvedDisks,
                true,
            )
        ) {
            throw new RuntimeException(
                'The media storage disk is not approved.',
            );
        }

        return $disk;
    }

    private function directoryFor(
        MediaAsset $media,
    ): string {
        $directory = $this->nullableString(
            $media->getAttribute(
                'directory',
            ),
        );

        if ($directory === null) {
            $path = $this->requiredString(
                $media->getAttribute(
                    'path',
                ),
                'The media storage path is missing.',
            );

            $directory = str_replace(
                '\\',
                '/',
                dirname(
                    $path,
                ),
            );
        }

        $directory = trim(
            str_replace(
                '\\',
                '/',
                $directory,
            ),
            '/',
        );

        if (
            $directory === ''
            || $directory === '.'
            || $directory === '..'
            || str_contains(
                '/'.$directory.'/',
                '/../',
            )
        ) {
            throw new RuntimeException(
                'The media storage directory is invalid.',
            );
        }

        return $directory;
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

        return 'replacement.'
            .$fallbackExtension;
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

    /**
     * @return list<array{
     *     disk: string,
     *     path: string
     * }>
     */
    private function oldPhysicalFiles(
        MediaAsset $media,
    ): array {
        /**
         * @var list<array{
         *     disk: string,
         *     path: string
         * }> $files
         */
        $files = [];

        $disk = $this->nullableString(
            $media->getAttribute(
                'disk',
            ),
        );

        $path = $this->nullableString(
            $media->getAttribute(
                'path',
            ),
        );

        if (
            $disk !== null
            && $path !== null
        ) {
            $files[] = [
                'disk' => $disk,
                'path' => $path,
            ];
        }

        $variants = $media
            ->variants()
            ->get();

        foreach ($variants as $variant) {
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
                continue;
            }

            $files[] = [
                'disk' => $variantDisk,
                'path' => $variantPath,
            ];
        }

        $unique = [];

        foreach ($files as $file) {
            $unique[
                $file['disk']
                .'|'
                .$file['path']
            ] = $file;
        }

        return array_values(
            $unique,
        );
    }

    private function deletePhysicalFile(
        string $disk,
        string $path,
    ): void {
        if (
            trim($disk) === ''
            || ! $this->safeStoragePath(
                $path,
            )
        ) {
            return;
        }

        try {
            Storage::disk(
                $disk,
            )->delete(
                $path,
            );
        } catch (Throwable) {
            /*
             * Database already points to the valid
             * replacement file set.
             *
             * Any remaining orphan can be removed by
             * maintenance tooling later.
             */
        }
    }

    private function safeStoragePath(
        string $path,
    ): bool {
        $path = str_replace(
            '\\',
            '/',
            trim(
                $path,
            ),
        );

        if (
            $path === ''
            || str_starts_with(
                $path,
                '/',
            )
            || str_contains(
                '/'.$path.'/',
                '/../',
            )
        ) {
            return false;
        }

        return true;
    }

    private function requiredString(
        mixed $value,
        string $message,
    ): string {
        if (! is_string($value)) {
            throw new RuntimeException(
                $message,
            );
        }

        $value = trim(
            $value,
        );

        if ($value === '') {
            throw new RuntimeException(
                $message,
            );
        }

        return $value;
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

    private function nullableInteger(
        mixed $value,
    ): ?int {
        return is_int($value)
            ? $value
            : null;
    }
}

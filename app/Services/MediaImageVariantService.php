<?php

namespace App\Services;

use App\Enums\MediaSource;
use App\Enums\MediaType;
use App\Enums\MediaVariantPreset;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class MediaImageVariantService
{
    /**
     * @return list<MediaVariant>
     */
    public function generate(
        MediaAsset $media,
        ?User $actor = null,
    ): array {
        $this->assertProcessable(
            $media,
        );

        $disk = $this->requiredString(
            $media->getAttribute(
                'disk',
            ),
            'The media storage disk is missing.',
        );

        $path = $this->requiredString(
            $media->getAttribute(
                'path',
            ),
            'The media storage path is missing.',
        );

        $uuid = $this->requiredString(
            $media->getAttribute(
                'uuid',
            ),
            'The media UUID is missing.',
        );

        $storage = Storage::disk(
            $disk,
        );

        if (! $storage->exists($path)) {
            throw new RuntimeException(
                'The original media image could not be found.',
            );
        }

        $originalBytes = $storage->get(
            $path,
        );

        if ($originalBytes === '') {
            throw new RuntimeException(
                'The original media image is empty.',
            );
        }

        /*
         * Keep previous variant records/files until
         * the replacement variants are fully generated
         * and their database records are committed.
         */
        $oldVariants = $media
            ->variants()
            ->get();

        $oldVariantCount =
            $oldVariants->count();

        /**
         * @var list<string> $newPaths
         */
        $newPaths = [];

        /**
         * @var array<string, array{
         *     disk: string,
         *     path: string,
         *     mime_type: string,
         *     extension: string,
         *     width: int,
         *     height: int,
         *     size_bytes: int,
         *     checksum: string
         * }> $generated
         */
        $generated = [];

        try {
            foreach (
                MediaVariantPreset::cases() as $preset
            ) {
                /*
                 * Always process from the original.
                 *
                 * orient() applies EXIF orientation.
                 *
                 * scale() keeps the aspect ratio and
                 * does not upscale smaller images.
                 */
                $image = Image::fromBytes(
                    $originalBytes,
                )
                    ->orient()
                    ->scale(
                        width: $preset
                            ->maximumWidth(),

                        height: $preset
                            ->maximumHeight(),
                    )
                    ->toWebp()
                    ->quality(
                        $preset->quality(),
                    );

                $variantBytes =
                    $image->toBytes();

                if ($variantBytes === '') {
                    throw new RuntimeException(
                        'An image variant could not be encoded.',
                    );
                }

                [
                    $width,
                    $height,
                ] = $image->dimensions();

                if (
                    $width <= 0
                    || $height <= 0
                ) {
                    throw new RuntimeException(
                        'Generated image dimensions are invalid.',
                    );
                }

                $variantDirectory =
                    $this->variantDirectory(
                        path: $path,
                        uuid: $uuid,
                    );

                /*
                 * Use a fresh physical filename for every
                 * generation. This keeps the old variant
                 * valid until the new DB record commits.
                 */
                $variantName =
                    $preset->value
                    .'-'
                    .Str::uuid()->toString()
                    .'.webp';

                $variantPath =
                    $variantDirectory
                    .'/'
                    .$variantName;

                $stored = $storage->put(
                    $variantPath,
                    $variantBytes,
                );

                if ($stored !== true) {
                    throw new RuntimeException(
                        'An image variant could not be stored.',
                    );
                }

                $newPaths[] =
                    $variantPath;

                $generated[
                    $preset->value
                ] = [
                    'disk' => $disk,

                    'path' => $variantPath,

                    'mime_type' => 'image/webp',

                    'extension' => 'webp',

                    'width' => $width,

                    'height' => $height,

                    'size_bytes' => strlen(
                        $variantBytes,
                    ),

                    'checksum' => hash(
                        'sha256',
                        $variantBytes,
                    ),
                ];
            }

            $variants = DB::transaction(
                function () use (
                    $media,
                    $generated,
                    $oldVariantCount,
                    $actor,
                ): array {
                    /**
                     * @var list<MediaVariant> $saved
                     */
                    $saved = [];

                    foreach (
                        MediaVariantPreset::cases() as $preset
                    ) {
                        $data =
                            $generated[
                                $preset->value
                            ];

                        $variant =
                            MediaVariant::query()
                                ->updateOrCreate(
                                    [
                                        'media_asset_id' => $media->id,

                                        'name' => $preset->value,
                                    ],
                                    [
                                        'disk' => $data['disk'],

                                        'path' => $data['path'],

                                        'mime_type' => $data[
                                                'mime_type'
                                            ],

                                        'extension' => $data[
                                                'extension'
                                            ],

                                        'width' => $data['width'],

                                        'height' => $data['height'],

                                        'size_bytes' => $data[
                                                'size_bytes'
                                            ],

                                        'checksum' => $data[
                                                'checksum'
                                            ],

                                        'generated_at' => now(),
                                    ],
                                );

                        $saved[] =
                            $variant;
                    }

                    app(
                        AuditLogger::class,
                    )->log(
                        event: 'media.variants.generated',

                        description: 'Image variants were generated.',

                        actor: $actor,

                        subject: $media,

                        oldValues: [
                            'variants_count' => $oldVariantCount,
                        ],

                        newValues: [
                            'variants' => [
                                MediaVariantPreset::Thumbnail
                                    ->value,

                                MediaVariantPreset::Medium
                                    ->value,
                            ],

                            'variants_count' => count(
                                $saved,
                            ),
                        ],
                    );

                    return $saved;
                },
            );
        } catch (Throwable $exception) {
            /*
             * Database generation did not fully succeed.
             *
             * Remove only newly generated physical files.
             * Existing variant records/files remain valid.
             */
            foreach (
                $newPaths as $newPath
            ) {
                try {
                    $storage->delete(
                        $newPath,
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
         * New DB records are now authoritative.
         * Previous physical variant files can be removed.
         */
        foreach (
            $oldVariants as $oldVariant
        ) {
            $oldDisk =
                $oldVariant->getAttribute(
                    'disk',
                );

            $oldPath =
                $oldVariant->getAttribute(
                    'path',
                );

            if (
                ! is_string($oldDisk)
                || trim($oldDisk) === ''
                || ! is_string($oldPath)
                || trim($oldPath) === ''
                || in_array(
                    $oldPath,
                    $newPaths,
                    true,
                )
            ) {
                continue;
            }

            try {
                Storage::disk(
                    $oldDisk,
                )->delete(
                    $oldPath,
                );
            } catch (Throwable) {
                /*
                 * The database already points to the
                 * valid replacement variants.
                 *
                 * Any old orphan can be cleaned later.
                 */
            }
        }

        return $variants;
    }

    /**
     * Remove all generated physical variant files
     * belonging to a media asset.
     *
     * Used when image upload processing fails after
     * variant files have already been generated.
     */
    public function cleanupPhysicalFiles(
        MediaAsset $media,
    ): void {
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

        $uuid = $this->nullableString(
            $media->getAttribute(
                'uuid',
            ),
        );

        if (
            $disk === null
            || $path === null
            || $uuid === null
        ) {
            return;
        }

        try {
            Storage::disk(
                $disk,
            )->deleteDirectory(
                $this->variantDirectory(
                    path: $path,
                    uuid: $uuid,
                ),
            );
        } catch (Throwable) {
            /*
             * Best-effort cleanup.
             *
             * Preserve the original operation failure.
             */
        }
    }

    private function assertProcessable(
        MediaAsset $media,
    ): void {
        $type = $media->getAttribute(
            'type',
        );

        if (
            ! $type instanceof MediaType
            || $type !== MediaType::Image
        ) {
            throw new RuntimeException(
                'Only image media can generate image variants.',
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
                'Only uploaded images can generate local variants.',
            );
        }
    }

    private function variantDirectory(
        string $path,
        string $uuid,
    ): string {
        $directory = str_replace(
            '\\',
            '/',
            dirname(
                $path,
            ),
        );

        $directory = trim(
            $directory,
            '/',
        );

        if (
            $directory === ''
            || $directory === '.'
        ) {
            throw new RuntimeException(
                'The original media directory is invalid.',
            );
        }

        return $directory
            .'/variants/'
            .$uuid;
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
}

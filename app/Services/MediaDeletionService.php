<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class MediaDeletionService
{
    public function delete(
        MediaAsset $media,
        User $actor,
    ): MediaAsset {
        Gate::forUser($actor)->authorize(
            'media.delete',
        );

        if ($media->trashed()) {
            throw new RuntimeException(
                'This media asset is already in the trash.',
            );
        }

        DB::transaction(
            function () use (
                $media,
            ): void {
                app(
                    AuditLogger::class,
                )->log(
                    event: 'media.deleted',

                    description: 'Media asset was moved to trash.',

                    subject: $media,

                    oldValues: [
                        'deleted' => false,
                    ],

                    newValues: [
                        'deleted' => true,
                    ],
                );

                $media->delete();
            },
        );

        return $media->refresh();
    }

    public function restore(
        MediaAsset $media,
        User $actor,
    ): MediaAsset {
        Gate::forUser($actor)->authorize(
            'media.delete',
        );

        if (! $media->trashed()) {
            throw new RuntimeException(
                'This media asset is not in the trash.',
            );
        }

        DB::transaction(
            function () use (
                $media,
            ): void {
                $restored = $media->restore();

                if ($restored !== true) {
                    throw new RuntimeException(
                        'The media asset could not be restored.',
                    );
                }

                app(
                    AuditLogger::class,
                )->log(
                    event: 'media.restored',

                    description: 'Media asset was restored from trash.',

                    subject: $media,

                    oldValues: [
                        'deleted' => true,
                    ],

                    newValues: [
                        'deleted' => false,
                    ],
                );
            },
        );

        return $media->refresh();
    }

    public function forceDelete(
        MediaAsset $media,
        User $actor,
    ): void {
        Gate::forUser($actor)->authorize(
            'media.delete',
        );

        if (! $media->trashed()) {
            throw new RuntimeException(
                'Only trashed media may be permanently deleted.',
            );
        }

        /*
         * Load variants before the database rows
         * are permanently removed.
         */
        $media->loadMissing(
            'variants',
        );

        $files = $this->physicalFiles(
            $media,
        );

        $mediaId = $media->getKey();

        DB::transaction(
            function () use (
                $media,
                $files,
            ): void {
                app(
                    AuditLogger::class,
                )->log(
                    event: 'media.force_deleted',

                    description: 'Media asset was permanently deleted.',

                    subject: $media,

                    oldValues: [
                        'deleted' => true,

                        'files_count' => count($files),

                        'variants_count' => $media->variants->count(),
                    ],

                    newValues: [
                        'permanently_deleted' => true,
                    ],
                );

                /*
                 * media_variants has an FK with
                 * cascade delete, therefore
                 * force-deleting the parent removes
                 * the variant database records.
                 */
                $deleted =
                    $media->forceDelete();

                if ($deleted !== true) {
                    throw new RuntimeException(
                        'The media asset could not be permanently deleted.',
                    );
                }
            },
        );

        /*
         * Database deletion is authoritative.
         *
         * Physical cleanup happens only AFTER the
         * transaction commits. If storage cleanup
         * encounters a filesystem problem, we prefer
         * an orphaned file over a database record
         * pointing to a file that no longer exists.
         */
        $this->deletePhysicalFiles(
            $files,
        );

        /*
         * Defensive check only.
         */
        if (
            MediaAsset::withTrashed()
                ->whereKey($mediaId)
                ->exists()
        ) {
            throw new RuntimeException(
                'Permanent media deletion did not complete.',
            );
        }
    }

    /**
     * @return list<array{
     *     disk: string,
     *     path: string
     * }>
     */
    private function physicalFiles(
        MediaAsset $media,
    ): array {
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

        foreach (
            $media->variants as $variant
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

            $files[] = [
                'disk' => $variantDisk,

                'path' => $variantPath,
            ];
        }

        /*
         * Prevent duplicate physical delete calls
         * if malformed records happen to point at
         * the same file.
         */
        $unique = [];

        foreach ($files as $file) {
            $key =
                $file['disk']
                .'|'
                .$file['path'];

            $unique[$key] =
                $file;
        }

        return array_values(
            $unique,
        );
    }

    /**
     * @param list<array{
     *     disk: string,
     *     path: string
     * }> $files
     */
    private function deletePhysicalFiles(
        array $files,
    ): void {
        foreach ($files as $file) {
            try {
                Storage::disk(
                    $file['disk'],
                )->delete(
                    $file['path'],
                );
            } catch (Throwable) {
                /*
                 * Do not undo a successful permanent
                 * database deletion because of an
                 * orphaned physical file.
                 *
                 * A maintenance cleanup tool can
                 * remove storage orphans later.
                 */
            }
        }
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
}

<?php

namespace App\Services;

use App\Enums\MediaSource;
use App\Enums\MediaVariantPreset;
use App\Enums\MediaVisibility;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;

final class MediaUrlService
{
    public function original(
        MediaAsset $media,
    ): ?string {
        if (! $this->isPublicUpload($media)) {
            return null;
        }

        $disk = $this->stringValue(
            $media->getAttribute('disk'),
        );

        $path = $this->stringValue(
            $media->getAttribute('path'),
        );

        if (
            $disk === null
            || $path === null
        ) {
            return null;
        }

        return Storage::disk($disk)
            ->url($path);
    }

    public function variant(
        MediaAsset $media,
        MediaVariantPreset $preset,
    ): ?string {
        if (! $this->isPublicUpload($media)) {
            return null;
        }

        $variant = $this->findVariant(
            $media,
            $preset,
        );

        if (! $variant instanceof MediaVariant) {
            return null;
        }

        $disk = $this->stringValue(
            $variant->getAttribute('disk'),
        );

        $path = $this->stringValue(
            $variant->getAttribute('path'),
        );

        if (
            $disk === null
            || $path === null
        ) {
            return null;
        }

        /*
         * Defense-in-depth:
         * public URLs must never be generated
         * for files living on the private disk.
         */
        $publicDisk = config(
            'media.disks.public',
            'public',
        );

        if (
            ! is_string($publicDisk)
            || $disk !== trim($publicDisk)
        ) {
            return null;
        }

        return Storage::disk($disk)
            ->url($path);
    }

    public function thumbnail(
        MediaAsset $media,
    ): ?string {
        return $this->variant(
            $media,
            MediaVariantPreset::Thumbnail,
        );
    }

    public function medium(
        MediaAsset $media,
    ): ?string {
        return $this->variant(
            $media,
            MediaVariantPreset::Medium,
        );
    }

    public function thumbnailOrOriginal(
        MediaAsset $media,
    ): ?string {
        return $this->thumbnail($media)
            ?? $this->original($media);
    }

    public function mediumOrOriginal(
        MediaAsset $media,
    ): ?string {
        return $this->medium($media)
            ?? $this->original($media);
    }

    private function findVariant(
        MediaAsset $media,
        MediaVariantPreset $preset,
    ): ?MediaVariant {
        if ($media->relationLoaded('variants')) {
            foreach ($media->variants as $variant) {
                $name = $variant->getAttribute(
                    'name',
                );

                if (
                    $name instanceof MediaVariantPreset
                    && $name === $preset
                ) {
                    return $variant;
                }
            }

            return null;
        }

        return $media->variant(
            $preset,
        );
    }

    private function isPublicUpload(
        MediaAsset $media,
    ): bool {
        $visibility = $media->getAttribute(
            'visibility',
        );

        $source = $media->getAttribute(
            'source',
        );

        return $visibility
            instanceof MediaVisibility
            && $visibility ===
            MediaVisibility::Public
            && $source
            instanceof MediaSource
            && $source ===
            MediaSource::Upload;
    }

    private function stringValue(
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
}

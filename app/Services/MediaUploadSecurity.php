<?php

namespace App\Services;

use App\Enums\MediaType;

final class MediaUploadSecurity
{
    public function uploadsAllowed(
        MediaType $type,
    ): bool {
        return config(
            "media.uploads.{$type->value}.enabled",
            false,
        ) === true;
    }

    public function maximumKilobytes(
        MediaType $type,
    ): int {
        $value = config(
            "media.uploads.{$type->value}.max_kb",
            0,
        );

        return is_int($value)
            ? max(0, $value)
            : 0;
    }

    /**
     * @return array<string, list<string>>
     */
    public function mimeExtensionMap(
        MediaType $type,
    ): array {
        $configured = config(
            "media.uploads.{$type->value}.mime_extensions",
            [],
        );

        if (! is_array($configured)) {
            return [];
        }

        $result = [];

        foreach (
            $configured as $mimeType => $extensions
        ) {
            if (
                ! is_string($mimeType)
                || ! is_array($extensions)
            ) {
                continue;
            }

            $normalisedExtensions = [];

            foreach ($extensions as $extension) {
                if (! is_string($extension)) {
                    continue;
                }

                $extension = $this->normaliseExtension(
                    $extension,
                );

                if ($extension === '') {
                    continue;
                }

                $normalisedExtensions[] = $extension;
            }

            if ($normalisedExtensions === []) {
                continue;
            }

            $result[strtolower(trim($mimeType))] = array_values(
                array_unique(
                    $normalisedExtensions,
                ),
            );
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function allowedMimeTypes(
        MediaType $type,
    ): array {
        return array_keys(
            $this->mimeExtensionMap(
                $type,
            ),
        );
    }

    public function allowsMimeType(
        MediaType $type,
        string $mimeType,
    ): bool {
        if (! $this->uploadsAllowed($type)) {
            return false;
        }

        return array_key_exists(
            strtolower(
                trim($mimeType),
            ),
            $this->mimeExtensionMap(
                $type,
            ),
        );
    }

    public function allowsExtensionForMimeType(
        MediaType $type,
        string $mimeType,
        string $extension,
    ): bool {
        if (! $this->uploadsAllowed($type)) {
            return false;
        }

        $mimeType = strtolower(
            trim($mimeType),
        );

        $extension = $this->normaliseExtension(
            $extension,
        );

        $extensions = $this
            ->mimeExtensionMap($type)[$mimeType]
            ?? [];

        return in_array(
            $extension,
            $extensions,
            true,
        );
    }

    public function preferredExtensionForMimeType(
        MediaType $type,
        string $mimeType,
    ): ?string {
        $mimeType = strtolower(
            trim($mimeType),
        );

        $extensions = $this
            ->mimeExtensionMap($type)[$mimeType]
            ?? [];

        if ($extensions === []) {
            return null;
        }

        return $extensions[0];
    }

    public function isForbiddenExtension(
        string $extension,
    ): bool {
        $forbidden = config(
            'media.forbidden_extensions',
            [],
        );

        if (! is_array($forbidden)) {
            return true;
        }

        $extension = $this->normaliseExtension(
            $extension,
        );

        if ($extension === '') {
            return true;
        }

        foreach ($forbidden as $value) {
            if (
                is_string($value)
                && $this->normaliseExtension($value)
                === $extension
            ) {
                return true;
            }
        }

        return false;
    }

    private function normaliseExtension(
        string $extension,
    ): string {
        return strtolower(
            ltrim(
                trim($extension),
                '.',
            ),
        );
    }

    public function maximumImageWidth(): int
    {
        return $this->positiveIntegerConfig(
            'media.image_processing.max_width',
            8000,
        );
    }

    public function maximumImageHeight(): int
    {
        return $this->positiveIntegerConfig(
            'media.image_processing.max_height',
            8000,
        );
    }

    public function maximumImagePixels(): int
    {
        return $this->positiveIntegerConfig(
            'media.image_processing.max_pixels',
            24000000,
        );
    }

    private function positiveIntegerConfig(
        string $key,
        int $default,
    ): int {
        $value = config(
            $key,
            $default,
        );

        if (
            ! is_int($value)
            || $value < 1
        ) {
            return $default;
        }

        return $value;
    }
}

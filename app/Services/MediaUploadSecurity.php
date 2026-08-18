<?php

namespace App\Services;

use App\Enums\MediaType;

final class MediaUploadSecurity
{
    public function uploadsAllowed(
        MediaType $type,
    ): bool {
        $enabled = config(
            "media.uploads.{$type->value}.enabled",
            false,
        );

        return $enabled === true;
    }

    public function maximumKilobytes(
        MediaType $type,
    ): int {
        $value = config(
            "media.uploads.{$type->value}.max_kb",
            0,
        );

        if (! is_int($value)) {
            return 0;
        }

        return max(
            0,
            $value,
        );
    }

    /**
     * @return list<string>
     */
    public function allowedMimeTypes(
        MediaType $type,
    ): array {
        $mimeTypes = config(
            "media.uploads.{$type->value}.mime_types",
            [],
        );

        if (! is_array($mimeTypes)) {
            return [];
        }

        $result = [];

        foreach ($mimeTypes as $mimeType) {
            if (
                is_string($mimeType)
                && trim($mimeType) !== ''
            ) {
                $result[] = strtolower(
                    trim($mimeType),
                );
            }
        }

        return array_values(
            array_unique($result),
        );
    }

    public function allowsMimeType(
        MediaType $type,
        string $mimeType,
    ): bool {
        if (
            ! $this->uploadsAllowed(
                $type,
            )
        ) {
            return false;
        }

        return in_array(
            strtolower(
                trim($mimeType),
            ),
            $this->allowedMimeTypes(
                $type,
            ),
            true,
        );
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

        $normalised = strtolower(
            ltrim(
                trim($extension),
                '.',
            ),
        );

        if ($normalised === '') {
            return true;
        }

        foreach ($forbidden as $value) {
            if (
                is_string($value)
                && strtolower(
                    trim($value),
                ) === $normalised
            ) {
                return true;
            }
        }

        return false;
    }
}

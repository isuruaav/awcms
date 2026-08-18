<?php

namespace App\Services;

use App\Enums\PageBlockType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PageBlockSanitizer
{
    public const MAX_BLOCKS = 100;

    public function __construct(
        private readonly ContentSanitizer $contentSanitizer,
    ) {}

    /**
     * @param  array<array-key, mixed>|null  $blocks
     * @return list<array{
     *     id: string,
     *     type: string,
     *     data: array<string, mixed>
     * }>
     */
    public function normalize(
        ?array $blocks,
    ): array {
        if ($blocks === null || $blocks === []) {
            return [];
        }

        if (count($blocks) > self::MAX_BLOCKS) {
            throw ValidationException::withMessages([
                'blocks' => sprintf(
                    'A page may contain a maximum of %d blocks.',
                    self::MAX_BLOCKS,
                ),
            ]);
        }

        $normalized = [];

        foreach (
            array_values($blocks) as $index => $block
        ) {
            if (! is_array($block)) {
                throw $this->invalidBlock(
                    $index,
                    'Invalid block structure.',
                );
            }

            $typeValue = $block['type'] ?? null;

            if (! is_string($typeValue)) {
                throw $this->invalidBlock(
                    $index,
                    'The block type is missing.',
                );
            }

            $type = PageBlockType::tryFrom(
                $typeValue,
            );

            if (! $type instanceof PageBlockType) {
                throw $this->invalidBlock(
                    $index,
                    'Unsupported block type.',
                );
            }

            $data = $block['data'] ?? [];

            if (! is_array($data)) {
                throw $this->invalidBlock(
                    $index,
                    'Invalid block data.',
                );
            }

            $normalized[] = [
                'id' => $this->blockId(
                    $block['id'] ?? null,
                ),

                'type' => $type->value,

                'data' => $this->normalizeData(
                    type: $type,
                    data: $data,
                    index: $index,
                ),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeData(
        PageBlockType $type,
        array $data,
        int $index,
    ): array {
        return match ($type) {
            PageBlockType::Heading => $this->headingData(
                $data,
            ),

            PageBlockType::Text => $this->textData(
                $data,
            ),

            PageBlockType::Image => $this->imageData(
                $data,
                $index,
            ),

            PageBlockType::Button => $this->buttonData(
                $data,
                $index,
            ),

            PageBlockType::TwoColumns => $this->twoColumnsData(
                $data,
            ),

            PageBlockType::Callout => $this->calloutData(
                $data,
            ),

            PageBlockType::Divider => [],

            PageBlockType::Spacer => $this->spacerData(
                $data,
            ),
        };
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function headingData(
        array $data,
    ): array {
        $level = $this->stringValue(
            $data['level'] ?? null,
        );

        if (
            ! in_array(
                $level,
                ['h2', 'h3', 'h4'],
                true,
            )
        ) {
            $level = 'h2';
        }

        return [
            'level' => $level,

            'text' => $this->plainText(
                $data['text'] ?? null,
                255,
            ),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function textData(
        array $data,
    ): array {
        return [
            'content' => $this->richText(
                $data['content'] ?? null,
            ),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function imageData(
        array $data,
        int $index,
    ): array {
        $source = $this->imageUrl(
            $data['src'] ?? null,
        );

        if (
            $source === ''
            && $this->stringValue(
                $data['src'] ?? null,
            ) !== ''
        ) {
            throw $this->invalidBlock(
                $index,
                'The image URL is invalid.',
            );
        }

        $alignment = $this->stringValue(
            $data['alignment'] ?? null,
        );

        if (
            ! in_array(
                $alignment,
                [
                    'left',
                    'center',
                    'right',
                    'full',
                ],
                true,
            )
        ) {
            $alignment = 'center';
        }

        return [
            'src' => $source,

            'alt' => $this->plainText(
                $data['alt'] ?? null,
                160,
            ),

            'caption' => $this->plainText(
                $data['caption'] ?? null,
                300,
            ),

            'alignment' => $alignment,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function buttonData(
        array $data,
        int $index,
    ): array {
        $url = $this->linkUrl(
            $data['url'] ?? null,
        );

        if (
            $url === ''
            && $this->stringValue(
                $data['url'] ?? null,
            ) !== ''
        ) {
            throw $this->invalidBlock(
                $index,
                'The button URL is invalid.',
            );
        }

        $target = $this->stringValue(
            $data['target'] ?? null,
        );

        if (
            ! in_array(
                $target,
                [
                    '_self',
                    '_blank',
                ],
                true,
            )
        ) {
            $target = '_self';
        }

        $style = $this->stringValue(
            $data['style'] ?? null,
        );

        if (
            ! in_array(
                $style,
                [
                    'primary',
                    'secondary',
                    'outline',
                ],
                true,
            )
        ) {
            $style = 'primary';
        }

        return [
            'label' => $this->plainText(
                $data['label'] ?? null,
                80,
            ),

            'url' => $url,

            'target' => $target,

            'style' => $style,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function twoColumnsData(
        array $data,
    ): array {
        $ratio = $this->stringValue(
            $data['ratio'] ?? null,
        );

        if (
            ! in_array(
                $ratio,
                [
                    '50-50',
                    '40-60',
                    '60-40',
                ],
                true,
            )
        ) {
            $ratio = '50-50';
        }

        return [
            'ratio' => $ratio,

            'left' => $this->richText(
                $data['left'] ?? null,
            ),

            'right' => $this->richText(
                $data['right'] ?? null,
            ),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function calloutData(
        array $data,
    ): array {
        $style = $this->stringValue(
            $data['style'] ?? null,
        );

        if (
            ! in_array(
                $style,
                [
                    'info',
                    'success',
                    'warning',
                    'neutral',
                ],
                true,
            )
        ) {
            $style = 'info';
        }

        return [
            'title' => $this->plainText(
                $data['title'] ?? null,
                120,
            ),

            'content' => $this->richText(
                $data['content'] ?? null,
            ),

            'style' => $style,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function spacerData(
        array $data,
    ): array {
        $size = $this->stringValue(
            $data['size'] ?? null,
        );

        if (
            ! in_array(
                $size,
                [
                    'small',
                    'medium',
                    'large',
                ],
                true,
            )
        ) {
            $size = 'medium';
        }

        return [
            'size' => $size,
        ];
    }

    private function blockId(
        mixed $value,
    ): string {
        if (is_string($value)) {
            $value = trim(
                $value,
            );

            if (
                preg_match(
                    '/^[A-Za-z0-9_-]{8,64}$/',
                    $value,
                ) === 1
            ) {
                return $value;
            }
        }

        return Str::uuid()->toString();
    }

    private function richText(
        mixed $value,
    ): string {
        if (! is_string($value)) {
            return '';
        }

        return $this->contentSanitizer
            ->sanitize(
                $value,
            );
    }

    private function plainText(
        mixed $value,
        int $maximumLength,
    ): string {
        if (! is_string($value)) {
            return '';
        }

        return $this->contentSanitizer
            ->plainText(
                $value,
                $maximumLength,
            );
    }

    private function stringValue(
        mixed $value,
    ): string {
        return is_string($value)
            ? trim($value)
            : '';
    }

    private function imageUrl(
        mixed $value,
    ): string {
        $url = $this->stringValue(
            $value,
        );

        if ($url === '') {
            return '';
        }

        if (
            $this->isSafeRelativeUrl(
                $url,
            )
        ) {
            return $url;
        }

        return $this->isHttpUrl(
            $url,
        )
            ? $url
            : '';
    }

    private function linkUrl(
        mixed $value,
    ): string {
        $url = $this->stringValue(
            $value,
        );

        if ($url === '') {
            return '';
        }

        if (
            str_starts_with(
                $url,
                '#',
            )
            && preg_match(
                '/^#[A-Za-z0-9_-]+$/',
                $url,
            ) === 1
        ) {
            return $url;
        }

        if (
            $this->isSafeRelativeUrl(
                $url,
            )
        ) {
            return $url;
        }

        if ($this->isHttpUrl($url)) {
            return $url;
        }

        $scheme = parse_url(
            $url,
            PHP_URL_SCHEME,
        );

        if (! is_string($scheme)) {
            return '';
        }

        return in_array(
            strtolower($scheme),
            [
                'mailto',
                'tel',
            ],
            true,
        )
            ? $url
            : '';
    }

    private function isSafeRelativeUrl(
        string $url,
    ): bool {
        if (
            ! str_starts_with(
                $url,
                '/',
            )
        ) {
            return false;
        }

        /*
         * Prevent protocol-relative URLs such as:
         * //attacker.example
         */
        if (
            str_starts_with(
                $url,
                '//',
            )
        ) {
            return false;
        }

        return ! $this->containsControlCharacters(
            $url,
        );
    }

    private function isHttpUrl(
        string $url,
    ): bool {
        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL,
            ) === false
        ) {
            return false;
        }

        $scheme = parse_url(
            $url,
            PHP_URL_SCHEME,
        );

        if (! is_string($scheme)) {
            return false;
        }

        return in_array(
            strtolower($scheme),
            [
                'http',
                'https',
            ],
            true,
        );
    }

    private function containsControlCharacters(
        string $value,
    ): bool {
        return preg_match(
            '/[\x00-\x1F\x7F]/',
            $value,
        ) === 1;
    }

    private function invalidBlock(
        int $index,
        string $message,
    ): ValidationException {
        return ValidationException::withMessages([
            "blocks.{$index}" => $message,
        ]);
    }
}

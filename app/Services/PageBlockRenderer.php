<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Validation\ValidationException;

final class PageBlockRenderer
{
    public function __construct(
        private readonly PageBlockSanitizer $sanitizer,
    ) {}

    /**
     * Prepare page blocks for safe public rendering.
     *
     * Invalid legacy/corrupted blocks are ignored rather
     * than causing the entire public page to fail.
     *
     * @return list<array{
     *     id: string,
     *     type: string,
     *     data: array<string, mixed>
     * }>
     */
    public function forPage(
        Page $page,
    ): array {
        $blocks = $page->getAttribute(
            'blocks',
        );

        if (! is_array($blocks)) {
            return [];
        }

        $blocks = array_slice(
            array_values($blocks),
            0,
            PageBlockSanitizer::MAX_BLOCKS,
        );

        $safeBlocks = [];

        foreach ($blocks as $block) {
            try {
                $normalized = $this->sanitizer
                    ->normalize([
                        $block,
                    ]);
            } catch (ValidationException) {
                /*
                 * Fail closed for malformed or unsafe
                 * legacy block data.
                 */
                continue;
            }

            if ($normalized === []) {
                continue;
            }

            $safeBlocks[] = $normalized[0];
        }

        return $safeBlocks;
    }
}

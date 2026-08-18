<?php

namespace App\Support;

use App\Enums\PageBlockType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PageBlockFactory
{
    /**
     * @return array{
     *     id: string,
     *     type: string,
     *     data: array<string, mixed>
     * }
     */
    public static function make(
        string $type,
    ): array {
        $blockType = PageBlockType::tryFrom(
            $type,
        );

        if (! $blockType instanceof PageBlockType) {
            throw ValidationException::withMessages([
                'blocks' => 'Unsupported page block type.',
            ]);
        }

        return [
            'id' => Str::uuid()->toString(),

            'type' => $blockType->value,

            'data' => match ($blockType) {
                PageBlockType::Heading => [
                    'level' => 'h2',
                    'text' => '',
                ],

                PageBlockType::Text => [
                    'content' => '',
                ],

                PageBlockType::Image => [
                    'src' => '',
                    'alt' => '',
                    'caption' => '',
                    'alignment' => 'center',
                ],

                PageBlockType::Button => [
                    'label' => '',
                    'url' => '',
                    'target' => '_self',
                    'style' => 'primary',
                ],

                PageBlockType::TwoColumns => [
                    'ratio' => '50-50',
                    'left' => '',
                    'right' => '',
                ],

                PageBlockType::Callout => [
                    'title' => '',
                    'content' => '',
                    'style' => 'info',
                ],

                PageBlockType::Divider => [],

                PageBlockType::Spacer => [
                    'size' => 'medium',
                ],
            },
        ];
    }
}

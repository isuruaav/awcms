<?php

namespace App\Services;

use InvalidArgumentException;

final class PageCss
{
    public const MAX_LENGTH = 50000;

    public function error(?string $css): ?string
    {
        try {
            $this->compile($css, 'cms-page-validation');

            return null;
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }
    }

    /** Fail closed when reading invalid CSS from an old or manually edited record. */
    public function forPage(?string $css, int $pageId): string
    {
        try {
            return $this->compile($css, 'cms-page-'.$pageId);
        } catch (InvalidArgumentException) {
            return '';
        }
    }

    private function compile(?string $css, string $id): string
    {
        $css = trim($css ?? '');

        if (mb_strlen($css) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Custom CSS may not exceed 50,000 characters.');
        }

        if (preg_match('~</?[a-z!]~i', $css) === 1 || str_contains($css, "\0")) {
            throw new InvalidArgumentException('Enter CSS only, without HTML or <style> tags.');
        }

        // Decode escapes for the security check, while preserving the original source.
        $uncommented = preg_replace('~/\*.*?\*/~s', '', $css) ?? $css;
        $continued = preg_replace('/\\\\(?:\r\n|[\n\r\f])/', '', $uncommented) ?? $uncommented;
        $decoded = preg_replace_callback(
            '/\\\\(?:([0-9a-f]{1,6})[ \t\r\n\f]?|(.))/iu',
            static fn (array $match): string => $match[1] !== ''
                ? mb_chr(min((int) hexdec($match[1]), 0x10FFFF), 'UTF-8')
                : $match[2],
            $continued,
        ) ?? $css;

        if (preg_match('~expression\s*\(|(?:java|vb)script\s*:|(?:behavior|-moz-binding)\s*:~i', $decoded) === 1) {
            throw new InvalidArgumentException('Custom CSS contains an unsupported active value.');
        }

        $offset = 0;

        // A low-specificity prefix keeps every selector inside this page's content.
        return $this->rules($css, $offset, ':where(#'.$id.')');
    }

    private function rules(string $css, int &$offset, string $scope, int $depth = 0): string
    {
        if ($depth > 10) {
            throw new InvalidArgumentException('Custom CSS has too many nested rules.');
        }

        $result = '';

        while ($offset < strlen($css)) {
            [$header, $delimiter] = $this->segment($css, $offset, '{};');
            $header = trim($header);

            if ($header === '' && $delimiter === null && $depth === 0) {
                return $result;
            }

            if ($header === '' && $delimiter === '}' && $depth > 0) {
                return $result;
            }

            if ($header === '' || $delimiter !== '{') {
                throw new InvalidArgumentException('Check CSS selectors and matching opening/closing braces.');
            }

            if (str_starts_with($header, '@')) {
                if (preg_match('/^@(media|supports|container)\s/i', $header) !== 1) {
                    throw new InvalidArgumentException('Custom CSS supports @media, @supports and @container rules. Other @rules are not supported.');
                }

                $result .= $header." {\n".$this->rules($css, $offset, $scope, $depth + 1)."}\n";

                continue;
            }

            $selectors = [];
            $selectorOffset = 0;

            do {
                [$selector, $separator] = $this->segment($header, $selectorOffset, ',');

                if (trim($selector) === '') {
                    throw new InvalidArgumentException('Custom CSS contains an empty selector.');
                }

                $selectors[] = $scope.' '.trim($selector);
            } while ($separator !== null);

            [$declarations, $end] = $this->segment($css, $offset, '{}');

            if ($end !== '}') {
                throw new InvalidArgumentException('Check CSS braces. Write selectors separately instead of nesting them inside another selector.');
            }

            $result .= implode(",\n", $selectors).' {'.$declarations."}\n";
        }

        if ($depth > 0) {
            throw new InvalidArgumentException('Custom CSS is missing a closing brace.');
        }

        return $result;
    }

    /**
     * Read a CSS segment without treating punctuation in comments, strings,
     * attribute selectors or functions as rule boundaries.
     *
     * @return array{string, ?string}
     */
    private function segment(string $css, int &$offset, string $delimiters): array
    {
        $text = '';
        $stack = [];
        $length = strlen($css);
        $quote = null;

        while ($offset < $length) {
            $character = $css[$offset++];

            if ($character === '\\') {
                if ($offset >= $length) {
                    throw new InvalidArgumentException('Custom CSS ends with an incomplete escape.');
                }

                $text .= $character.$css[$offset++];

                continue;
            }

            if ($quote !== null) {
                if (str_contains("\n\r\f", $character)) {
                    throw new InvalidArgumentException('CSS strings cannot contain an unescaped line break.');
                }

                if ($character === $quote) {
                    $quote = null;
                }

                $text .= $character;

                continue;
            }

            if ($character === '/' && ($css[$offset] ?? '') === '*') {
                $end = strpos($css, '*/', $offset + 1);

                if ($end === false) {
                    throw new InvalidArgumentException('Custom CSS has an unclosed comment.');
                }

                $offset = $end + 2;
                $text .= ' ';

                continue;
            }

            if ($character === '"' || $character === "'") {
                $quote = $character;
            } elseif ($character === '(' || $character === '[') {
                $stack[] = $character === '(' ? ')' : ']';
            } elseif ($character === ')' || $character === ']') {
                if (array_pop($stack) !== $character) {
                    throw new InvalidArgumentException('Check CSS parentheses and attribute selector brackets.');
                }
            } elseif ($stack !== [] && str_contains('{}', $character)) {
                throw new InvalidArgumentException('Unexpected CSS brace inside parentheses or brackets.');
            } elseif ($stack === [] && str_contains($delimiters, $character)) {
                return [$text, $character];
            }

            $text .= $character;
        }

        if ($quote !== null || $stack !== []) {
            throw new InvalidArgumentException('Check CSS quotes, parentheses and attribute selector brackets.');
        }

        return [$text, null];
    }
}

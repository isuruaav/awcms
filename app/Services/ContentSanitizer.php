<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;
use RuntimeException;

final class ContentSanitizer
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $cachePath = storage_path(
            'framework/cache/htmlpurifier',
        );

        if (
            ! is_dir($cachePath)
            && ! mkdir(
                $cachePath,
                0755,
                true,
            )
            && ! is_dir($cachePath)
        ) {
            throw new RuntimeException(
                'Unable to create the HTML Purifier cache directory.',
            );
        }

        $config = HTMLPurifier_Config::createDefault();

        $config->set(
            'Core.Encoding',
            'UTF-8',
        );

        /*
         * Only HTML elements required by the
         * AWCMS page editor are allowed.
         */
        $config->set(
            'HTML.Allowed',
            implode(
                ',',
                [
                    'div',
                    'p',
                    'br',
                    'h2',
                    'h3',
                    'h4',
                    'strong',
                    'b',
                    'em',
                    'i',
                    'u',
                    's',
                    'del',
                    'ul',
                    'ol',
                    'li',
                    'blockquote',
                    'pre',
                    'code',
                    'a[href|title]',
                ],
            ),
        );

        /*
         * Active and embedded content is explicitly forbidden.
         */
        $config->set(
            'HTML.ForbiddenElements',
            [
                'script',
                'style',
                'iframe',
                'frame',
                'frameset',
                'object',
                'embed',
                'applet',
                'form',
                'input',
                'button',
                'textarea',
                'select',
                'option',
                'link',
                'meta',
                'base',
                'svg',
                'math',
                'video',
                'audio',
                'source',
                'canvas',
            ],
        );

        $config->set(
            'HTML.ForbiddenAttributes',
            [
                'style',
                'class',
                'id',
                'onclick',
                'onerror',
                'onload',
                'onmouseover',
                'onfocus',
                'onblur',
            ],
        );

        /*
         * javascript:, data: and other unsafe
         * URL schemes are not allowed.
         */
        $config->set(
            'URI.AllowedSchemes',
            [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'tel' => true,
            ],
        );

        $config->set(
            'Attr.EnableID',
            false,
        );

        $config->set(
            'CSS.AllowedProperties',
            [],
        );

        $config->set(
            'AutoFormat.RemoveEmpty',
            false,
        );

        $config->set(
            'Cache.SerializerPath',
            $cachePath,
        );

        $this->purifier = new HTMLPurifier(
            $config,
        );
    }

    public function sanitize(?string $html): string
    {
        if (! is_string($html)) {
            return '';
        }

        $html = trim($html);

        if ($html === '') {
            return '';
        }

        /*
     * Trix may store manually pasted HTML tags as
     * encoded entities such as &lt;h2&gt;.
     *
     * Decode one layer only, then immediately pass
     * the result through HTML Purifier.
     */
        $decodedHtml = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        $cleanHtml = trim(
            $this->purifier->purify(
                $decodedHtml,
            ),
        );

        /*
     * Trix may represent an empty editor using
     * markup such as <div><br></div>.
     */
        if ($this->plainText($cleanHtml) === '') {
            return '';
        }

        return $cleanHtml;
    }

    public function plainText(
        ?string $value,
        ?int $maximumLength = null,
    ): string {
        if (! is_string($value)) {
            return '';
        }

        /*
         * Add spaces between block elements before
         * removing HTML tags.
         */
        $valueWithSpaces = preg_replace(
            '/<(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6]|\/blockquote)>/iu',
            ' ',
            $value,
        );

        if (! is_string($valueWithSpaces)) {
            $valueWithSpaces = $value;
        }

        $text = html_entity_decode(
            strip_tags($valueWithSpaces),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        $normalised = preg_replace(
            '/\s+/u',
            ' ',
            trim($text),
        );

        if (! is_string($normalised)) {
            return '';
        }

        if (
            is_int($maximumLength)
            && $maximumLength > 0
        ) {
            return Str::limit(
                $normalised,
                $maximumLength,
                '',
            );
        }

        return $normalised;
    }
}

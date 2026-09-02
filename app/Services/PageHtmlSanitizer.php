<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_AttrDef_Text;
use HTMLPurifier_Config;
use RuntimeException;

final class PageHtmlSanitizer
{
    private HTMLPurifier $htmlPurifier;

    private HTMLPurifier $visualPurifier;

    /**
     * @var list<string>
     */
    private array $allowedElements = [
        'div',
        'section',
        'main',
        'article',
        'header',
        'footer',
        'nav',
        'aside',
        'figure',
        'figcaption',
        'span',
        'p',
        'br',
        'hr',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'del',
        'small',
        'sub',
        'sup',
        'ul',
        'ol',
        'li',
        'blockquote',
        'pre',
        'code',
        'a',
        'img',
        'table',
        'thead',
        'tbody',
        'tfoot',
        'tr',
        'th',
        'td',
    ];

    public function __construct()
    {
        $cachePath = storage_path(
            'framework/cache/htmlpurifier-page',
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
                'Unable to create the page HTML Purifier cache directory.',
            );
        }

        $this->htmlPurifier = $this->makePurifier(
            cachePath: $cachePath,
            definitionId: 'awcms-page-tailwind-html',
            definitionRev: 5,
            allowVisualStyles: true,
        );

        $this->visualPurifier = $this->makePurifier(
            cachePath: $cachePath,
            definitionId: 'awcms-page-visual-html',
            definitionRev: 3,
            allowVisualStyles: true,
        );
    }

    /**
     * Sanitize developer-authored HTML + Tailwind content.
     *
     * Tailwind classes are preserved. A deliberately small set of safe
     * presentation styles is also allowed so Visual Editor formatting
     * survives switching to HTML mode: color, background-color and text-align.
     * The <style> element remains forbidden.
     */
    public function sanitize(?string $html): string
    {
        return $this->purify(
            $this->htmlPurifier,
            $html,
        );
    }

    /**
     * Sanitize Word-like visual editor content.
     *
     * Only the small inline-CSS subset required by the visual toolbar is
     * allowed: text colour, background colour and text alignment.
     */
    public function sanitizeVisual(?string $html): string
    {
        return $this->purify(
            $this->visualPurifier,
            $html,
        );
    }

    private function makePurifier(
        string $cachePath,
        string $definitionId,
        int $definitionRev,
        bool $allowVisualStyles,
    ): HTMLPurifier {
        $config = HTMLPurifier_Config::createDefault();

        $config->set(
            'Core.Encoding',
            'UTF-8',
        );

        $config->set(
            'Core.RemoveProcessingInstructions',
            true,
        );

        $config->set(
            'HTML.AllowedElements',
            $this->allowedElements,
        );

        $allowedAttributes = [
            '*.class',
            '*.id',
            '*.title',
            'a.href',
            'a.title',
            'img.src',
            'img.alt',
            'img.width',
            'img.height',
            'th.colspan',
            'th.rowspan',
            'td.colspan',
            'td.rowspan',
        ];

        if ($allowVisualStyles) {
            $allowedAttributes[] = '*.style';
        }

        $config->set(
            'HTML.AllowedAttributes',
            $allowedAttributes,
        );

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

        $forbiddenAttributes = [
            'onclick',
            'onerror',
            'onload',
            'onmouseover',
            'onfocus',
            'onblur',
            'onchange',
            'onsubmit',
            'onkeydown',
            'onkeyup',
            'onkeypress',
        ];

        if (! $allowVisualStyles) {
            $forbiddenAttributes[] = 'style';
        }

        $config->set(
            'HTML.ForbiddenAttributes',
            $forbiddenAttributes,
        );

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
            true,
        );

        $config->set(
            'CSS.AllowedProperties',
            $allowVisualStyles
                ? [
                    'color',
                    'background-color',
                    'text-align',
                ]
                : [],
        );

        $config->set(
            'AutoFormat.RemoveEmpty',
            false,
        );

        $config->set(
            'Cache.SerializerPath',
            $cachePath,
        );

        /*
         * HTML Purifier's default class attribute uses NMTOKENS, which is
         * stricter than Tailwind class syntax. A class value is inert here,
         * so permit it as text while HTML, URLs and event handlers remain
         * strongly sanitised.
         */
        $config->set(
            'HTML.DefinitionID',
            $definitionId,
        );

        $config->set(
            'HTML.DefinitionRev',
            $definitionRev,
        );

        $definition = $config->maybeGetRawHTMLDefinition();

        if ($definition !== null) {
            $html5BlockElements = [
                'section',
                'main',
                'article',
                'header',
                'footer',
                'nav',
                'aside',
                'figure',
                'figcaption',
            ];

            foreach ($html5BlockElements as $elementName) {
                if (isset($definition->info[$elementName])) {
                    continue;
                }

                $definition->addElement(
                    $elementName,
                    'Block',
                    'Flow',
                    'Common',
                );
            }

            foreach ($this->allowedElements as $elementName) {
                if (! isset($definition->info[$elementName])) {
                    continue;
                }

                $definition->info[$elementName]->attr['class'] =
                    new HTMLPurifier_AttrDef_Text;
            }
        }

        return new HTMLPurifier(
            $config,
        );
    }

    private function purify(
        HTMLPurifier $purifier,
        ?string $html,
    ): string {
        if (! is_string($html)) {
            return '';
        }

        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $cleanHtml = trim(
            $purifier->purify(
                $html,
            ),
        );

        if ($cleanHtml === '') {
            return '';
        }

        return $cleanHtml;
    }
}

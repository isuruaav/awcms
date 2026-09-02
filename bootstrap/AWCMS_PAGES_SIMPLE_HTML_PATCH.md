# AWCMS Pages — Simple HTML + Tailwind Editor Patch

## What changes

- Removes **SEO & Social Sharing** from the Page Create/Edit UI.
- Removes **Page Builder** from the Page Create/Edit UI.
- Replaces the rich-text editor with a large monospace HTML textarea.
- Page content accepts sanitized HTML and Tailwind utility classes.
- Keeps scripts, iframes, forms, inline styles and JavaScript event handlers blocked.
- Preserves legacy SEO fields and Page Builder backend/data for backward compatibility.
- Existing legacy page blocks can still render, but they are no longer editable from the Pages UI.
- Adds a Tailwind safelist source with common CMS utility classes.

## After copying the patch

Run:

```powershell
php artisan optimize:clear
npm run build
php artisan test tests\Feature\Admin\PageBuilderUiTest.php tests\Feature\PageHtmlContentTest.php tests\Feature\Admin\PagePreviewTest.php tests\Feature\PublicPageRenderingTest.php tests\Feature\PageBlockRenderingTest.php
composer types:check
vendor\bin\pint --test
```

If the targeted tests pass, run the full suite:

```powershell
php artisan test
```

## Tailwind classes entered through CMS content

Tailwind generates CSS from source files at build time, not from database records at request time. This patch registers `resources/tailwind/page-content-safelist.html` as an explicit Tailwind source and includes common utilities.

If you later need a Tailwind class that is not in the safelist:

1. Add the complete class name to `resources/tailwind/page-content-safelist.html`.
2. Run `npm run build`.

## Security

Allowed page HTML is sanitized before storage and again before display. The page editor does not allow active content such as scripts, iframes, forms, inline `style` attributes or JavaScript event handlers.

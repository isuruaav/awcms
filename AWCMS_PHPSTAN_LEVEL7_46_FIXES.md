# AWCMS PHPStan Level 7 — 46 Error Fix Patch

This patch addresses the 46 PHPStan/Larastan errors reported after the Pages-aligned multilingual News and multiple-image changes.

## Principles

- PHPStan/Larastan level is **not lowered**.
- `treatPhpDocTypesAsCertain` is **not disabled**.
- Eloquent enum-backed attributes are read safely using `getRawOriginal()` and converted with enum `tryFrom()` where PHPStan cannot reliably infer runtime casts.
- Published/Draft security checks are preserved.
- Manual English/Sinhala/Tamil support is preserved.
- Visual Editor + HTML/Tailwind behavior is preserved.
- Multiple News article images are preserved.
- English legacy URLs remain `/pages/{slug}` and `/news/{slug}`; Sinhala/Tamil remain localized.
- The public Pages controller keeps the Request-based route-parameter handling that fixed localized page 404s.

## Files

- app/Http/Controllers/Admin/NewsPreviewController.php
- app/Http/Controllers/PublicNewsController.php
- app/Http/Controllers/PublicPageController.php
- app/Livewire/Admin/News/NewsCreate.php
- app/Livewire/Admin/News/NewsEdit.php
- app/Livewire/Admin/Pages/PageCreate.php
- app/Livewire/Admin/Pages/PageEdit.php
- app/Models/MenuItem.php
- app/Services/NewsArticleService.php
- app/Services/NewsDeletionService.php
- app/Services/NewsImageService.php
- app/Services/NewsRevisionService.php
- app/Services/PageRevisionService.php
- app/Support/PageSeo.php

## Validation completed in patch workspace

All 14 PHP files pass `php -l` syntax validation.

Full PHPStan/Pest/Pint must be run in the user's AWCMS environment where `vendor/` is installed.

# AWCMS Multilingual Menu Builder Patch

This patch adds manual English, Sinhala and Tamil labels to the existing Menu Builder while preserving existing menu records, permissions, audit logging and hierarchy.

## Install

Copy the patch contents into the AWCMS project root, preserving the directory structure. Then run these commands from PowerShell:

```powershell
cd E:\wamp64\www\awcms

php artisan migrate
php artisan optimize:clear
php artisan test tests/Feature/Admin/MenuServiceTest.php tests/Feature/Admin/MenuTranslationServiceTest.php
vendor\bin\pint --test
composer types:check
```

Do not run `migrate:fresh`, `db:wipe` or `migrate:reset`.

The migration creates `menu_item_translations` and copies every existing menu item's label and custom URL into its English translation. Sinhala and Tamil fields use the English value as a frontend fallback until translations are entered.

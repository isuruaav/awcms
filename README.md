# AWCMS v1

Army Website Content Management System built with Laravel 13, Livewire 4, Blade, Tailwind CSS 4 and MySQL/SQLite.

## Included modules

- Authentication, email verification, password reset, 2FA/passkeys and active-account protection
- User management and role assignment
- Roles & Permissions management
- Pages, page builder, revisions, preview, SEO and publishing workflow
- News, categories, revisions and publishing workflow
- Media Library with upload security, metadata, variants, replacement and deletion controls
- Galleries with image ordering, cover handling and public gallery pages
- Documents/PDFs with categories, version history, restore, stable public links, view/download and publishing
- Menu Builder with nested items, content links, custom URLs, ordering and drag/drop assistance
- Site Settings with logo/favicon, contact details, commander/head profile, theme family, colours, SEO, social links, hero slides and maintenance mode
- Contact Messages
- Redirect Manager
- Audit Logs
- Dynamic public homepage, navigation and footer

## Local installation

```powershell
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

For the user's WAMP setup, update `.env` to the required MySQL database before running migrations.

## Required quality gates

The project is configured for Larastan/PHPStan **Level 7** in `phpstan.neon`.

Run these before deployment:

```powershell
php artisan optimize:clear
php artisan migrate --seed
php artisan test
composer types:check
vendor\bin\pint --test
npm run build
php artisan view:cache
php artisan route:list --except-vendor
```

Do not lower the PHPStan level or bypass failed tests to deploy.

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false` and the correct `APP_URL`.
- Use HTTPS and secure session cookies.
- Configure production mail, queue and cache drivers.
- Keep the web server document root pointed at `public/`.
- Ensure only `storage/` and `bootstrap/cache/` are writable by the web server.
- Run database/media backups before releases.
- Test backup restore regularly.
- Do not commit `.env`, passwords, mail credentials or API secrets.

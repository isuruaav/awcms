# Hero button link fix - 2026-10-21

Incremental update for the existing sch_of_slsc installation with multilingual hero slides and the School of Signals theme.

Fixes transparent hero overlays intercepting clicks and resolves local button URLs within the application's installation directory. Existing /page/ and /si/page/ links are resolved to the actual /pages/ and /si/pages/ routes. Already-prefixed local paths do not acquire the prefix twice. Full http/https URLs remain unchanged. Saved slide records are not rewritten.

The payload contains five files: app/Models/HeroSlide.php, both homepage views, the theme's source CSS, and its matching published CSS. No migration, composer install, npm build, or theme publishing is required. The previous Hero Slide modal update is separate. No .env, uploaded media, vendor/, node_modules/, tests, or runtime caches are included.

## Hosting server

Upload the ZIP. From the folder containing it, run as the application owner:

```bash
unzip sch_of_slsc-hero-button-link-fix-2026-10-21.zip -d sch-hero-link-fix
cd sch-hero-link-fix
bash deploy.sh /var/www/html/sch_of_slsc
```

Replace the project path if needed. The target must contain artisan, .env and vendor/. PHP CLI 8.3+, Bash, unzip, tar and sha256sum are required. The installer checks hashes, backs up replaced files under storage/app/deployment-backups/, briefly enters maintenance mode, copies the payload and clears Blade views. It preserves the previous file-based maintenance state.

Optional check without installing:

```bash
bash deploy.sh /var/www/html/sch_of_slsc --check
```

For a hosting-specific PHP CLI, set PHP_BIN when calling the script.

## Manual upload / Windows

Merge the contents of payload/ into the existing Laravel project root, preserving paths. Then run from that root:

```text
php artisan view:clear
```

Refresh with Ctrl+F5. Check both English and Sinhala hero buttons. New local page URLs can use /en/pages/aboutus and /si/pages/siaboutus. If installation stops, resolve the reported error before running php artisan up if the installer started maintenance mode. The terminal prints the backup path.

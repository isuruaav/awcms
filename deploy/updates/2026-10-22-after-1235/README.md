# Code changes after 12:35 - 22 October 2026

Cutoff: 2026-10-22 12:35:00, Asia/Colombo (UTC+05:30).
This incremental package contains 28 runtime files for the existing sch_of_slsc installation.

Included changes:

- Header/footer CSS saves preserve escaped icon characters and still reject blocked CSS constructs.
- Font Awesome 6 family aliases use the installed Font Awesome 7 files.
- Homepage telephone, email-provider chooser and map links, including English/Sinhala labels.
- Social icons are selected automatically from the platform saved in Site Settings.
- Contact Messages sidebar envelope/count badge, refreshed every 15 seconds while visible and immediately after reading/resolving messages.
- Matching theme CSS and the complete built Vite assets and manifests.

This is the code-only option selected by the user. Database content is excluded: saved header/footer HTML/CSS, site settings, social-link records and messages are not exported or overwritten. No database migration, permission seeder, dependency installation or npm build is required.

Use with the existing application version and theme assets from before this cutoff. Each listed runtime file is replaced in full, so retain any hosting-only edits before applying the package. Font Awesome 7 assets must already exist under public/themes/school-of-signals/assets/fonts/fontawesome-7/.

## Run on a Linux hosting server

Upload the ZIP into the Laravel project root (the directory containing artisan, .env and vendor/). Open a terminal in that same directory and run:

```bash
unzip sch_of_slsc-update-after-1235-2026-10-22.zip -d update-after-1235-20261022
bash update-after-1235-20261022/deploy.sh "$PWD"
```

RUN-COMMANDS.txt contains these commands separately.

The installer verifies file hashes, backs up existing target files to storage/app/deployment-backups/, briefly enters maintenance mode, copies the payload, clears compiled Blade views and restores the prior file-based maintenance state. It retains existing hashed assets. Existing files and newly added paths are recorded separately in the backup directory.

If the existing Composer loader is authoritative, the installer needs Composer and regenerates its class map with dump-autoload --no-dev --optimize --no-scripts. Otherwise no Composer command is run.

Optional validation without installation:

```bash
bash update-after-1235-20261022/deploy.sh "$PWD" --check
```

PHP CLI 8.3+, Bash, unzip, tar and sha256sum are required. For a hosting-specific PHP executable:

```bash
PHP_BIN=/path/to/php bash update-after-1235-20261022/deploy.sh "$PWD"
```

## Manual upload / Windows

Back up the files listed in FILES.txt, then merge payload/ into the existing Laravel project root, preserving paths. Run from that root:

```text
php artisan view:clear
```

An authoritative Composer loader also needs composer dump-autoload --no-dev --optimize --no-scripts. The two new Livewire class/view files must be uploaded with the existing files.

## Checks

- Combined regression checks: 69 tests, 305 assertions passed.
- Targeted Pint: passed. Targeted PHPStan: zero errors.
- Vite production build: passed; compiled output is included.
- Package manifest and per-file SHA-256 checks are included.

These are targeted checks for this release, not a claim that the entire project test suite passes. The unrelated Past Commandants profile-link test was already failing before these changes.

After applying, refresh the English and Sinhala homepages. Check the email-provider choices, location link and social icons. Submit a test contact message, check the sidebar badge within 15 seconds while visible, then open/resolve the message and check that the count drops. The default Email app option depends on the visitor's configured mail handler.

This package was prepared locally. The hosting server has not been changed by the assistant.

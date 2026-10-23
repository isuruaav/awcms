# Media paths and Yes/No confirmations - 22 October 2026

Incremental update for the existing sch_of_slsc application. The ZIP contains
runtime files under `payload/`, compiled Vite assets, a file manifest and an
installer. Upload it to the Laravel project root containing `artisan`, `.env`
and `vendor/`.

Included:

- Media Library upload/list/edit screens expose a selectable path and Copy Path.
- Clipboard copying works with the browser clipboard and a selection fallback.
- Public media links use clean filenames, such as `/media-files/448_19_news.jpg`.
  Duplicates receive `-2`, `-3`, etc. Existing UUID links keep working.
- Existing uploads receive persistent clean names through one additive migration.
- Document Uploads expose English/Sinhala paths, including after saving while a
  table filter hides the saved document.
- The page editor supports image URLs and a Document button for document links.
- News/Page saves and News publish, unpublish, delete and restore use the shared
  Yes/No dialog. Pending/cancelled actions cannot be resumed by an earlier
  request; duplicate saves and stale confirmations are blocked.
- Mobile News actions are disabled while their requests run, matching desktop.
- Current News Trash backend/preview dependencies used by the included News UI.

This package assumes the currently installed application already has the school
leadership/profile, contact-message notification, document-upload and page custom
CSS modules. The installer checks representative prerequisite files before
changing the target. It replaces listed files in full; retain any server-only
edits before installing.

No `.env`, credentials, uploaded files, database dumps, runtime caches,
`vendor/` or `node_modules/` are included. Existing site content stays on the
server. No dependency installation, permission seeding or server npm build is
needed.

## Linux server commands

Open the terminal in the Laravel project root, upload the ZIP there, and run:

```bash
unzip -o sch_of_slsc-media-confirmations-2026-10-22.zip -d update-media-confirmations-20261022
bash update-media-confirmations-20261022/deploy.sh "$PWD"
```

Optional checks without installation:

```bash
bash update-media-confirmations-20261022/deploy.sh "$PWD" --check
```

The installer checks package hashes and target paths, backs up files to
`storage/app/deployment-backups/media-confirmations-20261022-*/`, enters
maintenance mode, copies the payload, runs only the migration below, clears
route and Blade view caches, and returns the application to its previous
maintenance state. It preserves older hashed build assets.

```text
database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php
```

The migration adds the unique, nullable `media_assets.public_filename` column
and assigns names to existing records. It does not rename physical media files
or replace news/pages/settings. It is skipped if already applied. It does not
run other pending migrations.

Requirements: PHP CLI 8.3+, Bash, unzip, tar and GNU coreutils. Use the account
that normally owns the application files. For a hosting-specific PHP executable:

```bash
PHP_BIN=/path/to/php bash update-media-confirmations-20261022/deploy.sh "$PWD"
```

Composer is required only when the existing installation uses an authoritative
class map. In that case the installer runs
`composer dump-autoload --no-dev --optimize --no-scripts --no-interaction`.

## Verify after installation

Refresh with Ctrl+F5. Copy an image path from Media Library and a document path
from Document Uploads, insert them into a test page, and view the published page.
Check News save/publish/unpublish/Trash confirmations: No leaves the item
unchanged; Yes performs the chosen operation once.

The current local image's `-2` suffix reflects the local database's existing
duplicate. The server generates aliases from its own media records. Re-copy
paths on the server rather than using URLs containing localhost.

## Restore the previous code if necessary

Use the backup path printed by the installer, from the Laravel project root:

```bash
php artisan down --retry=60
tar -xzf storage/app/deployment-backups/REPLACE_WITH_BACKUP_FOLDER/files-before-update.tar.gz -C "$PWD"
php artisan route:clear
php artisan view:clear
php artisan up
```

If Composer's authoritative map was regenerated, its previous files are in the
same backup. `existing-files.txt` and `new-files.txt` record replaced and added
files. Restoring old code does not require removing the additive database column.
If the site was already in maintenance mode, keep it there instead of running
`artisan up`. The installer stops on errors and prints the backup location.

## Validation

The included application code passed targeted media, clean-path, document,
confirmation and News/Page checks during this session. The latest confirmation
checks passed 24 JavaScript tests and 18 PHP feature tests; the media checks
passed 54 PHP tests. Production Vite build, targeted Pint and PHPStan checks
passed. These are targeted checks, not the entire project's test suite.

No live browser connection was available for the confirmation click check.
The clean media route was verified against the local HTTP server and returned
the same image bytes as its legacy UUID URL. The ZIP and installer are verified
locally; the hosting server has not been changed by the assistant.

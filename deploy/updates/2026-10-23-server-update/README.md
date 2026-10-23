# Server update: 23 October 2026

Prepared from the supplied server comparison and migration-status reports.
408 of 449 reference files already match.
There are 41 differences: 9 missing,
3 known previous versions and 29 other differences.

This ZIP installs 49 current files: the 39 application
differences, plus the complete compiled Vite build and its dependencies. Includes:

- Media/document Copy Path and clean public media filename URLs.
- Shared Yes/No confirmations for News/Page actions and matching built assets.
- Later theme renderer, legacy Commandant/Chief Instructor and history-view fixes.
- Current routes and controller/service formatting changes from the same local snapshot.

Hosting composer.json and public/.htaccess differ and are preserved. The installer
checks require/autoload compatibility and the unchanged composer.lock. No dependency
installation, server npm build, local database content import, seeding, or Apache
configuration change is performed. Existing .env, uploads, saved settings/layouts,
and the five server-only old hashed assets remain on the server.

The comparison contains hashes rather than server file contents. Consequently the
server-side source edits cannot be reviewed line by line from those reports.
Application files listed in FILES.txt are deliberately replaced with the current
local versions, and their exact prior bytes are backed up. The installer refuses
code that has changed since the supplied report (or no longer matches this release).

## Install from the existing Laravel root

```bash
unzip sch_of_slsc-server-update-2026-10-23.zip -d update-20261023
php update-20261023/install.php "$PWD" --check
php update-20261023/install.php "$PWD"
```

Run the install command only after --check passes. If update-20261023 already
exists, extract into a new directory and adjust the path. Do not use sudo for
the PHP installer; use the account that owns the existing application files.

Requirements: PHP CLI 8.3+ with proc_open, tar, a writable private storage/app
directory, and mariadb-dump or mysqldump in CLI PATH for MySQL/MariaDB. SQLite
uses VACUUM INTO. Other database engines stop before copying application files.
An authoritative Composer class map also requires Composer CLI. The server's
effective Laravel database configuration supplies backup credentials privately;
passwords are not written into commands, reports or the release.

--check verifies package integrity, target hashes, compatible Composer settings
and PHP syntax without booting Laravel, copying payloads or running migrations.
Installation checks all prerequisite applied migrations, locks concurrent updates,
backs up overwritten files, briefly enables maintenance mode, makes a full database
backup, copies payloads, refreshes an authoritative class map if necessary, runs
only this migration, and clears route/view caches:

```text
database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php
```

This adds media_assets.public_filename and fills aliases for existing media.
It does not rename uploaded physical files or replace existing site content.
The migration is skipped if already applied. A partial migration state stops
before payload copying. Previously applied footer/content migrations are not rerun.

Successful installation prints the private backup directory under:
storage/app/deployment-backups/2026-10-23-server-update-*/
It contains files-before-update.tar.gz, BACKUP-STATE.json and database.sql
(or database.sqlite). Keep that directory private; database backups contain site data.

If installation fails before payload copying, a newly enabled maintenance mode
is lifted. If it fails after copying starts, it leaves maintenance enabled so
you can fix the error or restore code. A previously down site stays down.

## Restore previous code

Use the exact backup directory printed by the installer:

```bash
php update-20261023/install.php "$PWD" --restore-code /absolute/path/to/backup-directory
```

This checks the backup identity and rejects files edited since installation,
restores the previous overwritten files (including Composer metadata when needed),
clears route/view caches, and restores the previous maintenance state. Added files,
new hashed assets and the additive database column/migration are retained; old
code can use the extra nullable column. No automatic database rollback or import
is attempted. The full database backup is available for a separately planned
database restoration if required.

## Verify

Refresh with Ctrl+F5. Check English/Sinhala homepages, login, News/Page saves and
Yes/No cancellation/confirmation, Media Library/Document Copy Path, a published
page using copied image/document links, and Commandant/Chief Instructor history.
Existing media links should keep working. If PHP opcache timestamps are disabled
on hosting, reload PHP-FPM/Apache using the hosting's normal process.

Validation results are recorded in VALIDATION.json after local checks complete.
This archive is prepared locally; the server has not been modified by the assistant.

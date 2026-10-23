# Revision 2: reviewed server homepage compatibility

This replaces the first latest-update ZIP, whose preflight stopped on home.blade.php.
It adds the exact reviewed server homepage LF hash to the recognized versions.
The payload (all 306 files) and installer are byte-for-byte identical to that ZIP.
Unknown server changes still stop the installer. The original homepage bytes are
backed up before installing the current homepage, just like other overwritten files.
Use this ZIP and its separate commands file, extracting into the new v2 directory.

# Latest project update — 23 October 2026

This cumulative package contains 306 runtime/build files. It uses the previously
delivered sch_of_slsc-server-update-2026-10-23.zip as its baseline and includes current
local changes made after it. The previous ZIP is not overwritten.

Included changes:
- Publications: PDF + separate JPG/JPEG cover uploads, up to 20 MB PDF / 2 MB cover,
  English/Sinhala selection, five items per public page, PDF links, icon controls.
- Courses: admin management, qualifications, categories, Sinhala names, public
  English/Sinhala pages and header/homepage course links.
- Latest admin sidebar/layout/fonts, News/Page controls, News pagination and theme styles.
- Full fresh Vite build, theme fonts/icons/browser assets, PDF.js supporting assets,
  and the previous update's application fixes for cumulative compatibility.

No local database or uploaded PDFs/images are included. Content/settings entered
only in the local database are not transferred by this source-code package.
Courses migrations create the 16 supplied starter courses and five categories only
when those migrations have not already run. Publications/Courses navigation migrations
patch the saved EN/SI headers with revision history. Other saved page/layout content
is retained. Existing PDFs, media and uploaded files remain in place.

## Install

Upload the ZIP to /var/www/html/sch_of_slsc/ and run RUN-COMMANDS.txt. The same
commands are supplied separately beside the ZIP. Extract into a fresh directory.
Use the application's owner account. The final install runs only if --check passes.

The installer accepts the previous ZIP's hashes, the original audited server hashes,
or this release's hashes. It stops before copying if a target has another version.
An independently changed hosting public/.user.ini also stops for comparison.
It preserves hosting .env, .htaccess, composer.json and composer.lock; it verifies
Composer require/autoload compatibility. Server npm/composer install is not needed.

Requirements: PHP CLI 8.3+, proc_open, tar, writable application/private storage,
and mariadb-dump or mysqldump on PATH for MySQL/MariaDB. SQLite uses VACUUM INTO.
An authoritative Composer class map additionally requires Composer CLI to refresh it.
The Laravel effective DB configuration supplies credentials privately to the dump tool.

--check verifies hashes, package integrity and PHP syntax without copying files or
running migrations. Installation verifies database prerequisites/partial schema state,
locks concurrent deployments, backs up overwritten files, enables maintenance briefly,
makes a complete database backup, installs the files, refreshes an authoritative
class map if needed, runs only the following migrations (already applied ones skip),
then clears config/route/view caches and restores the previous maintenance state:

database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php
database/migrations/2026_10_23_150000_create_publications_table.php
database/migrations/2026_10_23_150100_connect_publication_navigation.php
database/migrations/2026_10_23_150200_add_locale_to_publications_table.php
database/migrations/2026_10_23_150300_create_courses_tables.php
database/migrations/2026_10_23_150400_add_courses_to_navigation.php
database/migrations/2026_10_23_150500_add_sinhala_course_names.php
database/migrations/2026_10_23_150600_create_course_categories_table.php

Backups are private under storage/app/deployment-backups/2026-10-23-latest-update-v2-*/ with
files-before-update.tar.gz, BACKUP-STATE.json and database.sql or database.sqlite.
No application files are copied if database backup fails. A failure after copying
begins leaves maintenance enabled so the fault can be resolved or code restored.
An originally down site stays down.

## Code restore

Use the optional restore command in RUN-COMMANDS.txt with the exact printed backup.
The installer rejects files edited since installation and restores overwritten bytes,
Composer metadata if applicable, and saved header HTML when no later edits conflict.
Added files, publication/course records and additive migrations remain; no automatic
database rollback or import occurs. The complete pre-update DB backup is retained.
After a code restore, navigation migrations remain marked applied; review/reconnect
the Publication/Courses header links if installing the feature again.

## After install

Refresh Ctrl+F5. Check admin Publications (PDF + JPG cover), public EN/SI listings,
Courses/categories/qualifications, admin sidebar, News pagination and Page actions.
public/.user.ini sets PHP upload_max_filesize=21M, post_max_size=24M and max_input_time=120;
PHP-FPM/CGI may take up to its .user.ini cache interval to apply it. Apache module PHP
and reverse-proxy upload limits use hosting configuration; verify a large upload there.
Reload the hosting PHP service if opcache timestamp validation is disabled.

VALIDATION.json records local checks and their limits. Hosting connectivity and a
live browser upload must be checked after installation. The assistant only prepared
this package locally and did not change the server.

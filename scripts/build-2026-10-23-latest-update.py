"""Build a cumulative update from the delivered server-update ZIP and current source."""

from datetime import datetime, timedelta, timezone
from hashlib import sha256
import json
from pathlib import Path
import subprocess
from zipfile import ZipFile, ZIP_DEFLATED

ROOT = Path(__file__).resolve().parents[1]
RELEASE = '2026-10-23-latest-update'
STAGE = ROOT / 'deploy/updates' / RELEASE
ARCHIVE = STAGE.parent / 'sch_of_slsc-latest-update-2026-10-23.zip'
PREVIOUS = STAGE.parent / '2026-10-23-server-update/PATCH-MANIFEST.json'
previous = json.loads(PREVIOUS.read_text(encoding='utf-8'))
RUNTIME = ('app/', 'bootstrap/', 'config/', 'database/migrations/', 'lang/', 'resources/', 'routes/', 'themes/')
EXTRA = {'package.json', 'package-lock.json', 'vite.config.js', 'public/.user.ini'}


def git_paths(*arguments):
    return set(filter(None, subprocess.check_output(['git', *arguments, '-z'], cwd=ROOT).decode().split('\0')))


def version(data):
    result = {'sha256': sha256(data).hexdigest()}
    if b'\0' not in data:
        try:
            data.decode('utf-8')
            result['lf_sha256'] = sha256(data.replace(b'\r\n', b'\n')).hexdigest()
        except UnicodeDecodeError:
            pass
    return result


changed = git_paths('diff', '--name-only', 'HEAD') | git_paths('ls-files', '--others', '--exclude-standard')
selected = {p for p in changed if p.startswith(RUNTIME) or p in EXTRA}
selected.update(p for p in previous['files'] if not p.startswith('public/build/'))
assert not any(not (ROOT / p).is_file() for p in selected), 'Deleted runtime files require a reviewed removal plan.'
assert (ROOT / 'composer.lock').read_bytes() == subprocess.check_output(['git', 'show', 'HEAD:composer.lock'], cwd=ROOT)
assert not {'composer.json', '.env', '.env.example', 'public/.htaccess'} & selected

payload = {p: (ROOT / p).read_bytes() for p in selected}
payload.update({p.relative_to(ROOT).as_posix(): p.read_bytes() for p in (ROOT / 'public/build').rglob('*') if p.is_file()})
# Include theme browser assets with their source versions (fonts/icons are referenced by the new admin layout).
for source in (ROOT / 'themes/school-of-signals/assets').rglob('*'):
    if source.is_file():
        relative = 'public/' + source.relative_to(ROOT).as_posix()
        payload[relative] = source.read_bytes()

files = {p: {**version(data), 'bytes': len(data)} for p, data in sorted(payload.items())}
guards = {}
for relative in sorted(set(files) | {'composer.lock'}):
    old = previous['guard_files'].get(relative)
    current = version(payload[relative] if relative in payload else (ROOT / relative).read_bytes())
    versions = []
    allow_missing = old is None
    if old:
        versions.append(old['current'])
        if old['baseline_sha256']:
            versions.append({'sha256': old['baseline_sha256']})
        else:
            allow_missing = True
    unique = {item['sha256']: item for item in reversed(versions) if item['sha256'] != current['sha256']}
    guards[relative] = {'current': current, 'allow_missing': allow_missing, 'previous_versions': list(unique.values())}

migrations = ['database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php']
migrations += sorted(p for p in files if p.startswith('database/migrations/2026_10_23_150'))
assert len(migrations) == 8
schema_checks = []


def schema(migration, table, *columns):
    if not columns:
        schema_checks.append({'migration': migrations[migration], 'table': table})
    for column in columns:
        schema_checks.append({'migration': migrations[migration], 'table': table, 'column': column})


schema(0, 'media_assets', 'public_filename')
schema(1, 'publications')
schema(1, 'publications', 'uuid', 'title', 'title_si', 'disk', 'pdf_path', 'cover_path', 'original_name', 'size_bytes',
       'cover_width', 'cover_height', 'is_published', 'published_at', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at')
schema(3, 'publications', 'locale')
schema(4, 'courses')
schema(4, 'course_qualifications')
schema(6, 'courses', 'name_si')
schema(7, 'course_categories')
schema(7, 'courses', 'course_category_id')

build = json.loads(payload['public/build/manifest.json'])
for item in build.values():
    for asset in [item['file'], *item.get('css', []), *item.get('assets', [])]:
        assert 'public/build/' + asset in payload, f'Missing Vite asset: {asset}'
pdf_version = json.loads((ROOT / 'node_modules/pdfjs-dist/package.json').read_text())['version']
for directory in ['cmaps', 'standard_fonts', 'wasm']:
    for source in (ROOT / 'node_modules/pdfjs-dist' / directory).rglob('*'):
        if source.is_file():
            relative = source.relative_to(ROOT / 'node_modules/pdfjs-dist').as_posix()
            assert payload['public/build/pdfjs/' + pdf_version + '/' + relative] == source.read_bytes()

composer = json.loads((ROOT / 'composer.json').read_text())
manifest = {
    'release': RELEASE,
    'created_at': datetime.now(timezone(timedelta(hours=5, minutes=30))).isoformat(timespec='seconds'),
    'project': 'sch_of_slsc',
    'baseline_release': previous['release'],
    'baseline_zip_sha256': sha256((STAGE.parent / 'sch_of_slsc-server-update-2026-10-23.zip').read_bytes()).hexdigest(),
    'files': files, 'guard_files': guards,
    'composer_compatibility': {key: composer[key] for key in ['require', 'autoload']},
    'required_existing_migrations': previous['required_existing_migrations'],
    'migrations': migrations, 'schema_checks': schema_checks,
    'changed_source_files': sorted(selected),
    'preserved': ['.env', 'composer.json', 'composer.lock', 'public/.htaccess', 'uploads', 'existing database content', 'old hashed assets'],
    'requires_npm_on_server': False, 'database_content_included': False,
}
commands = '''# Upload sch_of_slsc-latest-update-2026-10-23.zip to /var/www/html/sch_of_slsc/
# Run this whole block using the application owner account.
cd /var/www/html/sch_of_slsc &&
unzip sch_of_slsc-latest-update-2026-10-23.zip -d update-20261023-latest &&
php update-20261023-latest/install.php "$PWD" --check &&
php update-20261023-latest/install.php "$PWD"

# If that extraction directory already exists, choose a new directory name
# and use that same name for both PHP commands. Do not reuse the old update folder.
# The installer prints its private code/database backup directory on success.

# OPTIONAL code restore: replace the final argument with that printed backup path.
# php update-20261023-latest/install.php "$PWD" --restore-code /absolute/path/to/backup-directory
'''
readme = f'''# Latest project update — 23 October 2026

This cumulative package contains {len(files)} runtime/build files. It uses the previously
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

{chr(10).join(migrations)}

Backups are private under storage/app/deployment-backups/{RELEASE}-*/ with
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
'''
entries = {'payload/' + p: data for p, data in sorted(payload.items())}
entries.update({
    'install.php': (ROOT / 'scripts/latest-update-install.php').read_bytes().replace(b'\r\n', b'\n'),
    'PATCH-MANIFEST.json': (json.dumps(manifest, indent=2) + '\n').encode(),
    'FILES.txt': ('\n'.join(files) + '\n').encode(),
    'FILES.sha256': ''.join(f'{v["sha256"]}  {p}\n' for p, v in files.items()).encode(),
    'README.md': readme.encode('utf-8'),
    'RUN-COMMANDS.txt': commands.encode(),
})
validation = STAGE / 'VALIDATION.json'
if validation.is_file():
    entries['VALIDATION.json'] = validation.read_bytes()
entries['PACKAGE.sha256'] = ''.join(f'{sha256(data).hexdigest()}  {p}\n' for p, data in sorted(entries.items())).encode()
STAGE.mkdir(parents=True, exist_ok=True)
for relative, data in entries.items():
    target = STAGE / relative
    assert target.resolve().is_relative_to(STAGE.resolve())
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_bytes(data)
assert {p.relative_to(STAGE).as_posix() for p in STAGE.rglob('*') if p.is_file()} == set(entries), 'Unexpected stale stage files.'
with ZipFile(ARCHIVE, 'w', ZIP_DEFLATED, compresslevel=9) as archive:
    for relative, data in sorted(entries.items()):
        archive.writestr(relative, data)
with ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == len(set(archive.namelist()))
    for relative, data in entries.items():
        assert archive.read(relative) == data
ARCHIVE.with_suffix('.zip.sha256').write_text(sha256(ARCHIVE.read_bytes()).hexdigest() + '  ' + ARCHIVE.name + '\n', encoding='ascii')
ARCHIVE.with_name(ARCHIVE.stem + '-COMMANDS.txt').write_text(commands, encoding='utf-8', newline='\n')
print(json.dumps({'archive': str(ARCHIVE), 'payload_files': len(files), 'bytes': ARCHIVE.stat().st_size, 'migrations': len(migrations), 'sha256': sha256(ARCHIVE.read_bytes()).hexdigest()}, indent=2))

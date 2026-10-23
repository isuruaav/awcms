"""Package the exact server delta from the supplied comparison and migration reports."""

from datetime import datetime, timedelta, timezone
from hashlib import sha256
import json
from pathlib import Path
import re
from zipfile import ZipFile, ZIP_DEFLATED

ROOT = Path(__file__).resolve().parents[1]
RELEASE = '2026-10-23-server-update'
STAGE = ROOT / 'deploy/updates' / RELEASE
ARCHIVE = STAGE.parent / 'sch_of_slsc-server-update-2026-10-23.zip'
COMPARISON = Path(r'C:\Users\dmisa\Downloads\server-comparison.json')
MIGRATIONS = Path(r'C:\Users\dmisa\Downloads\server-migrations.txt')
REFERENCE_SCRIPT = ROOT / 'deploy/audits/2026-10-23-server-check/compare-server-2026-10-23.php'
MIGRATION = 'database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php'
PRESERVE = {'composer.json', 'public/.htaccess'}
now = datetime.now(timezone(timedelta(hours=5, minutes=30)))

report = json.loads(COMPARISON.read_text(encoding='utf-8'))
assert report['project'] == 'sch_of_slsc' and report['reference'] == '2026-10-23'
assert not report['scan_issues'] and report['counts']['unsafe_or_unreadable'] == 0
reference = json.loads(REFERENCE_SCRIPT.read_text(encoding='utf-8').split("<<<'MANIFEST'\n", 1)[1].split('\nMANIFEST,', 1)[0])
assert report['local_reference_files'] == len(reference['files'])
differences = {item['path']: item for item in report['differences']}
assert len(differences) == sum(value for key, value in report['counts'].items() if key != 'current')
ran = re.findall(r'^\s*(\S+)\s+\.+\s+\[\d+\] Ran\s*$', MIGRATIONS.read_text(encoding='utf-8'), re.MULTILINE)
assert ran and ran[-1] == '2026_10_21_120000_add_custom_css_to_pages_and_page_revisions'

guards = {}
for relative, item in reference['files'].items():
    path = ROOT / relative
    assert path.is_file() and path.resolve().is_relative_to(ROOT)
    data = path.read_bytes()
    assert sha256(data).hexdigest() == item['sha256'], f'Local source changed since comparison: {relative}'
    current = {key: item[key] for key in ['sha256', 'lf_sha256'] if key in item}
    old = differences.get(relative)
    guards[relative] = {'current': current, 'baseline_sha256': old.get('server_sha256') if old else item['sha256']}

paths = set(differences) - PRESERVE
paths.update(path.relative_to(ROOT).as_posix() for path in (ROOT / 'public/build').rglob('*') if path.is_file())
assert MIGRATION in paths
payload = {}
fileManifest = {}
for relative in sorted(paths):
    assert relative in reference['files']
    assert relative.startswith(('app/', 'resources/', 'routes/', 'themes/', 'public/build/')) or relative == MIGRATION
    data = (ROOT / relative).read_bytes()
    payload['payload/' + relative] = data
    fileManifest[relative] = {'sha256': sha256(data).hexdigest(), 'bytes': len(data)}

composer = json.loads((ROOT / 'composer.json').read_text(encoding='utf-8'))
metadata = {
    'release': RELEASE,
    'created_at': now.isoformat(timespec='seconds'),
    'project': 'sch_of_slsc',
    'server_report_generated_at': report['generated_at_utc'],
    'server_report_sha256': sha256(COMPARISON.read_bytes()).hexdigest(),
    'migration_report_sha256': sha256(MIGRATIONS.read_bytes()).hexdigest(),
    'server_counts': report['counts'],
    'files': fileManifest,
    'guard_files': guards,
    'composer_compatibility': {key: composer[key] for key in ['require', 'autoload']},
    'required_existing_migrations': ran,
    'migration': MIGRATION,
    'preserved_differences': sorted(PRESERVE),
    'server_only_assets_retained': report['server_only_in_compared_directories'],
    'database_content_included': False,
    'requires_npm_on_server': False,
    'review_limitation': 'The reports contain hashes, not server source contents. Reported application files are replaced with current local versions after backing up their original bytes. Hosting .htaccess and composer.json are preserved; dependency/autoload compatibility is checked.',
}
commands = '''# Upload the ZIP into /var/www/html/sch_of_slsc/, then open Terminal there.
unzip sch_of_slsc-server-update-2026-10-23.zip -d update-20261023
php update-20261023/install.php "$PWD" --check
php update-20261023/install.php "$PWD"
'''
readme = f'''# Server update: 23 October 2026

Prepared from the supplied server comparison and migration-status reports.
{report['counts']['current']} of {report['local_reference_files']} reference files already match.
There are {len(differences)} differences: {report['counts']['missing']} missing,
{report['counts']['known_previous']} known previous versions and {report['counts']['different']} other differences.

This ZIP installs {len(fileManifest)} current files: the {len(differences) - len(PRESERVE)} application
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
{MIGRATION}
```

This adds media_assets.public_filename and fills aliases for existing media.
It does not rename uploaded physical files or replace existing site content.
The migration is skipped if already applied. A partial migration state stops
before payload copying. Previously applied footer/content migrations are not rerun.

Successful installation prints the private backup directory under:
storage/app/deployment-backups/{RELEASE}-*/
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
'''
entries = {
    **payload,
    'install.php': (ROOT / 'scripts/server-update-install.php').read_bytes().replace(b'\r\n', b'\n'),
    'PATCH-MANIFEST.json': (json.dumps(metadata, indent=2) + '\n').encode(),
    'FILES.txt': ('\n'.join(fileManifest) + '\n').encode(),
    'FILES.sha256': ''.join(f'{item["sha256"]}  {path}\n' for path, item in fileManifest.items()).encode(),
    'README.md': readme.encode(),
    'RUN-COMMANDS.txt': commands.encode(),
}
validation = STAGE / 'VALIDATION.json'
if validation.is_file():
    entries['VALIDATION.json'] = validation.read_bytes()
entries['PACKAGE.sha256'] = ''.join(f'{sha256(data).hexdigest()}  {path}\n' for path, data in sorted(entries.items())).encode()
STAGE.mkdir(parents=True, exist_ok=True)
for relative, data in entries.items():
    target = STAGE / relative
    assert target.resolve().is_relative_to(STAGE.resolve())
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_bytes(data)
assert {p.relative_to(STAGE).as_posix() for p in STAGE.rglob('*') if p.is_file()} == set(entries)
with ZipFile(ARCHIVE, 'w', ZIP_DEFLATED, compresslevel=9) as archive:
    for relative, data in sorted(entries.items()):
        archive.writestr(relative, data)
with ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == len(set(archive.namelist()))
    for relative, data in entries.items():
        assert archive.read(relative) == data
ARCHIVE.with_suffix('.zip.sha256').write_text(sha256(ARCHIVE.read_bytes()).hexdigest() + '  ' + ARCHIVE.name + '\n', encoding='ascii')
ARCHIVE.with_name(ARCHIVE.stem + '-COMMANDS.txt').write_text(commands, encoding='utf-8')
print(json.dumps({'archive': str(ARCHIVE), 'payload_files': len(fileManifest), 'bytes': ARCHIVE.stat().st_size, 'sha256': sha256(ARCHIVE.read_bytes()).hexdigest(), 'preserved': sorted(PRESERVE)}, indent=2))

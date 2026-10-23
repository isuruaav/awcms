"""Build a portable, read-only PHP code comparison for an unknown server version."""

from datetime import datetime, timezone, timedelta
from hashlib import sha256
import json
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED

ROOT = Path(__file__).resolve().parents[1]
REFERENCE = '2026-10-23'
OUTPUT = ROOT / 'deploy' / 'audits' / f'{REFERENCE}-server-check'
DIRECTORIES = ['app', 'bootstrap', 'config', 'database', 'lang', 'resources', 'routes', 'themes', 'public/build', 'public/themes']
TOP_FILES = ['artisan', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'vite.config.js', 'public/index.php', 'public/.htaccess', '.htaccess']
TEXT_SUFFIXES = {'.php', '.js', '.mjs', '.cjs', '.css', '.json', '.html', '.svg', '.txt', '.md', '.xml', '.gitignore', '.htaccess'}
LOCAL_ZONE = timezone(timedelta(hours=5, minutes=30))


def ignored(relative):
    return relative.startswith('bootstrap/cache/') or any(
        part in {'.env', '.git', 'node_modules', 'vendor', '.gitignore'} for part in Path(relative).parts
    )


def hashes(path):
    data = path.read_bytes()
    result = {'sha256': sha256(data).hexdigest()}
    if path.suffix.lower() in TEXT_SUFFIXES or path.name in {'artisan', '.htaccess'}:
        result['lf_sha256'] = sha256(data.replace(b'\r\n', b'\n')).hexdigest()
    return result


paths = set()
for name in DIRECTORIES:
    paths.update(path for path in (ROOT / name).rglob('*') if path.is_file() and not path.is_symlink())
paths.update(ROOT / name for name in TOP_FILES if (ROOT / name).is_file())
files = {}
for path in sorted(paths):
    relative = path.relative_to(ROOT).as_posix()
    if not ignored(relative):
        if not path.resolve().is_relative_to(ROOT):
            raise ValueError(f'Source leaves project: {relative}')
        files[relative] = hashes(path)

packageAudit = []
coveredYesterday = set()
for manifestPath in sorted((ROOT / 'deploy/updates').glob('*/PATCH-MANIFEST.json')):
    package = json.loads(manifestPath.read_text(encoding='utf-8'))
    changed, absent = [], []
    for entry in package.get('files', []):
        relative = entry['path']
        archived = manifestPath.parent / 'payload' / relative
        if not archived.is_file():
            raise ValueError(f'Missing historical payload: {archived}')
        version = hashes(archived)
        if version['sha256'] != entry['sha256']:
            raise ValueError(f'Historical payload hash mismatch: {relative}')
        if relative in files:
            files[relative].setdefault('previous', []).append({'release': manifestPath.parent.name, **version})
            if files[relative]['sha256'] != version['sha256']:
                changed.append(relative)
        else:
            absent.append(relative)
        if manifestPath.parent.name.startswith('2026-10-22') and relative in files and files[relative]['sha256'] == version['sha256']:
            coveredYesterday.add(relative)
    packageAudit.append({'release': manifestPath.parent.name, 'files': len(package.get('files', [])), 'different_from_current': changed, 'no_longer_current': absent})

yesterday = []
for relative in files:
    path = ROOT / relative
    modified = datetime.fromtimestamp(path.stat().st_mtime, LOCAL_ZONE)
    if modified.date().isoformat() == '2026-10-22':
        yesterday.append({'path': relative, 'modified_at': modified.isoformat(timespec='seconds'), 'current_bytes_in_existing_oct22_package': relative in coveredYesterday})
yesterday.sort(key=lambda item: (item['modified_at'], item['path']))

manifest = {'reference': REFERENCE, 'directories': DIRECTORIES, 'files': files}
template = (ROOT / 'scripts/server-comparison-template.php').read_text(encoding='utf-8')
assert template.count('__SERVER_COMPARISON_MANIFEST__') == 1
script = template.replace('__SERVER_COMPARISON_MANIFEST__', json.dumps(manifest, ensure_ascii=True, indent=2))
OUTPUT.mkdir(parents=True, exist_ok=True)
scriptName = f'compare-server-{REFERENCE}.php'
(OUTPUT / scriptName).write_bytes(script.encode('utf-8'))
(OUTPUT / 'LOCAL-AUDIT.json').write_text(json.dumps({'reference': REFERENCE, 'reference_file_count': len(files), 'previous_packages': packageAudit, 'modified_on_oct22': yesterday, 'limitations': ['File modification times identify candidates, not an exact change history.', 'No server files or database state have been inspected.', 'Saved settings/layouts/content in the local database are not included.']}, indent=2) + '\n', encoding='utf-8')
readme = f'''# Server comparison: 23 October 2026

The existing server version is unknown. This is a comparison tool, not an installer.
It reads {len(files)} local-reference paths and reports matching, older, different,
missing and server-only code files. File contents and secrets are not included in
the report. It does not load Laravel, read .env, connect to a database, or modify
the application. Run using PHP CLI 8.0+.

1. Upload only `{scriptName}` into the existing Laravel root, next to `artisan`.
   Keep the script outside the public directory.
2. Open a server terminal in that Laravel root and run:

```bash
php {scriptName} . > server-comparison-{REFERENCE}.json
php artisan migrate:status --no-ansi > server-migrations-{REFERENCE}.txt
```

The second command reads Laravel's applied migration list. If it fails, keep the
error text; do not run migrations yet. The comparison script does not require it.
If the server uses a specific PHP executable, replace `php` accordingly.

3. Download both report files and share them for the update preparation.
   The JSON includes hashes and code paths, not file contents. The migration
   report includes migration names/status. Do not share .env or database passwords.

The update can then be built from current code, with hosting edits reviewed,
required migrations selected, and a backup/rollback procedure. Take a server
database and file backup before installing the eventual update. Local saved
header/footer layouts or settings are database content and require a separate
decision if they must be copied to the server.

Latest existing update ZIP: 22 October, 19:34 Asia/Colombo.
Local source changes continue through 22:58, so that ZIP does not cover all
current changes. LOCAL-AUDIT.json lists package differences and timestamp
candidates. Timestamps alone are not used to decide what to overwrite.

Do not delete server-only files automatically: they may be hosting changes or
older hashed assets still in use. A current code match does not prove database
compatibility or installed dependency compatibility. Composer lockfiles are
compared; vendor packages are not scanned. No server update has been performed.
'''
(OUTPUT / 'README.md').write_text(readme, encoding='utf-8')
archive = OUTPUT.parent / f'sch_of_slsc-server-check-{REFERENCE}.zip'
with ZipFile(archive, 'w', ZIP_DEFLATED) as zipped:
    for name in [scriptName, 'README.md', 'LOCAL-AUDIT.json']:
        zipped.write(OUTPUT / name, name)
with ZipFile(archive) as zipped:
    assert zipped.testzip() is None
archive.with_suffix('.zip.sha256').write_text(sha256(archive.read_bytes()).hexdigest() + '  ' + archive.name + '\n', encoding='ascii')
print(json.dumps({'script': str(OUTPUT / scriptName), 'zip': str(archive), 'reference_files': len(files), 'oct22_files': len(yesterday), 'oct22_current_files_not_in_existing_oct22_packages': [item['path'] for item in yesterday if not item['current_bytes_in_existing_oct22_package']]}, indent=2))

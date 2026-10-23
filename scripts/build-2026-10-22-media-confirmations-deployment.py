"""Build and verify the session's media-path and confirmation deployment ZIP."""

from datetime import datetime, timedelta, timezone
from pathlib import Path, PurePosixPath
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo
import hashlib
import json
import re
import subprocess

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / 'deploy/updates/2026-10-22-media-confirmations'
ARCHIVE = ROOT / 'deploy/updates/sch_of_slsc-media-confirmations-2026-10-22.zip'
COMMAND_FILE = ARCHIVE.with_name(ARCHIVE.stem + '-COMMANDS.txt')
CREATED = datetime.now(timezone(timedelta(hours=5, minutes=30)))

FILES = {
    'app/Http/Controllers/PublicMediaFileController.php',
    'app/Livewire/Admin/Documents/DocumentUploads.php',
    'app/Livewire/Admin/Media/MediaEdit.php',
    'app/Livewire/Admin/Media/MediaUpload.php',
    'app/Livewire/Admin/News/NewsIndex.php',
    'app/Models/MediaAsset.php',
    'app/Services/MediaPublicFilename.php',
    'app/Services/MediaUrlService.php',
    'app/Services/NewsDeletionService.php',
    'database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php',
    'resources/js/admin-confirmations.js',
    'resources/js/app.js',
    'resources/js/copy-path.js',
    'resources/js/page-content-editor.js',
    'resources/views/admin/news/preview.blade.php',
    'resources/views/components/admin/confirmation-dialog.blade.php',
    'resources/views/components/admin/copy-path.blade.php',
    'resources/views/components/admin/icon-action.blade.php',
    'resources/views/components/forms/page-content-editor.blade.php',
    'resources/views/components/layouts/admin.blade.php',
    'resources/views/livewire/admin/documents/document-uploads.blade.php',
    'resources/views/livewire/admin/media/media-edit.blade.php',
    'resources/views/livewire/admin/media/media-index.blade.php',
    'resources/views/livewire/admin/media/media-upload.blade.php',
    'resources/views/livewire/admin/news/news-create.blade.php',
    'resources/views/livewire/admin/news/news-edit.blade.php',
    'resources/views/livewire/admin/news/news-index.blade.php',
    'resources/views/livewire/admin/pages/page-create.blade.php',
    'resources/views/livewire/admin/pages/page-edit.blade.php',
    'routes/web.php',
}
assert len(FILES) == 30
FILES.update(path.relative_to(ROOT).as_posix() for path in (ROOT / 'public/build').rglob('*') if path.is_file())

payload = {}
manifest = []
for relative in sorted(FILES):
    path = ROOT / relative
    assert path.is_file() and not path.is_symlink() and path.resolve().is_relative_to(ROOT), relative
    assert not any(part.startswith('.') for part in PurePosixPath(relative).parts), relative
    assert not relative.startswith(('storage/', 'vendor/', 'node_modules/', 'public/storage/', 'bootstrap/cache/'))
    data = path.read_bytes()
    payload[relative] = data
    manifest.append({'path': relative, 'bytes': len(data), 'sha256': hashlib.sha256(data).hexdigest()})

vite_manifest = json.loads(payload['public/build/manifest.json'])
for entry in vite_manifest.values():
    for asset in [entry['file'], *entry.get('css', []), *entry.get('assets', [])]:
        assert 'public/build/' + asset in payload, 'Missing Vite dependency: ' + asset
for relative, data in payload.items():
    if relative.startswith('public/build/') and relative.endswith('.css'):
        for asset in re.findall(r'url\(([^)]+)\)', data.decode('utf-8')):
            asset = asset.strip('\"\'')
            if asset.startswith('data:') or '://' in asset:
                continue
            referenced = 'public/' + asset.lstrip('/') if asset.startswith('/') else (PurePosixPath(relative).parent / asset).as_posix()
            assert referenced in payload, 'Missing CSS dependency: ' + referenced

for relative in payload:
    if relative.endswith('.php') and not relative.startswith('resources/'):
        result = subprocess.run(['php', '-l', str(ROOT / relative)], capture_output=True, text=True)
        assert result.returncode == 0, result.stdout + result.stderr

commands = (
    '# Linux server: run in the existing Laravel project root containing artisan, .env and vendor/.\n'
    'unzip -o sch_of_slsc-media-confirmations-2026-10-22.zip -d update-media-confirmations-20261022\n'
    'bash update-media-confirmations-20261022/deploy.sh "$PWD"\n'
)
metadata = {
    'README.md': (STAGE / 'README.md').read_bytes().replace(b'\r\n', b'\n'),
    'deploy.sh': (STAGE / 'deploy.sh').read_bytes().replace(b'\r\n', b'\n'),
    'RUN-COMMANDS.txt': commands.encode('utf-8'),
    'FILES.txt': ('\n'.join(payload) + '\n').encode('utf-8'),
    'FILES.sha256': ''.join(f'{row["sha256"]}  {row["path"]}\n' for row in manifest).encode('ascii'),
    'PATCH-MANIFEST.json': (json.dumps({
        'release': '2026-10-22-media-confirmations',
        'project': 'sch_of_slsc',
        'created_at': CREATED.isoformat(timespec='seconds'),
        'scope': 'Media/document Copy Path, clean filename URLs, page insertion, and News/Page Yes/No fixes',
        'database_content_included': False,
        'requires_migration': 'database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php',
        'requires_npm_build_on_server': False,
        'application_validation_during_session': {
            'media_php_tests_passed': 54,
            'confirmation_php_tests_passed': 18,
            'javascript_tests_passed': 24,
            'targeted_phpstan_errors': 0,
            'targeted_pint': 'passed',
            'vite_build': 'passed',
            'live_confirmation_browser_check': 'unavailable',
        },
        'payload_files': len(payload),
        'files': manifest,
    }, indent=2) + '\n').encode('utf-8'),
}

entries = [('payload/' + name, data) for name, data in payload.items()] + list(metadata.items())
expected = {name for name, _ in entries}
for relative, data in entries:
    target = STAGE / relative
    assert target.resolve().is_relative_to(STAGE.resolve())
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_bytes(data)
assert {p.relative_to(STAGE).as_posix() for p in STAGE.rglob('*') if p.is_file()} == expected

with ZipFile(ARCHIVE, 'w', ZIP_DEFLATED, compresslevel=9) as archive:
    for name, data in entries:
        info = ZipInfo(name, CREATED.timetuple()[:6])
        info.create_system = 3
        info.external_attr = (0o100755 if name == 'deploy.sh' else 0o100644) << 16
        info.compress_type = ZIP_DEFLATED
        archive.writestr(info, data)
with ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == len(set(archive.namelist())) == len(expected)
    for row in manifest:
        assert hashlib.sha256(archive.read('payload/' + row['path'])).hexdigest() == row['sha256']
    assert all(not name.startswith('/') and '..' not in PurePosixPath(name).parts and '\\' not in name for name in archive.namelist())

digest = hashlib.sha256(ARCHIVE.read_bytes()).hexdigest()
ARCHIVE.with_suffix('.zip.sha256').write_bytes((digest + '  ' + ARCHIVE.name + '\n').encode('ascii'))
COMMAND_FILE.write_bytes(commands.encode('utf-8'))
print(json.dumps({'archive': str(ARCHIVE), 'commands': str(COMMAND_FILE), 'payload_files': len(payload), 'archive_bytes': ARCHIVE.stat().st_size, 'sha256': digest}))

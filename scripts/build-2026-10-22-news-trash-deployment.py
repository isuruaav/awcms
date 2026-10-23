"""Package the five runtime files for the News Trash fix; never deploy them."""
from pathlib import Path
import hashlib
import json
import shutil
import zipfile

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / 'deploy/updates/2026-10-22-news-trash-fix'
ARCHIVE = ROOT / 'deploy/updates/sch_of_slsc-news-trash-fix-2026-10-22.zip'
FILES = [
    'app/Livewire/Admin/News/NewsIndex.php',
    'app/Services/NewsDeletionService.php',
    'resources/views/admin/news/preview.blade.php',
    'resources/views/livewire/admin/news/news-index.blade.php',
    'routes/web.php',
]

assert STAGE.resolve().is_relative_to(ROOT)
assert ARCHIVE.resolve().is_relative_to(ROOT)
manifest = []
for relative in FILES:
    source = ROOT / relative
    target = STAGE / 'payload' / relative
    assert target.resolve().is_relative_to(STAGE)
    assert source.is_file(), relative
    target.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, target)
    digest = hashlib.sha256(target.read_bytes()).hexdigest()
    manifest.append({'path': relative, 'sha256': digest, 'bytes': target.stat().st_size})

(STAGE / 'FILES.txt').write_text('\n'.join(FILES) + '\n', encoding='utf-8')
(STAGE / 'FILES.sha256').write_text(''.join(row['sha256'] + '  ' + row['path'] + '\n' for row in manifest), encoding='ascii')
(STAGE / 'PATCH-MANIFEST.json').write_text(json.dumps({'name': 'News Trash fix', 'date': '2026-10-22', 'requires_migration': False, 'files': manifest}, indent=2) + '\n', encoding='utf-8')
expected = {STAGE / name for name in ['README.md', 'FILES.txt', 'FILES.sha256', 'PATCH-MANIFEST.json']}
expected.update(STAGE / 'payload' / relative for relative in FILES)
assert {p for p in STAGE.rglob('*') if p.is_file()} == expected
with zipfile.ZipFile(ARCHIVE, 'w', zipfile.ZIP_DEFLATED) as archive:
    for file in sorted(expected):
        archive.write(file, file.relative_to(STAGE).as_posix())
with zipfile.ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    for row in manifest:
        assert hashlib.sha256(archive.read('payload/' + row['path'])).hexdigest() == row['sha256']
digest = hashlib.sha256(ARCHIVE.read_bytes()).hexdigest()
ARCHIVE.with_suffix('.zip.sha256').write_text(digest + '  ' + ARCHIVE.name + '\n', encoding='ascii')
print(json.dumps({'archive': str(ARCHIVE), 'payload_files': len(FILES), 'archive_bytes': ARCHIVE.stat().st_size, 'sha256': digest}))

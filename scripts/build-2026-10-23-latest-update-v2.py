"""Recognize the homepage version reviewed in the user's server diff."""
from datetime import datetime, timezone
from hashlib import sha256
import json
from pathlib import Path
import re
import shutil
import subprocess
import tempfile
from zipfile import ZipFile, ZIP_DEFLATED

ROOT = Path(__file__).resolve().parents[1]
UPDATES = ROOT / 'deploy/updates'
ORIGINAL = UPDATES / 'sch_of_slsc-latest-update-2026-10-23.zip'
ARCHIVE = UPDATES / 'sch_of_slsc-latest-update-2026-10-23-v2.zip'
STAGE = UPDATES / '2026-10-23-latest-update-v2'
AUDIT = ROOT / 'deploy/audits/2026-10-23-homepage-baseline'
REPORT = Path(r'C:\Users\dmisa\.codex\attachments\58e18df3-3ff0-47fe-acbc-3801d23f6b12\Pasted text.txt')
HOME = 'themes/school-of-signals/views/home.blade.php'
RELEASE = '2026-10-23-latest-update-v2'


def digest(data):
    return sha256(data).hexdigest()


assert digest(ORIGINAL.read_bytes()) == '2ec6b98b7a443b811b8acdf2a6d172cd2c15ed65f399b262fc714a80a07b8f98'
with ZipFile(ORIGINAL) as archive:
    assert archive.testzip() is None
    entries = {name: archive.read(name) for name in archive.namelist()}
for line in entries['PACKAGE.sha256'].decode().splitlines():
    checksum, relative = line.split('  ', 1)
    assert digest(entries[relative]) == checksum

target = entries['payload/' + HOME].decode('utf-8').replace('\r\n', '\n').splitlines(keepends=True)
report_lines = REPORT.read_text(encoding='utf-8').splitlines()
first = next(i for i, line in enumerate(report_lines) if line.startswith('--- themes/'))
last = next((i for i in range(first + 2, len(report_lines)) if report_lines[i].startswith('noble@site:')), len(report_lines))
diff_lines = report_lines[first:last]
hunks = []
for line in diff_lines[2:]:
    match = re.fullmatch(r'@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@.*', line)
    if match:
        old_start, old_count, new_start, new_count = match.groups()
        hunks.append({'old_start': int(old_start) - 1, 'old_count': int(old_count or 1),
                      'new_start': int(new_start) - 1, 'new_count': int(new_count or 1), 'lines': []})
    else:
        # The pasted transcript strips the context prefix on empty diff lines.
        if line == '':
            line = ' '
        assert hunks and line and line[0] in ' +-', repr(line)
        hunks[-1]['lines'].append(line)
assert len(hunks) == 5

# Reverse the exact reviewed diff against the already delivered payload.
server = []
cursor = 0
for hunk in hunks:
    old = [line[1:] + '\n' for line in hunk['lines'] if line[0] in ' -']
    new = [line[1:] + '\n' for line in hunk['lines'] if line[0] in ' +']
    assert len(old) == hunk['old_count'] and len(new) == hunk['new_count']
    start = hunk['new_start']
    assert start >= cursor and target[start:start + len(new)] == new, 'Diff does not match the delivered payload.'
    server.extend(target[cursor:start])
    assert len(server) == hunk['old_start']
    server.extend(old)
    cursor = start + len(new)
server.extend(target[cursor:])
server_bytes = ''.join(server).encode('utf-8')

# Round-trip the reviewed change to prove the reconstructed source is complete.
forward = []
cursor = 0
for hunk in hunks:
    old = [line[1:] + '\n' for line in hunk['lines'] if line[0] in ' -']
    new = [line[1:] + '\n' for line in hunk['lines'] if line[0] in ' +']
    start = hunk['old_start']
    assert server[start:start + len(old)] == old
    forward.extend(server[cursor:start])
    forward.extend(new)
    cursor = start + len(old)
forward.extend(server[cursor:])
assert forward == target

AUDIT.mkdir(parents=True, exist_ok=True)
(AUDIT / 'reviewed-server-home.blade.php').write_bytes(server_bytes)
(AUDIT / 'server-to-release.diff').write_text('\n'.join(diff_lines) + '\n', encoding='utf-8', newline='\n')
manifest = json.loads(entries['PATCH-MANIFEST.json'])
original_manifest = json.loads(entries['PATCH-MANIFEST.json'])
manifest['release'] = RELEASE
manifest['created_at'] = datetime.now(timezone.utc).isoformat(timespec='seconds')
manifest['supersedes'] = original_manifest['release']
manifest['reviewed_server_homepage'] = {
    'path': HOME, 'lf_sha256': digest(server_bytes), 'source': 'User-supplied complete unified server-to-release diff',
    'review': 'Contact/email/map behavior is unchanged; homepage differences are formatting and the new Courses URL helper/link. The server already uses the current aboutus slug.'
}
manifest['guard_files'][HOME]['previous_versions'].append({'sha256': digest(server_bytes), 'lf_sha256': digest(server_bytes)})
assert manifest['files'] == original_manifest['files']
assert entries['install.php'] == (ROOT / 'scripts/latest-update-install.php').read_bytes().replace(b'\r\n', b'\n')
commands = '''# Upload sch_of_slsc-latest-update-2026-10-23-v2.zip to /var/www/html/sch_of_slsc/
# The earlier attempt stopped during --check and did not install the payload.
cd /var/www/html/sch_of_slsc &&
unzip sch_of_slsc-latest-update-2026-10-23-v2.zip -d update-20261023-latest-v2 &&
php update-20261023-latest-v2/install.php "$PWD" --check &&
php update-20261023-latest-v2/install.php "$PWD"

# If that directory exists, use a fresh directory name in all three paths.
# Keep the private backup directory printed by a successful installation.
# Optional code restore, using that exact printed backup directory:
# php update-20261023-latest-v2/install.php "$PWD" --restore-code /absolute/path/to/backup-directory
'''
entries['RUN-COMMANDS.txt'] = commands.encode()
entries['PATCH-MANIFEST.json'] = (json.dumps(manifest, indent=2) + '\n').encode()
readme = entries['README.md'].decode('utf-8').replace('sch_of_slsc-latest-update-2026-10-23.zip', ARCHIVE.name)
readme = readme.replace('2026-10-23-latest-update-*/', RELEASE + '-*/')
readme = '''# Revision 2: reviewed server homepage compatibility

This replaces the first latest-update ZIP, whose preflight stopped on home.blade.php.
It adds the exact reviewed server homepage LF hash to the recognized versions.
The payload (all 306 files) and installer are byte-for-byte identical to that ZIP.
Unknown server changes still stop the installer. The original homepage bytes are
backed up before installing the current homepage, just like other overwritten files.
Use this ZIP and its separate commands file, extracting into the new v2 directory.

''' + readme
entries['README.md'] = readme.encode('utf-8')

# Exercise the real preflight on the reconstructed server file. Do not boot Laravel.
with tempfile.TemporaryDirectory(prefix='qa-home-v2-', dir=ROOT / 'deploy/audits') as temporary:
    fixture = Path(temporary).resolve()
    assert fixture.is_relative_to((ROOT / 'deploy/audits').resolve())
    app = fixture / 'app'
    release = fixture / 'release'
    for name, data in entries.items():
        if name.startswith('payload/'):
            destination = app / name.removeprefix('payload/')
            destination.parent.mkdir(parents=True, exist_ok=True)
            destination.write_bytes(data)
    (app / HOME).write_bytes(server_bytes)
    (app / 'vendor').mkdir()
    (app / 'vendor/autoload.php').write_text('<?php throw new RuntimeException("Preflight must not boot Laravel");')
    (app / 'artisan').write_text('<?php throw new RuntimeException("Preflight must not run artisan");')
    (app / '.env').write_text('APP_ENV=testing\n')
    shutil.copyfile(ROOT / 'composer.json', app / 'composer.json')
    shutil.copyfile(ROOT / 'composer.lock', app / 'composer.lock')

    def write_release():
        checksums = ''.join(f'{digest(data)}  {name}\n' for name, data in sorted(entries.items()) if name != 'PACKAGE.sha256')
        entries['PACKAGE.sha256'] = checksums.encode()
        for name, data in entries.items():
            path = release / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(data)

    def preflight():
        result = subprocess.run(['php', '-d', 'xdebug.mode=off', str(release / 'install.php'), str(app), '--check'],
                                cwd=ROOT, capture_output=True, text=True, timeout=90)
        return result.returncode, result.stdout + result.stderr

    write_release()
    status, output = preflight()
    assert status == 0 and 'Package verified' in output, output
    assert (app / HOME).read_bytes() == server_bytes
    (app / HOME).write_bytes(server_bytes.replace(b'\n', b'\r\n'))
    status, output = preflight()
    assert status == 0, output
    (app / HOME).write_bytes(server_bytes + b'\n<!-- unreviewed edit -->\n')
    status, output = preflight()
    assert status != 0 and HOME in output, output

validation = json.loads(entries['VALIDATION.json'])
validation['revision_2'] = {
    'result': 'passed', 'reviewed_homepage_lf_sha256': digest(server_bytes),
    'checks': ['Strict reverse and forward application of all five supplied diff hunks',
               'Actual installer preflight accepts reviewed server homepage with LF and CRLF',
               'Actual installer preflight rejects an additional unreviewed homepage edit',
               'Preflight does not boot Laravel or copy application files',
               'All 306 payload files and installer are identical to the previously tested release'],
    'prior_validation': 'Application/build/SQLite install and restore tests were run on the identical original payload and installer.'
}
entries['VALIDATION.json'] = (json.dumps(validation, indent=2) + '\n').encode()
entries['PACKAGE.sha256'] = ''.join(f'{digest(data)}  {name}\n' for name, data in sorted(entries.items()) if name != 'PACKAGE.sha256').encode()
STAGE.mkdir(parents=True, exist_ok=True)
for name, data in entries.items():
    path = STAGE / name
    assert path.resolve().is_relative_to(STAGE.resolve())
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_bytes(data)
assert {p.relative_to(STAGE).as_posix() for p in STAGE.rglob('*') if p.is_file()} == set(entries)
with ZipFile(ARCHIVE, 'w', ZIP_DEFLATED, compresslevel=9) as archive:
    for name, data in sorted(entries.items()):
        archive.writestr(name, data)
with ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None
    for name, data in entries.items():
        assert archive.read(name) == data
ARCHIVE.with_suffix('.zip.sha256').write_text(digest(ARCHIVE.read_bytes()) + '  ' + ARCHIVE.name + '\n', encoding='ascii')
ARCHIVE.with_name(ARCHIVE.stem + '-COMMANDS.txt').write_text(commands, encoding='utf-8', newline='\n')
(AUDIT / 'REVIEW.json').write_text(json.dumps(manifest['reviewed_server_homepage'], indent=2) + '\n')
print(json.dumps({'archive': str(ARCHIVE), 'bytes': ARCHIVE.stat().st_size, 'sha256': digest(ARCHIVE.read_bytes()),
                  'reviewed_home_lf_sha256': digest(server_bytes), 'preflight_tests': 'passed'}, indent=2))

"""Exercise the actual installer on an isolated Laravel/SQLite application."""
from hashlib import sha256
import json
import os
from pathlib import Path
import shutil
import sqlite3
import subprocess
import tempfile
import uuid
from zipfile import ZipFile

ROOT = Path(__file__).resolve().parents[1]
STAGE = ROOT / 'deploy/updates/2026-10-23-latest-update'
OLD = ROOT / 'deploy/updates/2026-10-23-server-update'
manifest = json.loads((STAGE / 'PATCH-MANIFEST.json').read_text())
previous = json.loads((OLD / 'PATCH-MANIFEST.json').read_text())
checks = []


def digest(data):
    return sha256(data).hexdigest()


with tempfile.TemporaryDirectory(prefix='qa-latest-', dir=ROOT / 'deploy/audits') as temporary:
    temp = Path(temporary).resolve()
    assert temp.is_relative_to((ROOT / 'deploy/audits').resolve())
    fixture = temp / 'app'
    fixture.mkdir()
    release = temp / 'release'
    with ZipFile(STAGE.parent / 'sch_of_slsc-latest-update-2026-10-23.zip') as archive:
        archive.extractall(release)
    paths = set(previous['guard_files']) | set(manifest['files']) | {'artisan', 'composer.json', 'composer.lock'}
    for relative in paths:
        source = ROOT / relative
        if source.is_file() and relative not in manifest['migrations'][1:]:
            target = fixture / relative
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(source, target)
    for directory in ['vendor/composer', 'storage/framework/cache/data', 'storage/framework/views',
                      'storage/framework/sessions', 'storage/logs', 'storage/app', 'bootstrap/cache']:
        (fixture / directory).mkdir(parents=True, exist_ok=True)
    shutil.copyfile(ROOT / 'vendor/composer/installed.json', fixture / 'vendor/composer/installed.json')
    # Use the existing dependencies but load fixture App/Database classes instead of live project classes.
    (fixture / 'vendor/autoload.php').write_text('''<?php
$loader = require ''' + repr((ROOT / 'vendor/autoload.php').as_posix()) + ''';
$base = dirname(__DIR__);
$map = [];
foreach ($loader->getClassMap() as $class => $path) {
    foreach (['App\\\\' => 'app/', 'Database\\\\Seeders\\\\' => 'database/seeders/', 'Database\\\\Factories\\\\' => 'database/factories/'] as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $map[$class] = $base.'/'.$directory.str_replace('\\\\', '/', substr($class, strlen($prefix))).'.php';
        }
    }
}
$loader->addClassMap($map);
$loader->setPsr4('App\\\\', [$base.'/app']);
$loader->setPsr4('Database\\\\Seeders\\\\', [$base.'/database/seeders']);
$loader->setPsr4('Database\\\\Factories\\\\', [$base.'/database/factories']);
return $loader;
''', encoding='utf-8')
    db = fixture / 'database/database.sqlite'
    db.touch()
    (fixture / '.env').write_text('\n'.join([
        'APP_NAME=DeploymentQA', 'APP_ENV=testing', 'APP_DEBUG=false',
        'APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=', 'APP_URL=http://localhost',
        'DB_CONNECTION=sqlite', 'DB_DATABASE="' + db.as_posix() + '"', 'DB_URL=',
        'SESSION_DRIVER=array', 'CACHE_STORE=array', 'QUEUE_CONNECTION=sync',
        'APP_MAINTENANCE_DRIVER=file', 'MAIL_MAILER=array',
    ]) + '\n', encoding='utf-8')
    environment = {key: value for key, value in os.environ.items() if not key.startswith(('APP_', 'DB_', 'CACHE_', 'SESSION_'))}
    environment['XDEBUG_MODE'] = 'off'

    def run(*arguments, success=True):
        print('Checking: ' + ' '.join(map(str, arguments[:2])), flush=True)
        result = subprocess.run(['php', '-d', 'xdebug.mode=off', *map(str, arguments)], cwd=fixture,
                                env=environment, text=True, capture_output=True, timeout=180)
        if (result.returncode == 0) != success:
            raise AssertionError(f'Unexpected exit {result.returncode}: {arguments}\n{result.stdout}\n{result.stderr}')
        return result.stdout + result.stderr

    def install(mode=None, *extra, success=True):
        return run(release / 'install.php', fixture, *([mode] if mode else []), *extra, success=success)

    def query(sql, parameters=()):
        connection = sqlite3.connect(db)
        try:
            result = connection.execute(sql, parameters).fetchall()
            connection.commit()
            return result
        finally:
            connection.close()

    run('artisan', 'migrate', '--force', '--no-ansi')
    query("INSERT INTO users(name,email,password,created_at,updated_at) VALUES('Retained user','retained@example.invalid','dummy','2026-10-23','2026-10-23')")
    headers = [
        ('en', '<nav><a href="[[site_url:/galleries]]">Gallery</a><a href="[[site_url:/galleries]]">Publication</a></nav>'),
        ('si', '<nav><a href="[[site_url:/si/galleries]]">ඡායාරූප</a></nav>'),
    ]
    for locale, html in headers:
        query('INSERT INTO theme_layouts(uuid,theme_slug,region,locale,draft_html,published_html,status,revision_number,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?)',
              (str(uuid.uuid4()), 'school-of-signals', 'header', locale, html, html, 'published', 0, '2026-10-23', '2026-10-23'))

    # Reconstruct exactly the delivered older runtime for each overwritten path.
    before = {}
    for relative, guard in manifest['guard_files'].items():
        target = fixture / relative
        older = previous['guard_files'].get(relative)
        data = None
        if older:
            old_payload = OLD / 'payload' / relative
            candidates = [old_payload.read_bytes()] if old_payload.is_file() else []
            if target.is_file():
                candidates.append(target.read_bytes())
            git_path = relative.removeprefix('public/') if relative.startswith('public/themes/') else relative
            content = subprocess.run(['git', 'show', 'HEAD:' + git_path], cwd=ROOT, capture_output=True)
            if content.returncode == 0:
                candidates.append(content.stdout)
            data = next((item for item in candidates if digest(item) == older['current']['sha256']
                         or digest(item.replace(b'\r\n', b'\n')) == older['current'].get('lf_sha256')), None)
            assert data is not None, 'Unable to reconstruct baseline: ' + relative
        if data is None:
            target.unlink(missing_ok=True)
        else:
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_bytes(data)
        if relative in manifest['files']:
            before[relative] = data
    protected = {p: (fixture / p).read_bytes() for p in ['.env', 'composer.json', 'composer.lock', 'public/.htaccess']}
    install('--check')
    checks.append('Accepts the delivered previous ZIP and missing new files without changing the database')
    route = fixture / 'routes/web.php'
    original = route.read_bytes()
    route.write_bytes(original + b'\n// unexpected hosting edit\n')
    assert 'routes/web.php' in install('--check', success=False)
    route.write_bytes(original)
    checks.append('Rejects an unexpected hosting edit before copying')
    readme = release / 'README.md'
    original_readme = readme.read_bytes()
    readme.write_bytes(original_readme + b'tampered')
    assert 'integrity' in install('--check', success=False)
    readme.write_bytes(original_readme)
    checks.append('Rejects corrupted package contents')
    query('CREATE TABLE courses(id INTEGER PRIMARY KEY)')
    assert 'partial migration' in install(success=False)
    query('DROP TABLE courses')
    assert route.read_bytes() == original
    checks.append('Rejects a partial database migration before copying')
    output = install()
    assert 'Update complete' in output, output
    backups = sorted((fixture / 'storage/app/deployment-backups').glob(manifest['release'] + '-*'))
    assert len(backups) == 1
    backup = backups[0]
    state = json.loads((backup / 'BACKUP-STATE.json').read_text())
    saved = sqlite3.connect(state['database_backup'])
    try:
        assert saved.execute("SELECT count(*) FROM users WHERE email='retained@example.invalid'").fetchone()[0] == 1
        assert saved.execute("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='publications'").fetchone()[0] == 0
    finally:
        saved.close()
    for relative, item in manifest['files'].items():
        assert digest((fixture / relative).read_bytes()) == item['sha256'], relative
    for relative, data in protected.items():
        assert (fixture / relative).read_bytes() == data, relative
    assert not (fixture / 'storage/framework/down').exists()
    assert query('SELECT count(*) FROM courses')[0][0] == 16
    assert query('SELECT count(*) FROM course_categories')[0][0] == 5
    assert query('SELECT count(*) FROM courses WHERE course_category_id IS NULL')[0][0] == 0
    assert query('SELECT count(*) FROM courses WHERE name_si IS NOT NULL')[0][0] == 16
    assert query("SELECT count(*) FROM publications")[0][0] == 0
    for locale, draft, published in query('SELECT locale,draft_html,published_html FROM theme_layouts'):
        assert '/courses' in draft and '/publications' in draft
        assert '/courses' in published and '/publications' in published
    checks.append('Installs exact payload bytes, backs up code/database, migrates publications/courses/categories and connects EN/SI headers')
    checks.append('Preserves hosting configuration, existing accounts and the original maintenance state')
    query('INSERT INTO publications(uuid,title,locale,disk,pdf_path,cover_path,original_name,size_bytes,cover_width,cover_height,is_published) VALUES(?,?,?,?,?,?,?,?,?,?,?)',
          (str(uuid.uuid4()), 'Keep this publication', 'en', 'local', 'p.pdf', 'p.jpg', 'p.pdf', 100, 600, 800, 0))
    query("INSERT INTO course_qualifications(course_id,qualification) VALUES(1,'Keep this qualification')")
    install()
    assert query('SELECT count(*) FROM courses')[0][0] == 16
    assert query('SELECT count(*) FROM publications')[0][0] == 1
    assert query('SELECT count(*) FROM course_qualifications')[0][0] == 1
    checks.append('Repeat installation skips completed migrations and retains publication/course data without duplicate starter courses')
    # A later manual header edit must survive code restoration.
    query("UPDATE theme_layouts SET draft_html=draft_html || '<p>Later edit</p>' WHERE locale='en'")
    install('--restore-code', backup)
    for relative, data in before.items():
        if data is not None:
            assert (fixture / relative).read_bytes() == data, relative
    for locale, html in headers:
        row = query('SELECT draft_html,published_html FROM theme_layouts WHERE locale=?', (locale,))[0]
        assert row[1] == html
        assert (row[0] == html) if locale == 'si' else 'Later edit' in row[0]
    assert query('SELECT count(*) FROM publications')[0][0] == 1
    assert query('SELECT count(*) FROM courses')[0][0] == 16
    assert not (fixture / 'storage/framework/down').exists()
    checks.append('Code restore returns prior bytes/header HTML, preserves later header edits and retains additive database records')

print(json.dumps({'result': 'passed', 'database_backend': 'isolated SQLite with actual Laravel migrations', 'checks': checks}, indent=2))

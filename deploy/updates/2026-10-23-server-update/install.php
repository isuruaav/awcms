<?php

// Distributed with a generated PATCH-MANIFEST.json and payload/ directory.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('PHP CLI only.');
}
umask(0022);
$release = __DIR__;
$appRoot = realpath($argv[1] ?? getcwd());
$mode = $argv[2] ?? '--install';
$backupDir = null;
$startedMaintenance = false;
$filesChanged = false;
$lock = null;

function stop(string $message): never
{
    throw new RuntimeException($message);
}

function within(string $path, string $root): bool
{
    $path = str_replace('\\', '/', $path);
    $prefix = rtrim(str_replace('\\', '/', $root), '/').'/';

    return DIRECTORY_SEPARATOR === '\\'
        ? strncasecmp($path, $prefix, strlen($prefix)) === 0
        : str_starts_with($path, $prefix);
}

function safePath(string $root, string $relative): string
{
    if ($relative === '' || str_starts_with($relative, '/') || str_contains($relative, '\\') || in_array('..', explode('/', $relative), true)) {
        stop('Invalid relative path: '.$relative);
    }
    $path = $root.'/'.$relative;
    $parent = $path;
    while (!file_exists($parent) && !is_link($parent)) {
        $parent = dirname($parent);
    }
    $resolved = realpath($parent);
    if ($resolved === false || ($resolved !== $root && !within($resolved, $root))) {
        stop('Path leaves the selected directory: '.$relative);
    }
    if (is_link($path) || (file_exists($path) && !is_file($path))) {
        stop('Expected a regular file: '.$relative);
    }

    return $path;
}

function binary(string $name): ?string
{
    foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
        foreach (DIRECTORY_SEPARATOR === '\\' ? [$name.'.exe', $name] : [$name] as $candidate) {
            $path = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$candidate;
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }
    }

    return null;
}

function runCommand(array $command, string $cwd, $output = null, ?array $environment = null): void
{
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => $output ?? STDOUT, 2 => STDERR], $pipes, $cwd, $environment);
    if (!is_resource($process)) {
        stop('Could not start '.basename($command[0]));
    }
    fclose($pipes[0]);
    $code = proc_close($process);
    if ($code !== 0) {
        stop(basename($command[0]).' failed (exit '.$code.').');
    }
}

function artisan(string $root, array $arguments): void
{
    runCommand([PHP_BINARY, $root.'/artisan', ...$arguments, '--no-ansi'], $root);
}

function matchesVersion(string $path, array $version): bool
{
    if (!is_file($path) || !is_readable($path)) {
        return false;
    }
    if (hash_file('sha256', $path) === $version['sha256']) {
        return true;
    }

    return isset($version['lf_sha256'])
        && hash('sha256', str_replace("\r\n", "\n", file_get_contents($path))) === $version['lf_sha256'];
}

function verifyPackage(string $release): array
{
    foreach (file($release.'/PACKAGE.sha256', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!preg_match('/^([a-f0-9]{64})  (.+)$/', $line, $parts)) {
            stop('Invalid package checksum list.');
        }
        $path = safePath($release, $parts[2]);
        if (!is_file($path) || hash_file('sha256', $path) !== $parts[1]) {
            stop('Package integrity check failed: '.$parts[2]);
        }
    }
    $manifest = json_decode(file_get_contents($release.'/PATCH-MANIFEST.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach ($manifest['files'] as $relative => $version) {
        if (!matchesVersion(safePath($release, 'payload/'.$relative), $version)) {
            stop('Payload check failed: '.$relative);
        }
    }

    return $manifest;
}

function verifyTarget(string $root, array $manifest): void
{
    $drift = [];
    foreach ($manifest['guard_files'] as $relative => $expected) {
        $path = safePath($root, $relative);
        $current = matchesVersion($path, $expected['current']);
        $baseline = $expected['baseline_sha256'] === null
            ? !file_exists($path)
            : is_file($path) && hash_file('sha256', $path) === $expected['baseline_sha256'];
        if (!$current && !$baseline) {
            $drift[] = $relative;
        }
    }
    if ($drift !== []) {
        stop("Server code changed since the supplied report. No payload was copied. Recompare these paths:\n".implode("\n", $drift));
    }
    $composer = json_decode(file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach (['require', 'autoload'] as $key) {
        $actual = $composer[$key] ?? [];
        $expected = $manifest['composer_compatibility'][$key];
        if ($actual != $expected) {
            stop('The hosting composer.json has different '.$key.' settings. It must be reviewed before installing.');
        }
    }
}

function bootRuntime(string $root, array $manifest): array
{
    $loader = require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $connection = $app->make('db')->connection();
    $table = $app->make('config')->get('database.migrations.table', 'migrations');
    $ran = $connection->table($table)->pluck('migration')->all();
    $missing = array_values(array_diff($manifest['required_existing_migrations'], $ran));
    if ($missing !== []) {
        stop('Missing prerequisite migrations: '.implode(', ', $missing));
    }
    $applied = in_array(pathinfo($manifest['migration'], PATHINFO_FILENAME), $ran, true);
    $column = $connection->getSchemaBuilder()->hasColumn('media_assets', 'public_filename');
    if ($applied !== $column) {
        stop('The media filename migration and database column disagree. Inspect this partial migration before installing.');
    }
    $driver = $connection->getDriverName();
    if (!in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
        stop('Automatic database backup supports MySQL/MariaDB and SQLite. This server uses '.$driver.'.');
    }
    $dumper = null;
    if ($driver !== 'sqlite') {
        $dumper = binary('mariadb-dump') ?? binary('mysqldump');
        if ($dumper === null) {
            stop('Install/enable mariadb-dump or mysqldump in the server CLI PATH before installing. No payload was copied.');
        }
    }
    $composer = null;
    if ($loader->isClassMapAuthoritative()) {
        $composer = binary('composer');
        if ($composer === null) {
            stop('This server uses an authoritative Composer class map. Composer CLI is required.');
        }
    }

    return compact('app', 'connection', 'applied', 'driver', 'dumper', 'composer');
}

function backupDatabase(array $runtime, string $backup, string $root): string
{
    $connection = $runtime['connection'];
    if ($runtime['driver'] === 'sqlite') {
        $file = $backup.'/database.sqlite';
        $connection->getPdo()->exec('VACUUM INTO '.$connection->getPdo()->quote($file));
    } else {
        $config = $connection->getConfig();
        $file = $backup.'/database.sql';
        $database = (string) $config['database'];
        if (!preg_match('/^[A-Za-z0-9_$-]+$/', $database) || str_starts_with($database, '-')) {
            stop('Database name needs a hosting-specific backup command.');
        }
        $command = [$runtime['dumper'], '--single-transaction', '--quick', '--skip-lock-tables', '--hex-blob', '--no-tablespaces', '--user='.(string) $config['username']];
        if (!empty($config['unix_socket'])) {
            $command[] = '--socket='.$config['unix_socket'];
        } else {
            $host = $config['host'] ?? '127.0.0.1';
            if (is_array($host)) {
                stop('Multiple database hosts require a hosting-specific backup command.');
            }
            $command[] = '--host='.$host;
            $command[] = '--port='.(string) ($config['port'] ?? 3306);
        }
        foreach (['CA' => 'ca', 'CERT' => 'cert', 'KEY' => 'key'] as $attribute => $option) {
            $constant = 'PDO::MYSQL_ATTR_SSL_'.$attribute;
            if (defined($constant) && !empty($config['options'][constant($constant)])) {
                $command[] = '--ssl-'.$option.'='.$config['options'][constant($constant)];
            }
        }
        $command[] = $database;
        $environment = getenv();
        $environment['MYSQL_PWD'] = (string) ($config['password'] ?? '');
        $stream = fopen($file.'.partial', 'xb');
        if ($stream === false) {
            stop('Could not create the database backup.');
        }
        try {
            runCommand($command, $root, $stream, $environment);
        } finally {
            fclose($stream);
        }
        if (!rename($file.'.partial', $file)) {
            stop('Could not finish the database backup.');
        }
    }
    chmod($file, 0600);
    if (filesize($file) === 0) {
        stop('Database backup is empty.');
    }

    return $file;
}

try {
    if (PHP_VERSION_ID < 80300 || !function_exists('proc_open')) {
        stop('PHP CLI 8.3+ with proc_open is required.');
    }
    if ($appRoot === false || !is_file($appRoot.'/artisan') || !is_file($appRoot.'/vendor/autoload.php') || !is_file($appRoot.'/.env')) {
        stop('Select the existing Laravel root containing artisan, .env and vendor/autoload.php.');
    }
    if (!in_array($mode, ['--install', '--check', '--restore-code'], true) || count($argv) > ($mode === '--restore-code' ? 4 : 3)) {
        stop('Usage: php install.php /absolute/laravel/root [--check | --restore-code /absolute/backup/directory]');
    }
    $manifest = verifyPackage($release);
    $tar = binary('tar');
    if ($tar === null) {
        stop('The tar command is required for code backups.');
    }
    if ($mode === '--restore-code') {
        $backupDir = realpath($argv[3] ?? '');
        if ($backupDir === false || !within($backupDir, $appRoot.'/storage/app/deployment-backups')) {
            stop('Select this release backup under storage/app/deployment-backups.');
        }
        $state = json_decode(file_get_contents($backupDir.'/BACKUP-STATE.json'), true, 512, JSON_THROW_ON_ERROR);
        if ($state['release'] !== $manifest['release'] || $state['app_root'] !== $appRoot) {
            stop('Backup belongs to a different release or application root.');
        }
        $lock = fopen(safePath($appRoot, 'storage/app/deployment-backups/.server-update.lock'), 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            stop('Another server update or restore is running.');
        }
        foreach ($state['files'] as $relative => $oldHash) {
            $path = safePath($appRoot, $relative);
            $actual = is_file($path) ? hash_file('sha256', $path) : null;
            if ($actual !== $oldHash && $actual !== $manifest['files'][$relative]['sha256']) {
                stop('A file changed after installation; review before restoring: '.$relative);
            }
        }
        // File-based maintenance can be recognized without booting broken code.
        if (($state['maintenance_driver'] ?? '') !== 'file' || !is_file($appRoot.'/storage/framework/down')) {
            artisan($appRoot, ['down', '--retry=60']);
        }
        $filesChanged = true;
        runCommand([$tar, '-xzf', $backupDir.'/files-before-update.tar.gz', '-C', $appRoot], $appRoot);
        artisan($appRoot, ['route:clear']);
        artisan($appRoot, ['view:clear']);
        if (!$state['was_down']) {
            artisan($appRoot, ['up']);
        }
        echo "Previous code restored. Added files and the additive database migration were retained.\n";
        echo "Full database backup (not automatically restored): ".($state['database_backup'] ?? 'not created')."\n";
        exit(0);
    }
    verifyTarget($appRoot, $manifest);
    foreach ($manifest['files'] as $relative => $version) {
        if (str_ends_with($relative, '.php') && !str_ends_with($relative, '.blade.php')) {
            $quiet = fopen('php://temp', 'w+');
            try {
                runCommand([PHP_BINARY, '-l', $release.'/payload/'.$relative], $appRoot, $quiet);
            } finally {
                fclose($quiet);
            }
        }
    }
    if ($mode === '--check') {
        echo "Package verified; all ".count($manifest['guard_files'])." target paths match the report or current release.\n";
        echo "Composer requirements/autoload are compatible. No payload copied or migrations run.\n";
        echo "Database backup access and migration prerequisites are checked when installing, before copying files.\n";
        exit(0);
    }
    $runtime = bootRuntime($appRoot, $manifest);
    $backupRoot = $appRoot.'/storage/app/deployment-backups';
    $resolvedParent = realpath($appRoot.'/storage/app');
    if ($resolvedParent === false || !within($resolvedParent, $appRoot)) {
        stop('The private storage/app backup directory must stay inside this application.');
    }
    if (!is_dir($backupRoot) && !mkdir($backupRoot, 0700, true)) {
        stop('Could not create the private backup directory.');
    }
    if (is_link($backupRoot) || !within(realpath($backupRoot), $appRoot)) {
        stop('Unsafe backup directory.');
    }
    $lock = fopen(safePath($appRoot, 'storage/app/deployment-backups/.server-update.lock'), 'c');
    if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
        stop('Another server update is running.');
    }
    verifyTarget($appRoot, $manifest);
    $backupDir = $backupRoot.'/'.$manifest['release'].'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
    if (!mkdir($backupDir, 0700)) {
        stop('Could not create the release backup.');
    }
    $state = ['release' => $manifest['release'], 'app_root' => $appRoot, 'was_down' => $runtime['app']->isDownForMaintenance(), 'maintenance_driver' => $runtime['app']->make('config')->get('app.maintenance.driver', 'file'), 'files' => [], 'migration_previously_applied' => $runtime['applied']];
    $existing = [];
    foreach ($manifest['files'] as $relative => $version) {
        $path = safePath($appRoot, $relative);
        $state['files'][$relative] = is_file($path) ? hash_file('sha256', $path) : null;
        if (is_file($path)) {
            $existing[] = $relative;
        }
    }
    if ($runtime['composer'] !== null) {
        if (is_link($appRoot.'/vendor/composer') || !within(realpath($appRoot.'/vendor/composer'), $appRoot)) {
            stop('Composer metadata must stay inside this application for backup.');
        }
        $existing[] = 'vendor/composer';
    }
    file_put_contents($backupDir.'/existing-files.txt', implode("\n", $existing)."\n");
    file_put_contents($backupDir.'/BACKUP-STATE.json', json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    runCommand([$tar, '-czf', $backupDir.'/files-before-update.tar.gz', '-C', $appRoot, '-T', $backupDir.'/existing-files.txt'], $appRoot);
    echo "Code backup: $backupDir/files-before-update.tar.gz\n";
    if (!$state['was_down']) {
        artisan($appRoot, ['down', '--retry=60']);
        $startedMaintenance = true;
    }
    $state['database_backup'] = backupDatabase($runtime, $backupDir, $appRoot);
    file_put_contents($backupDir.'/BACKUP-STATE.json', json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    echo "Database backup: ".$state['database_backup']."\n";
    foreach ($manifest['files'] as $relative => $version) {
        $target = safePath($appRoot, $relative);
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true)) {
            stop('Could not create directory for '.$relative);
        }
        $filesChanged = true;
        if (!copy($release.'/payload/'.$relative, $target)) {
            stop('Could not copy '.$relative);
        }
        if ($state['files'][$relative] === null) {
            chmod($target, 0644);
        }
        if (hash_file('sha256', $target) !== $version['sha256']) {
            stop('Copied file check failed: '.$relative);
        }
    }
    if ($runtime['composer'] !== null) {
        runCommand([$runtime['composer'], 'dump-autoload', '--no-dev', '--classmap-authoritative', '--no-scripts', '--no-interaction'], $appRoot);
    }
    artisan($appRoot, ['migrate', '--path='.$manifest['migration'], '--force']);
    artisan($appRoot, ['route:clear']);
    artisan($appRoot, ['view:clear']);
    if (!$runtime['connection']->getSchemaBuilder()->hasColumn('media_assets', 'public_filename')) {
        stop('The media filename database column is missing after migration.');
    }
    if ($startedMaintenance) {
        artisan($appRoot, ['up']);
        $startedMaintenance = false;
    }
    echo "Update complete: ".count($manifest['files'])." files. Hosting .env, .htaccess and composer.json preserved.\n";
    echo "Backup directory: $backupDir\n";
    echo "Refresh with Ctrl+F5; check media Copy Path, News/Page Yes/No actions and leadership pages.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "STOPPED: ".$error->getMessage()."\n");
    if ($startedMaintenance && !$filesChanged) {
        try {
            artisan($appRoot, ['up']);
        } catch (Throwable $restoreError) {
            fwrite(STDERR, "Could not leave maintenance mode: ".$restoreError->getMessage()."\n");
        }
    } elseif ($filesChanged) {
        fwrite(STDERR, "Keep the application in maintenance mode until the error is fixed or code is restored.\n");
    }
    if ($backupDir !== null) {
        fwrite(STDERR, "Backup directory: $backupDir\n");
        fwrite(STDERR, "Code restore: php ".$release.'/install.php '.escapeshellarg($appRoot).' --restore-code '.escapeshellarg($backupDir)."\n");
    }
    exit(1);
} finally {
    if (is_resource($lock)) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

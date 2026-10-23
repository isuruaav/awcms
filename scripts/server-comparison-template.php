<?php

// Compare code only. This script does not load Laravel, read .env, or write files.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this file using PHP CLI.');
}

$manifest = json_decode(<<<'MANIFEST'
__SERVER_COMPARISON_MANIFEST__
MANIFEST, true, 512, JSON_THROW_ON_ERROR);

$root = realpath($argv[1] ?? getcwd());
if ($root === false || !is_file($root.'/artisan') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "Pass the existing Laravel project root containing artisan and bootstrap/app.php.\n");
    exit(1);
}

function insideRoot(string $path, string $root): bool
{
    $path = str_replace('\\', '/', $path);
    $prefix = rtrim(str_replace('\\', '/', $root), '/').'/';
    if (DIRECTORY_SEPARATOR === '\\') {
        return strncasecmp($path, $prefix, strlen($prefix)) === 0;
    }

    return strncmp($path, $prefix, strlen($prefix)) === 0;
}

function ignoredPath(string $path): bool
{
    return str_starts_with($path, 'bootstrap/cache/')
        || in_array('.env', explode('/', $path), true)
        || in_array('.git', explode('/', $path), true)
        || in_array('node_modules', explode('/', $path), true)
        || in_array('vendor', explode('/', $path), true)
        || str_ends_with($path, '/.gitignore');
}

$counts = array_fill_keys(['current', 'known_previous', 'different', 'missing', 'unsafe_or_unreadable'], 0);
$differences = [];
$migrationFiles = [];
foreach ($manifest['files'] as $relative => $expected) {
    if (str_contains($relative, '..') || str_starts_with($relative, '/') || str_contains($relative, '\\')) {
        fwrite(STDERR, "Invalid manifest path.\n");
        exit(1);
    }
    $path = $root.'/'.$relative;
    $status = 'missing';
    $detail = ['path' => $relative];
    if (file_exists($path) || is_link($path)) {
        $resolved = realpath($path);
        if ($resolved === false || !insideRoot($resolved, $root) || !is_file($path) || !is_readable($path)) {
            $status = 'unsafe_or_unreadable';
        } else {
            $hash = hash_file('sha256', $path);
            $lfHash = $hash;
            if (isset($expected['lf_sha256'])) {
                $contents = file_get_contents($path);
                $lfHash = $contents === false ? false : hash('sha256', str_replace("\r\n", "\n", $contents));
            }
            $detail['server_sha256'] = $hash;
            if ($hash === $expected['sha256'] || (isset($expected['lf_sha256']) && $lfHash === $expected['lf_sha256'])) {
                $status = 'current';
            } else {
                $status = 'different';
                foreach ($expected['previous'] ?? [] as $previous) {
                    if ($hash === $previous['sha256'] || (isset($previous['lf_sha256']) && $lfHash === $previous['lf_sha256'])) {
                        $status = 'known_previous';
                        $detail['matches_package'][] = $previous['release'];
                    }
                }
            }
        }
    }
    ++$counts[$status];
    if ($status !== 'current') {
        $detail['status'] = $status;
        $differences[] = $detail;
    }
    if (str_starts_with($relative, 'database/migrations/2026_10_')) {
        $migrationFiles[] = ['path' => $relative, 'file_status' => $status, 'database_applied_status' => 'not_checked'];
    }
}

$serverOnly = [];
$scanIssues = [];
$visited = 0;
foreach ($manifest['directories'] as $directory) {
    $base = $root.'/'.$directory;
    if (!is_dir($base)) {
        continue;
    }
    $resolved = realpath($base);
    if ($resolved === false || !insideRoot($resolved, $root) || is_link($base)) {
        $scanIssues[] = $directory;
        continue;
    }
    try {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (++$visited > 20000) {
                $scanIssues[] = 'File scan limit reached; server-only listing is incomplete.';
                break 2;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if (ignoredPath($relative)) {
                continue;
            }
            $resolved = $file->getRealPath();
            if ($file->isLink() || $resolved === false || !insideRoot($resolved, $root)) {
                $scanIssues[] = $relative;
            } elseif ($file->isFile() && !isset($manifest['files'][$relative])) {
                $serverOnly[] = $relative;
            }
        }
    } catch (Throwable $error) {
        $scanIssues[] = $directory.': could not scan all entries';
    }
}
sort($serverOnly);
$report = [
    'project' => 'sch_of_slsc',
    'reference' => $manifest['reference'],
    'generated_at_utc' => gmdate('c'),
    'php_version' => PHP_VERSION,
    'local_reference_files' => count($manifest['files']),
    'counts' => $counts,
    'differences' => $differences,
    'server_only_in_compared_directories' => $serverOnly,
    'scan_issues' => $scanIssues,
    'recent_migration_files' => $migrationFiles,
    'notes' => [
        'Comparison only. No application files or database records were changed.',
        'Different files can contain hosting edits. Review before replacement.',
        'Server-only files are listed for review, not deletion. Old hashed assets can be harmless.',
        'Current means equal bytes or equal CRLF/LF-normalized text; it does not prove database compatibility.',
        'Database migration state, saved content, installed dependencies, .env, uploads and runtime caches were not inspected.',
        'Also collect: php artisan migrate:status --no-ansi',
    ],
];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR).PHP_EOL;
exit($counts['unsafe_or_unreadable'] > 0 || $scanIssues !== [] ? 2 : 0);

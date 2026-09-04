<?php

namespace App\Services;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class ThemeAssetPublisher
{
    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = [
        'css',
        'gif',
        'ico',
        'jpeg',
        'jpg',
        'js',
        'png',
        'svg',
        'ttf',
        'webp',
        'woff',
        'woff2',
    ];

    private const MAX_FILE_BYTES = 25 * 1024 * 1024;

    public function __construct(
        private readonly ThemeManager $themeManager,
    ) {}

    public function publish(string $slug): int
    {
        if ($this->themeManager->find($slug) === null) {
            throw new RuntimeException('The requested theme is not installed or its manifest is invalid.');
        }

        $source = base_path('themes'.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'assets');

        if (! is_dir($source)) {
            throw new RuntimeException('The theme assets directory does not exist.');
        }

        $destination = public_path('themes'.DIRECTORY_SEPARATOR.$slug.DIRECTORY_SEPARATOR.'assets');

        $this->ensureDirectory($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $source,
                FilesystemIterator::SKIP_DOTS,
            ),
        );

        $published = 0;

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            if ($file->isLink()) {
                throw new RuntimeException('Theme asset symbolic links are not allowed.');
            }

            $extension = mb_strtolower($file->getExtension());

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $size = $file->getSize();

            if ($size === false || $size > self::MAX_FILE_BYTES) {
                throw new RuntimeException('A theme asset exceeds the 25 MB per-file limit.');
            }

            $sourcePath = $file->getPathname();
            $relativePath = ltrim(
                str_replace('\\', '/', substr($sourcePath, strlen($source))),
                '/',
            );

            if (! $this->isSafeRelativePath($relativePath)) {
                throw new RuntimeException('An unsafe theme asset path was rejected.');
            }

            $targetPath = $destination.DIRECTORY_SEPARATOR.str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $relativePath,
            );

            $this->ensureDirectory(dirname($targetPath));

            if (! copy($sourcePath, $targetPath)) {
                throw new RuntimeException('Unable to publish theme asset: '.$relativePath);
            }

            $published++;
        }

        return $published;
    }

    private function ensureDirectory(string $directory): void
    {
        if (
            ! is_dir($directory)
            && ! mkdir($directory, 0755, true)
            && ! is_dir($directory)
        ) {
            throw new RuntimeException('Unable to create the theme asset directory.');
        }
    }

    private function isSafeRelativePath(string $path): bool
    {
        return $path !== ''
            && ! str_contains($path, '..')
            && preg_match('/\A[a-zA-Z0-9_\/. -]+\z/', $path) === 1;
    }
}

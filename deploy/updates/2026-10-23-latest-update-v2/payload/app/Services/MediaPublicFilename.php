<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class MediaPublicFilename
{
    public function unique(string $originalName): string
    {
        $name = basename(str_replace('\\', '/', trim($originalName)));
        $stem = pathinfo($name, PATHINFO_FILENAME);
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        $stem = preg_replace('/[^\p{L}\p{M}\p{N}._-]+/u', '-', $stem) ?? '';
        $stem = mb_substr(trim($stem, '.-'), 0, 200);
        $stem = $stem !== '' ? $stem : 'file';
        $extension = substr(preg_replace('/[^a-zA-Z0-9]/', '', $extension) ?? '', 0, 16);
        $extension = $extension !== '' ? '.'.$extension : '';

        $candidate = $stem.$extension;
        $suffix = 1;

        // Keep names reserved for private and trashed files so old links cannot
        // later point at a different upload with the same original filename.
        while (DB::table('media_assets')->where('public_filename', $candidate)->exists()) {
            $candidate = $stem.'-'.++$suffix.$extension;
        }

        return $candidate;
    }
}

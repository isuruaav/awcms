# Server comparison: 23 October 2026

The existing server version is unknown. This is a comparison tool, not an installer.
It reads 449 local-reference paths and reports matching, older, different,
missing and server-only code files. File contents and secrets are not included in
the report. It does not load Laravel, read .env, connect to a database, or modify
the application. Run using PHP CLI 8.0+.

1. Upload only `compare-server-2026-10-23.php` into the existing Laravel root, next to `artisan`.
   Keep the script outside the public directory.
2. Open a server terminal in that Laravel root and run:

```bash
php compare-server-2026-10-23.php . > server-comparison-2026-10-23.json
php artisan migrate:status --no-ansi > server-migrations-2026-10-23.txt
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

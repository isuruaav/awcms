# News Trash preview and permanent deletion fix — 2026-10-22

Trash now offers Preview, Restore and Delete Permanently on desktop and mobile. The existing `news.delete` permission controls both restore and permanent deletion. Only trashed articles can be permanently deleted; the action records an audit event and removes the article's revisions/image links. Shared Media Library assets/files and other language versions remain available.

The authenticated admin preview route accepts soft-deleted articles and shows a read-only Trash notice with a Back to Trash link. Public news URLs and editing trashed articles still return 404. Permanent deletion requires confirmation in the interface and cannot be undone through Restore.

## Files

This incremental package replaces five files listed in FILES.txt. Use it with the existing installation that matches the current local sch_of_slsc project. Preserve any hosting-only edits before replacing full files.

No migration, permission seeder, Composer install, npm build, .env changes or uploaded media replacement is required. Tests and application data are excluded.

## Apply on the hosting server

From the folder containing the ZIP, run the following as the application owner. It extracts into a separate directory and backs up the five files before replacement:

```bash
unzip sch_of_slsc-news-trash-fix-2026-10-22.zip -d sch-news-trash-fix
cd sch-news-trash-fix || exit 1

news_app_root=/var/www/html/sch_of_slsc
test -f "$news_app_root/artisan" && test -f "$news_app_root/composer.json" || exit 1
(cd payload && sha256sum -c ../FILES.sha256) || exit 1

news_backup_dir="$news_app_root/storage/app/deployment-backups/news-trash-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$news_backup_dir" || exit 1
tar -czf "$news_backup_dir/files.tar.gz" -C "$news_app_root" --files-from="$PWD/FILES.txt" || exit 1
cp -R payload/. "$news_app_root/" || exit 1

cd "$news_app_root" || exit 1
php artisan route:clear
php artisan view:clear
```

Alternatively, upload the contents of payload/ into the Laravel project root, preserving paths, after backing up the five existing files. Then clear routes and views with the two Artisan commands above. Do not put the application code into public/.

Refresh the News page, choose Trash, and check Preview and Restore. Use only a disposable test article to check Delete Permanently. A code backup does not recover permanently deleted database records; normal database backups are separate.

## Validation

- New NewsTrashTest: 8 tests, 39 assertions passed. Covers preview, authorization, confirmation UI, deletion guard, dependent row cleanup, shared media/translation preservation and public/edit isolation.
- Related news regression run: 40 of 41 tests passed. One existing PublicNewsLanguageTest assertion expects a Tamil language-switcher URL, while the School of Signals theme is configured for English/Sinhala. This patch changes neither the public news template nor the theme language configuration.
- Targeted PHPStan: zero errors. Targeted Pint: passed. Routes PHP syntax: passed.

This package was prepared locally; it has not been installed or tested on the hosting server by the assistant.

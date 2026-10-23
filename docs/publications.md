# Publications

Open **Admin → Publications → Upload Publication**. Select the publication
language, then select its PDF (up to 20 MB) and manually upload its matching
first-page JPG/JPEG cover image (up to 2 MB). Enter a title in the selected
language and save. Upload
English and Sinhala editions as separate publication records, each with its own
PDF and manually selected JPG/JPEG first-page cover image (up to 2 MB). Upload
the PDF first, then its matching cover image. The filename supplies an editable
title when the title is blank. Use a PDF without a password.

Users with document publishing permission can publish immediately or return an
item to draft. Other document editors can save drafts. Uploads also require the
existing media upload permission. Publications use the same role permissions as
Document Uploads; no existing role grants are changed.

The public `/publications` and `/si/publications` pages show five publications
per page, newest first, filtered to English and Sinhala editions respectively.
Covers, titles and Read PDF links open the matching language's PDF in a new tab.
The centered page heading follows the gallery hero design. PDFs and covers live
on the private media disk and are served through publication routes; draft,
deleted and future publications are not public.

The `public/.user.ini` settings allow the 20 MB PDF plus multipart request
overhead. On Apache with `mod_fcgid`, ensure the virtual host's
`FcgidMaxRequestLen` is at least 25165824 bytes; this server-level directive
cannot be set in `.user.ini`.

Editing a title retains the existing files. A replacement PDF requires a
matching cover image; either file can also be replaced independently when
editing an existing publication. Superseded files are removed after the database
save succeeds. Deleting an item soft-deletes the publication; retained private
files do not become public.

## Deployment

Include the application files, Blade views, language files, routes, package
lockfiles and Vite configuration changed for this feature. `npm ci` and
`npm run build` create the application assets under `public/build/`. Ship the
**entire** generated `public/build/` directory if the server does not run npm.
There are no new Composer dependencies or image conversion requirements.

Run the feature migrations after backing up the hosting database:

```bash
php artisan migrate --path=database/migrations/2026_10_23_150000_create_publications_table.php --path=database/migrations/2026_10_23_150100_connect_publication_navigation.php --path=database/migrations/2026_10_23_150200_add_locale_to_publications_table.php --force
php artisan route:clear
php artisan view:clear
```

The navigation migration reconnects the existing Publication anchors in saved
English/Sinhala school headers. If the link is absent, it adds one beside Gallery.
Desktop/mobile classes and the Gallery link are retained. Original and updated
header HTML are saved in revision history, including draft/published versions.
Subsequent header edits are preserved when rolling the migration back.

Local migrations were applied during implementation. Production deployment is
separate from the earlier 23 October server-update ZIP, which predates this feature.

# sch_of_slsc — Project architecture සහ හදිසි උපකාර reference

මෙම වාර්තාව **2026-10-22 දින local working tree එකේ තිබෙන code** පරීක්ෂා කර සකස් කළ snapshot එකකි. Commit කර නොමැති නව files සහ වෙනස්කම්ද ඇතුළත් වේ. Hosting server එකේ code, database rows, Apache configuration හෝ uptime පරීක්ෂා කර ඇති බව මෙයින් අදහස් නොවේ.

## 1. මේ report එක භාවිතා කරන ආකාරය

හදිසි ගැටලුවක් නම් මුලින් **Troubleshooting** වගුව බලන්න. URL එකක් සම්බන්ධ ගැටලුවක් නම් **Routes** tab එකෙන් URL/name එක සොයන්න. File එකක් වෙනස් කිරීමට පෙර **Files** tab එකෙන් එය සොයා එහි **Uses** සහ **Used by** සම්බන්ධතා බලන්න. Database column එකක් නම් **Database** tab එකෙන් model, table, relationships සහ migrations සොයන්න.

`index.html` internet නැතිවත් browser එකෙන් open කළ හැකියි. Data එහිම ඇතුළත් නිසා වෙනම web server එකක් අවශ්‍ය නැහැ. CSV files spreadsheet එකකින් filter කළ හැකියි. Markdown report එක IDE එකෙන් search/read කළ හැකියි. මෙම report එක local reference එකක් ලෙස තබන්න; public website directory එකේ publish කිරීම අවශ්‍ය නැහැ.

**Coverage:** project-owned files 579, routes 135, Eloquent models 30, detected dependency references 8,784. Metadata-only full inventory එකේ entries 19,816 සහ directories 3,326 ඇතුළත් වේ. PHP source files 366 AST parser එකෙන් parse කර තිබේ.

**Evidence:** PHP imports/type references, literal view/config/path references, Blade components, JS/CSS imports සහ model relationships source එකෙන් හඳුනාගෙන ඇත. Laravel route registration, Livewire component resolution සහ model table names local console bootstrap එකෙන් තහවුරු කර ඇත. Import එකක් තිබීම එම branch එක සෑම request එකකම execute වන බවට සාක්ෂියක් නොවේ. Database-stored HTML, dynamic variable view names, runtime hooks සහ third-party internals සඳහා graph එක සම්පූර්ණ runtime trace එකක් නොවේ. Detected references නැති file එකක් අනවශ්‍ය file එකක් ලෙස සලකන්න එපා.

`.env` content, passwords, APP_KEY, database rows, uploaded documents/images, log contents සහ Git objects වාර්තාවට copy කර නැහැ. Inventory එකේ ඒවායේ path/size metadata පමණක් තිබිය හැකියි. Safe configuration whitelist එක local environment snapshot එකක් පමණි.

## 2. Request එකක් මුල සිට අවසානය දක්වා යන මාර්ගය

```text
Browser / HTTP request
  -> Apache Alias / DocumentRoot + public/.htaccess
  -> public/index.php
  -> vendor/autoload.php
  -> bootstrap/app.php + bootstrap/providers.php
  -> web middleware + authentication/permissions
  -> routes/web.php (account settings: routes/settings.php)
  -> Controller හෝ Livewire component
  -> Service / Model / Database / Storage
  -> Blade view + layout + translations
  -> public/build + public/themes + public/storage URLs
  -> HTML/CSS/JavaScript response
```

`artisan` එකද එම application bootstrap එක භාවිතා කරයි; HTTP request එකක් වෙනුවට console command එකක් handle කරයි. `/up` health route එක `bootstrap/app.php` හරහා register වේ. එය boot health එක පෙන්වයි; සෑම business feature එකකම හෝ database query එකකම සාර්ථකත්වය සහතික කරන්නේ නැහැ.

## 3. Folder responsibilities සහ ඉවත් කිරීමට පෙර දැනගත යුතු දේ

| Folder / file | වගකීම සහ සම්බන්ධතා |
| --- | --- |
| `app/Http/Controllers/` | Public requests, previews සහ dashboard data. Routes විසින් controllers වෙත යොමු කරයි. Controllers models/services භාවිතා කර views වෙත data යවයි. |
| `app/Livewire/` | Admin screens සහ account settings සඳහා server-side state/actions. `wire:click`, `wire:submit`, `wire:model` හරහා Blade UI එකට සම්බන්ධ වේ. |
| `app/Models/` | Eloquent table mapping, casts, relationships, query scopes සහ model events. Database සහ services අතර data layer එකයි. |
| `app/Services/` | Workflows, sanitization, media/file operations, revisions, theme rendering සහ audit logging. UI එකෙන් භාවිතා කරන business rules මෙහි බොහෝ දුරට තිබේ. |
| `app/Enums/`, `Support/`, `Rules/`, `Concerns/` | Valid values, SEO/slug helpers, custom CSS validation සහ shared validation rules. |
| `app/Providers/` | Authentication setup, authorization, view-shared data සහ theme namespace/translation registration. |
| `bootstrap/` | Application boot සහ cached framework metadata. `app.php` සහ `providers.php` runtime සඳහා අවශ්‍යයි. |
| `config/` | Environment variables -> application configuration bindings. `.env` වෙනස් කිරීමක් config cache එක නිසා වහාම පෙනෙන්නේ නැති විය හැකියි. |
| `routes/` | URL -> handler, names, middleware සහ route constraints. Account routes වෙනම `settings.php` තුළ තිබේ. |
| `database/migrations/` | Schema evolution සහ සමහර content data updates. Migration file එකක් runtime model එකක් මෙන් request එකකට run වෙන්නේ නැහැ. |
| `database/seeders/`, `factories/` | Permission/bootstrap data සහ test data. Seeders සමහරක් production data වෙනස් කරන නිසා අරමුණ තේරුම්ගෙන භාවිතා කරන්න. |
| `resources/views/` | Core public/fallback layouts, admin Livewire views, components සහ authentication views. Folder එකම ඉවත් කරන්න බැහැ. |
| `resources/js/`, `css/`, `fonts/`, `tailwind/` | Vite build source, page editor logic සහ CMS Tailwind safelist. Build output `public/build/` තුළ යයි. |
| `themes/school-of-signals/` | Theme manifest, theme views, CSS/JS/fonts සහ theme translations. Public files publish කළත් source views සහ CSS runtime කටයුතු සඳහා තවම අවශ්‍යයි. |
| `lang/` | Core English/Sinhala UI labels, pagination සහ validation messages. Theme translations වෙනම theme namespace එකක තිබේ. |
| `public/` | Web-accessible entry point සහ assets. Web root/Apache Alias මේ folder එකට යොමු විය යුතුයි. |
| `storage/app/public/` | Uploaded public media. `public/storage` මෙයට ඇති link/junction එකයි; duplicate upload copy එකක් නොවේ. |
| `storage/app/private/` | Private files සඳහා configured local disk root. Public media සමඟ හුවමාරු නොකරන්න. |
| `storage/framework/`, `storage/logs/` | Compiled views, caches, test artifacts, sessions සහ logs. Data/uploads සමඟ එකට delete නොකරන්න. |
| `scripts/` | `compile-leadership-profile.mjs` runtime dependency එකකි. `build-*-deployment.py` files deployment packaging tools වේ. Folder එකම අනවශ්‍යයි කියලා ඉවත් කරන්න බැහැ. |
| `deploy/` | Apache instructions සහ dated incremental deployment packages. Installer/payload copies application source එකට වෙනම වේ. |
| `tests/` | Pest feature/unit checks. Local `Tests\\TestCase` එක RefreshDatabase භාවිතා කරයි. Production site එකේ සාමාන්‍ය request runtime එකට tests අවශ්‍ය නැහැ. |
| `vendor/` | Composer PHP dependencies. `composer.json` + `composer.lock` මත install වේ. Runtime සඳහා අවශ්‍යයි. |
| `node_modules/` | Node dependencies. මේ project එකේ leadership profile save/preview compiler එකටද අවශ්‍යයි. Server platform එකට ගැළපෙන ලෙස install කරන්න. |
| `.github/`, `.cursor/`, `.vscode/` | CI, AI/editor සහ IDE settings. `.git/` version history/metadata වේ. Local development copy එකේ Git history රැකගන්න. |

## 4. Application boot, auth සහ permissions

`bootstrap/app.php` එක `active` -> `EnsureUserIsActive`, `site.public` -> `EnsureSiteIsPublic` aliases register කරයි. `EnsureSiteIsPublic` web middleware stack එකටද append කරයි. Site settings table එකේ maintenance flag enabled නම් public users ට `public.maintenance` view එක සහ HTTP 503 යවයි. Admin/login/settings/password/verification/health paths සඳහා exemptions තිබේ; `admin.access` ඇති user කෙනෙකුට maintenance bypass එකක්ද තිබේ.

`EnsureUserIsActive` disabled account එක logout කර session invalidate කර login route එකට යවයි. `FortifyServiceProvider` login email lowercase/trim කර User lookup, `is_active` සහ password hash check කරයි. Login, password reset, email verification සහ two-factor views `resources/views/livewire/auth/` යටතේ සකස් කරයි. Passkey settings UI එක `resources/js/passkeys.js` සහ passkey Blade components භාවිතා කරයි.

Admin routes සඳහා සාමාන්‍ය chain එක `auth -> active -> verified -> can:admin.access -> feature permission` වේ. Individual Livewire actions/services තුළද Gate checks තිබේ. Sidebar link එක පෙන්වීම permission ලබාදීමක් නොවේ. `AppServiceProvider` හි Super Administrator role check එක Gate::before හරහා authorization එකට සම්බන්ධ වේ. Authentication events සහ business services `AuditLogger` හරහා audit records ලියයි.

`RolesAndPermissionsSeeder`, `SchoolModulePermissionsSeeder` සහ role/permission manager අතර සම්බන්ධය Database/Files tabs තුළ බලන්න. `RetireArchivePermissionsSeeder` legacy archive permissions මකා දමන data change එකකි. එය සාමාන්‍ය troubleshooting command එකක් ලෙස run නොකරන්න.

## 5. Feature අනුව end-to-end connections

### Homepage සහ hero slides

`home` / `home.localized` -> `PublicHomeController` -> SiteSetting, HeroSlide + translations + image variants, SchoolLeader + position/image, published News/Gallery/Document -> active theme `home.blade.php` හෝ `resources/views/public/home.blade.php`. Homepage leadership query එකේ current, active commandant position සඳහා විශේෂ filter එකක් සහ limit 1 තිබේ. වෙනත් leadership appointment එක homepage එකේ නොපෙනීම image/CSS ගැටලුවක් බවට වහාම තීරණය නොකරන්න.

`HeroSlideIndex` -> `resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php` -> HeroSlide/HeroSlideTranslation + MediaAsset. Public button URL resolution HeroSlide model එකේද තිබේ. Button click ගැටලුවක් නම් saved URL, installation prefix, public routes සහ theme hero overlay CSS එක එකට පරීක්ෂා කරන්න.

### CMS Pages

`admin/pages` routes -> PageIndex/PageCreate/PageEdit/PageRevisionHistory + PagePreviewController -> Page, PageRevision, PageWorkflowService, PageRevisionService, sanitizers, slug/SEO/meta helpers. Page form එක HTML සහ visual editor data එකම canonical content field එකක තබයි. Shared form components -> `resources/js/page-content-editor.js` -> `resources/css/app.css` -> Vite build.

Public route -> PublicPageController -> locale + slug + published status + published_at <= now filter -> PageHtmlSanitizer / PageBlockRenderer / PageSeo -> active theme `pages.show` හෝ core `pages.show`. Published record එකක් තිබුණත් locale හෝ schedule වැරදි නම් 404 ලැබිය හැකියි. Legacy page blocks backend එක තවම තිබේ; නව UI එක වෙනස් වූ බව නිසා model/service code එක ඉවත් නොකරන්න.

Custom CSS -> `ValidPageCss` / `PageCss` -> Page + PageRevision `custom_css` -> `components/page/custom-styles.blade.php`. PageCss selectors page-specific scope එකකට යොමු කරයි; invalid stored CSS read කිරීමේදී හිස් result එකක් ලබාදීමට සකස් කර ඇත. CSS වෙනස්කම් නොපෙනේ නම් migration, saved value, scoped markup සහ compiled view/browser cache බලන්න. Database HTML තුළ අලුතින් type කළ Tailwind class එකක් Vite වෙත ස්වයංක්‍රීය source එකක් නොවේ; `resources/tailwind/page-content-safelist.html` සහ build output එකද බලන්න.

### News

NewsIndex/Create/Edit/RevisionHistory + NewsPreviewController -> NewsArticleService, NewsWorkflowService, NewsRevisionService, NewsImageService, NewsCategoryService, NewsDeletionService -> News, NewsRevision, NewsImage, NewsCategory, MediaAsset. PublicNewsController -> published/localized query -> theme news views හෝ core fallback views. English/Sinhala translation grouping සහ featured/multiple images වෙන වෙනම relationships වේ.

Public gallery එකේ news album නොපෙනේ නම් News `show_in_gallery`, published scope, selected locale සහ public image media eligibility බලන්න. News record එක පවතිනවා පමණින් gallery feed එකට ඇතුළත් වෙන්නේ නැහැ.

### Media සහ image variants

MediaUpload/MediaEdit/MediaIndex -> MediaUploadService / MediaReplacementService / MediaMetadataService / MediaDeletionService -> MediaAsset + MediaVariant -> configured disk. MediaUploadSecurity actual MIME, allowed extension, file size සහ image dimensions පරීක්ෂා කරයි. `config/media.php` හි image default limit 10,240 KB, document limit 20,480 KB, maximum dimensions 8,000 x 8,000 සහ maximum pixels 24,000,000 ය. PHP/web server upload limits මෙයට අමතර සීමා විය හැකියි.

MediaImageVariantService variants සෑදීම සහ MediaUrlService thumbnail/medium/original URL තේරීම සිදු කරයි. Image එක බිඳී ගියොත් DB media path/disk/source/visibility, physical file, variant row සහ `public/storage` link එක සම්බන්ධව පරීක්ෂා කරන්න. File එක physical folder එකෙන් පමණක් delete කළොත් DB references බිඳී යා හැකියි.

### Galleries

GalleryIndex/Create/Edit -> GalleryService -> Gallery + GalleryImage + MediaAsset. Cover selection, ordering, image membership සහ publish/unpublish rules service එකේ තිබේ. PublicGalleryController -> PublicGalleryFeed -> published manual galleries සහ eligible News albums එකට pagination කරයි. GalleryLocale URL/localization helper එකයි. Public views `resources/views/public/galleries/` තුළ ඇත; theme layout ඒවා වටා භාවිතා විය හැකියි.

### Documents — දැනට ක්‍රියාත්මක වන simplified upload flow

`admin/documents`, create සහ edit routes තුනම runtime registration අනුව **DocumentUploads** වෙත resolve වේ. DocumentUploads -> DocumentUploadManager -> Document, DocumentVersion සහ MediaAsset. Save කිරීම create/update permission එකට අමතරව `documents.publish` permission ඉල්ලයි; valid version එකක් ඇති විට document එක published බවට සකස් කරයි. English සහ Sinhala current version pointers වෙන වෙනම තබයි.

PublicDocumentController -> locale අනුව eligible version -> `public.documents.upload-index` / `upload-show` හෝ file stream. File view/download endpoints සහ legacy redirects දෙකම routes catalog එකේ තිබේ. පැරණි DocumentIndex/DocumentCreate/DocumentEdit/DocumentService සහ පැරණි document views තවම source/tests තුළ තිබුණත් primary admin routes ඒවා වෙත යන්නේ නැහැ. ඒ files මකාදැමීමට මෙම observation එක තනි සාක්ෂියක් නොවේ.

### School leadership සහ history

SchoolLeaderIndex -> SchoolLeader + SchoolLeadershipPosition + image media. SchoolLeadershipPositionIndex -> dynamic appointment types. SchoolLeaderProfiles -> LeadershipProfileContent -> HTML/CSS sanitizers -> **Node.js process** -> `scripts/compile-leadership-profile.mjs` -> `tailwindcss` in node_modules -> compiled profile CSS stored in SchoolLeader.

PublicSchoolLeadershipController -> enabled/current/history/profile filters -> theme commandant/leader-profile/school-leadership views. Profile document theme partial එකේ sandboxed iframe srcdoc එකක render වේ. `profile_enabled`, active leader, active position සහ language content පරීක්ෂා කරන්න. Profile save/preview පමණක් fail වේ නම් PHP application run වුණත් Node binary/PATH, compiler script හෝ Tailwind dependency අහිමි විය හැකියි.

PastCommandant සහ PastChiefInstructor නමින් legacy models/tables තවම තිබුණත් **දැනට history controllers තුනම SchoolLeader records** query කරයි. Commandants සඳහා `commandant`, chief instructors සඳහා `chief_instructor`, sergeant majors සඳහා `warrant_officer` appointment keys භාවිතා වේ. Position relationship එකක් නොමැති පැරණි rows සඳහා role_key fallback තිබේ. එම public pages legacy history tables වලින් data ගන්නා බව අනුමාන නොකරන්න.

### Menus, theme layouts, site settings සහ contact

AppServiceProvider public/core/theme layouts වෙත siteSettings, primaryMenu සහ publicSocialLinks share කරයි. Menu -> rootItems/children -> MenuItemTranslation -> resolvedUrl chain එක භාවිතා වේ. MenuService සහ models තිබුණත් current routes තුළ admin menu management screen එකක් හඳුනාගෙන නැහැ. Public menus තවම ඒ data භාවිතා කරන නිසා tables/services අනවශ්‍ය නොවේ.

ThemeLayoutIndex -> ThemeLayoutService + ThemeLayoutSanitizer -> ThemeLayout + ThemeLayoutRevision. Theme layout `layout.blade.php` -> ThemeLayoutRenderer -> published header/footer HTML/CSS. Placeholders (`[[primary_menu]]`, `[[contact_details]]`, `[[site_url:/...]]` වැනි) service එකෙන් replace කරයි; DB content Blade/PHP ලෙස evaluate නොකරයි. Requested language layout එක නැත්නම් English fallback එකක් භාවිතා කරයි. Published layout නැත්නම් Blade fallback markup තිබේ.

SiteSettingsIndex -> SiteSettingService -> SiteSetting relationships to logo/favicon/hero media. Site name, colors, Sinhala footer/address සහ maintenance mode මෙයට සම්බන්ධ වේ. PublicContactController POST requests throttle කර ContactMessage records සාදයි; ContactMessageIndex/ContactMessageService admin read/resolve flow එකයි. Mail configuration එක තිබීමෙන් පමණක් මෙම contact flow එක email notification යවන බව අනුමාන නොකරන්න.

RedirectIndex -> RedirectService -> Redirect. Route fallback -> PublicRedirectController. සාමාන්‍ය page route එකක් 404 වීම සහ redirect record එක match වීම වෙන වෙනම branches වේ.

## 6. Theme rendering සහ asset publishing අතර වෙනස

`AWCMS_ACTIVE_THEME` -> `config/awcms.php` -> ThemeManager safe manifest lookup -> ThemeViewResolver validated file -> theme view. ThemeServiceProvider `theme-school-of-signals::...` namespace සහ translation namespace register කරයි. Theme manifest එකේ slug, views සහ assets වැරදි නම් active theme එක resolve නොවී fallback views භාවිතා විය හැකියි.

Theme view source: `themes/school-of-signals/views/`. Theme CSS/JS source: `themes/school-of-signals/assets/`. Browser වෙත URL යන්නේ `public/themes/school-of-signals/assets/` යටතේ ඇති published copies වෙතයි. Source CSS වෙනස් කර publish නොකළොත් browser එක පරණ CSS එකම ගන්න පුළුවන්. `awcms:theme:publish-assets school-of-signals` command එක source assets public folder එකට copy කරයි. මෙය application files වෙනස් කරන operational command එකකි; source එක නිවැරදි බව තහවුරු කර run කරන්න.

`resources/css/app.css`, `resources/js/app.js`, `resources/js/passkeys.js` Vite entry points වේ. `app.js` page-content-editor, Trix සහ SortableJS import කරයි. `@vite` manifest එකෙන් hashed assets load කරයි; `public/build` අවශ්‍යයි. `public/hot` development server URL වෙත යොමු කරන marker එකක් නිසා production package එකට ඇතුළත් නොකරන්න.

Public-only assets source counterpart එකක් නොමැතිව තිබිය හැකියි. Snapshot එකේ `public/themes/.../images/slsc_about.png` theme homepage එකෙන් reference වේ. Theme source images folder එකේ එය නැති බව නිසා public image එක delete නොකරන්න. Asset inventory එකේ source/published hash match තොරතුරු ඇත. CSS URL එකක static reference නොපෙනීම DB-stored HTML තුළද reference නැති බවට සාක්ෂියක් නොවේ.

## 7. Database reference එක කියවීම

Database tab එකේ model එකක් තෝරා table name, soft deletes, fillable fields, casts, relationships සහ create/alter migration links බලන්න. Schema section එක **migration declarations** එකතුවකි; live DB schema dump එකක් නොවේ. Down/rollback declarations සහ repeated alter operations තිබිය හැකියි. `foreignId()->constrained()` තුළ implicit naming හෝ dynamic table names static analysis එකෙන් සම්පූර්ණයෙන් resolve නොවිය හැකියි.

Core content: pages/page_revisions; news/news_revisions/news_images/news_categories; media_assets/media_variants; galleries/gallery_images; documents/document_versions/document_categories. Site structure: menus/menu_items/menu_item_translations, site_settings/social_links/hero_slides/hero_slide_translations, theme_layouts/theme_layout_revisions, redirects/contact_messages/audit_logs. Leadership: school_leaders/school_leadership_positions සහ legacy history tables. Auth/infrastructure: users, roles/permissions/pivots, passkeys, sessions, cache, jobs සහ related tables migrations තුළ තිබේ.

Page/News statuses සඳහා enum values තවම තිබුණත් archive UI/actions retired වී තිබේ. Tests/ArchiveRetirementTest සහ service `archive()` methods පරීක්ෂා කරන්න. Enum එකේ `archived` තිබෙනවා කියලා දැනට archive button/workflow එකක් සක්‍රීයයි කියලා සිතන්න එපා.

Backup එකක් සෑදීමේදී database සහ `storage/app` දෙකම අදාළ වේ. Database backup එකක් පමණක් image/document physical files නැවත ලබාදෙන්නේ නැහැ. Production database engine, table presence සහ migrations state මෙම වාර්තාවෙන් තහවුරු කර නැහැ.

## 8. Troubleshooting — symptom එකෙන් අදාළ files වෙත

| ගැටලුව | මුලින් බලන්න | සම්බන්ධ files / ඊළඟ පරීක්ෂාව |
| --- | --- | --- |
| සියලු pages HTTP 500 | `storage/logs/laravel.log` හි අලුත්ම error එක; PHP version/extensions; vendor/autoload presence | `public/index.php`, `bootstrap/app.php`, `composer.lock`, config. Log එක publicly share කිරීමට පෙර credentials ඉවත් කරන්න. |
| Homepage 503; admin/login වැඩ | SiteSetting maintenance_mode හෝ Laravel maintenance state | `EnsureSiteIsPublic`, `public.maintenance`, `storage/framework/down`; දෙක වෙන වෙනම mechanisms වේ. |
| Login redirect loop / HTTP 419 | Actual URL prefix, session path/domain, HTTPS cookies, writable session storage | `config/session.php`, `config/app.php`, Apache Alias, Fortify views; config cache state. |
| Login disabled user message | User is_active | `EnsureUserIsActive`, `FortifyServiceProvider`; permissions එකක් පමණක් දීමෙන් active flag වෙනස් වෙන්නේ නැහැ. |
| Admin HTTP 403 | user role, admin.access සහ feature/action permission | route middleware, service Gate checks, RolesAndPermissionsSeeder, `UserManagementRules`. |
| Published page HTTP 404 | locale + slug + status + published_at + soft deletion | `PublicPageController`, Page model/scopes, routes; exact `/pages/` URL pattern බලන්න. |
| Theme එක load නොවේ | active theme setting + valid manifest + theme view file | `config/awcms.php`, ThemeManager/Resolver/Provider. |
| Past commandants page theme off කළ පසු fail | Controller එකේ core fallback view reference එකට file එක තිබේද | Snapshot එකේ `public.history.past-commandants` fallback file එක නැහැ; theme view එක තිබේ. `PastCommandantController.php` line 68 fallback branch බලන්න. |
| Header/footer edits නොපෙනේ | right theme/region/locale සහ published vs draft data | ThemeLayoutIndex, ThemeLayoutService/Renderer; English fallback branch බලන්න. |
| CSS/JS පරණයි | source vs published theme copy; Vite build manifest; browser cache | ThemeAssetPublisher, theme assets, public/themes, vite.config.js, public/build. |
| Vite manifest missing | build artifact deploy වී තිබේද | `npm run build` build machine එකේ; upload/install correct output; source code පමණක් upload කිරීම ප්‍රමාණවත් නැහැ. |
| CMS Tailwind class style නොපෙනේ | safelist/source detection සහ build | `resources/tailwind/page-content-safelist.html`, app.css. Profile compiler flow එක මෙයට වෙනස්ය. |
| Page custom CSS නොපෙනේ | custom_css migration + saved CSS + scope validation | PageCss, ValidPageCss, custom-styles component, PageRevisionService. |
| Images HTTP 404 | media disk/path සහ physical file; public/storage link | MediaUrlService, filesystems/media config, media_variants; DB reference රැකගන්න. |
| Upload rejects file | actual MIME/extension + app/PHP/web server limits | MediaUploadSecurity, config/media.php, Livewire temporary uploads. |
| Leadership profile preview/save fail | Node/PATH + compiler file + tailwindcss package | LeadershipProfileContent, scripts/compile-leadership-profile.mjs, node_modules. |
| Leadership profile public 404 | active leader/position, profile_enabled, required language content | SchoolLeader, PublicSchoolLeadershipController, SchoolLeaderProfiles. |
| Document save 403 | documents.create/update සමඟ documents.publish | DocumentUploadManager + DocumentUploads; current routes old DocumentEdit වෙත යන්නේ නැහැ. |
| Sinhala document missing | current_version_si සහ එම version/media eligibility | PublicDocumentController, DocumentUploadManager, DocumentVersion. |
| Gallery/news album missing | published + show_in_gallery + public image requirements | PublicGalleryFeed, GalleryService, NewsImageService, GalleryLocale. |
| Hero button click fail | saved URL/prefix, destination route, overlay CSS | HeroSlide model, both homepage views, theme.css. |
| Sinhala labels නොපෙනේ | locale selection + root/theme translation namespace | `lang/si`, `lang/si.json`, theme/lang/si, ThemeServiceProvider. |
| Removed tests නිසා test command fail | hosting copy එකේ tests/dev tools ඉවත් කළාද | Local full development copy එකෙන් tests run කරන්න; website runtime එක වෙනම පරීක්ෂා කරන්න. |

## 9. ඉක්මන් පරීක්ෂණ සහ operational commands

පහත read-only metadata commands project root (`artisan` තිබෙන directory) එකෙන් run කළ හැකියි. ඒවා database/data recovery හෝ full end-to-end test එකක් නොවේ.

```bash
php artisan --version
php artisan about --only=environment
php artisan route:list --except-vendor
php artisan migrate:status
composer check-platform-reqs --no-dev
node --version
npm --version
```

`migrate:status` configured database එකට connect වී migration state කියවයි. `route:list` success වීමෙන් handlers register වීම පමණක් තහවුරු වේ; authenticated actions execute වන්නේ නැහැ. `/up`, homepage, Sinhala page සහ login browser/curl මගින්ද පරීක්ෂා කරන්න.

```bash
site_url='https://site.army.lk/sch_of_slsc'
for check_path in /up / /si /login; do
  curl -sS -L --max-time 30 -o /dev/null \
    -w "$check_path -> HTTP %{http_code}\n" "$site_url$check_path"
done
```

ඉහත URL එක repository deployment guide එකේ target URL එකයි; report එක සකස් කිරීමේදී එම host එකට request කර නැහැ. Actual hosting prefix/domain වෙනස් නම් URL එක වෙනස් කරන්න. Healthy public site එකක සාමාන්‍ය result එක 200 ය; maintenance/auth configuration අනුව response වෙනස් විය හැකියි. Admin login, save, upload සහ profile preview manually verify කිරීමද අවශ්‍යයි.

පහත commands cache files වෙනස් කරන නිසා ගැටලුවට අදාළ විට පමණක් භාවිතා කරන්න:

```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan config:cache
```

Theme source assets නිවැරදි කර ඇති විට `php artisan awcms:theme:publish-assets school-of-signals` public copies වෙනස් කරයි. Filesystem link අහිමි නම් `php artisan storage:link` උපකාරී විය හැකියි; දැනට තිබෙන physical uploads folder/link එක මුලින් බලන්න. `npm run build` frontend output නැවත ලියයි. `php artisan optimize:clear` default cache entriesද clear කළ හැකි නිසා targeted fix එකක් වෙනුවට සෑම විටම run නොකරන්න.

Existing production installation එකේ `php artisan key:generate`, `migrate:fresh`, `db:wipe`, `composer setup` හෝ අරමුණ නොදන්නා seeders troubleshooting සඳහා run නොකරන්න. `composer setup` project script එක key generation/migration/build කටයුතු ඇතුළත් කරන initial setup script එකකි. Permission problem එකකට project එකම 777 කිරීම වෙනුවට actual web process user/group සහ අවශ්‍ය writable directories (`storage`, `bootstrap/cache`) නිවැරදි කරන්න.

Local full development copy එකේ tests/dev dependencies තිබෙන විට:

```bash
php artisan test
composer lint:check
composer types:check
```

`composer test` script එක config:clear, lint, PHPStan සහ test suite එක එකට run කරයි. `phpunit.xml` in-memory SQLite testing configuration එක දක්වයි; actual environment overrides සහ cache files පරීක්ෂා නොකර production database එකකට tests යොමු නොකරන්න. මේ documentation task එකේ full suite run කර නැහැ.

## 10. Change එකක් කළොත් එකට බලන්න ඕන files

| වෙනස්කම | සම්බන්ධ files සහ verification |
| --- | --- |
| URL/route name | routes -> controller/Livewire -> sidebar, Blade route() calls, MenuItem URLs, SEO canonical URLs, tests. |
| Page field | migration -> Page casts/validation -> PageCreate/Edit -> revisions capture/restore -> preview/public view -> tests. |
| News/image field | migration/model -> article/image/workflow services -> admin forms -> public news/gallery feed -> tests. |
| Leadership profile | migration + SchoolLeader -> profile component/service -> Node compiler -> sandbox view -> profile tests. |
| Upload rule | config/media -> MediaUploadSecurity -> upload/replacement validation -> variants -> document allowlist -> security tests. |
| Theme CSS/JS | source asset -> published copy -> cache-busting/public page -> desktop/mobile visual check. |
| Header/footer/menu | admin DB layout -> sanitization/placeholders -> locale publish -> fallback layout -> menu translations/resolved URLs. |
| Role/permission | permission seeder/role UI -> route can: -> service Gate -> sidebar visibility -> permission boundary tests. |
| Database restore | matching uploads + application version + migration state + existing APP_KEY/config -> public/admin smoke checks. |

## 11. Legacy / convention details that can mislead

Livewire class components ප්‍රථමයෙන් resolve කරන බව installed `vendor/livewire/livewire/src/Factory/Factory.php` තුළ සහ local route metadata තුළ දැකිය හැකියි. `resources/views/components/admin/users/⚡user-*.blade.php` placeholders තිබුණත් current named admin routes resolve වන්නේ `app/Livewire/Admin/Users/` class components වෙතයි. Duplicate-looking views තිබෙනවා කියලා current route handler එක අනුමාන නොකරන්න.

Current DocumentUploads flow, legacy DocumentService/UI සහ existing tests වෙන වෙනම පවතී. Menu admin screen ඉවත් වුවත් public menu rendering තවම සක්‍රීය source references ඇත. `resources/views/livewire/admin/users/audit-logs/` යටතේ ඇති simple view එක සහ main audit-log view එක වෙනම paths වේ. Theme-specific history view එකක් නොතිබුණොත් controller fallback එක භාවිතා කරයි.

Generated/dependency code සඳහා source-level graph එක recursively parse කර නැහැ. Full inventory එකේ `vendor`, `node_modules`, `.git`, uploads සහ generated files සියල්ලේ metadata ලැයිස්තුගත කර තිබේ. Composer package versions, dev/runtime classification සහ package requirements වෙනම package list එකේ ඇත. Binary file එකකට source code call graph එකක් නොලැබීම සාමාන්‍යයි.

## 12. Report නැවත සෑදීම

Project code වෙනස් වූ පසු local project root එකෙන් පහත commands run කරන්න. මේ tools documentation outputs සෑදීම හැර application business data වෙනස් කිරීමට සකස් කර නැහැ. Local Laravel console boot සහ model metadata inspect කරයි; credentials export නොකරයි. Framework bootstrap එකෙන් සාමාන්‍ය cache metadata ඇති විය හැකියි.

```bash
php -d xdebug.mode=off docs/architecture/tools/extract_php.php
php -d xdebug.mode=off docs/architecture/tools/inspect_runtime.php
php -d xdebug.mode=off artisan route:list --json -vv > docs/architecture/routes-runtime.json
python docs/architecture/tools/build_data.py
python docs/architecture/tools/build_report.py
```

PHP AST extractor එකට local development vendor tree එකේ `nikic/php-parser` අවශ්‍යයි. Production `--no-dev` copy එකේ එය නොමැති විය හැකියි. Python report tools standard library පමණක් භාවිතා කරයි. Documentation output folder එක inventory scan එකෙන් ඉවත් කර ඇත්තේ report එක තමන්වම නැවත index නොකිරීමටයි. Files/line numbers වෙනස් වූ විට report එක නැවත generate කරන්න.


# Appendix A — Root folder inventory

| Folder | Files | Bytes | Responsibility |
| --- | ---: | ---: | --- |
| `.cursor/` | 28 | 87,329 | Editor/AI integration configuration සහ local development skill files. |
| `.git/` | 1,052 | 6,391,084 | Local Git history/metadata; source contents විශ්ලේෂණය කර නැහැ. |
| `.github/` | 2 | 1,011 | CI workflow සහ dependency automation. |
| `.vscode/` | 1 | 249 | VS Code workspace settings. |
| `app/` | 161 | 1,045,652 | Application controllers, Livewire components, models සහ business services. |
| `bootstrap/` | 5 | 27,732 | Application boot/provider registration සහ generated bootstrap caches. |
| `config/` | 16 | 72,957 | Configuration definitions සහ environment bindings. |
| `database/` | 70 | 261,941 | Migrations, seeders, factories සහ local database artifact. |
| `deploy/` | 14 | 330,733 | Apache config සහ incremental release packages. |
| `docs/` | 0 | 0 |  |
| `lang/` | 6 | 10,569 | Application translations. |
| `node_modules/` | 3,435 | 56,942,883 | Node dependencies; profile compiler runtime dependency ද ඇතුළත්. |
| `public/` | 35 | 3,247,093 | Web entry point, published/build assets සහ storage alias. |
| `resources/` | 121 | 1,249,830 | Core Blade templates, frontend sources, fonts සහ safelist. |
| `routes/` | 3 | 28,447 | Web/account/console route definitions. |
| `scripts/` | 6 | 26,031 | Runtime profile compiler සහ dated release packaging tools. |
| `storage/` | 1,222 | 107,790,070 | Application uploads/data සහ framework caches/logs. |
| `tests/` | 111 | 630,048 | Local automated test coverage. |
| `themes/` | 27 | 704,131 | Theme manifests, Blade templates, translations සහ asset sources. |
| `vendor/` | 13,484 | 131,341,440 | Composer-installed PHP dependencies. |

# Appendix B — Complete registered route catalog

Middleware entries are the locally expanded route:list output. Handler resolution does not execute authenticated actions.

| Method | URI | Name | Handler source | Middleware |
| --- | --- | --- | --- | --- |
| GET\|HEAD | /.well-known/passkey-endpoints | well-known.passkeys | routes/settings.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | / | home | app/Http/Controllers/PublicHomeController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /_boost/browser-logs | boost.browser-logs | vendor/laravel/boost/src/BoostServiceProvider.php |  |
| GET\|HEAD | /admin | admin.dashboard | app/Http/Controllers/Admin/DashboardController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:dashboard.view |
| GET\|HEAD | /admin/audit-logs | admin.audit-logs.index | app/Livewire/Admin/AuditLogs/AuditLogIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:audit.view |
| GET\|HEAD | /admin/contact-messages | admin.contact-messages.index | app/Livewire/Admin/ContactMessages/ContactMessageIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:contacts.manage |
| GET\|HEAD | /admin/documents | admin.documents.index | app/Livewire/Admin/Documents/DocumentUploads.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:documents.view |
| GET\|HEAD | /admin/documents/categories | admin.documents.categories.index | app/Livewire/Admin/Documents/DocumentCategoryIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:documents.categories.manage |
| GET\|HEAD | /admin/documents/create | admin.documents.create | app/Livewire/Admin/Documents/DocumentUploads.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:documents.view; Illuminate\Auth\Middleware\Authorize:documents.create |
| GET\|HEAD | /admin/documents/{document}/edit | admin.documents.edit | app/Livewire/Admin/Documents/DocumentUploads.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:documents.view; Illuminate\Auth\Middleware\Authorize:documents.update |
| GET\|HEAD | /admin/galleries | admin.galleries.index | app/Livewire/Admin/Galleries/GalleryIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:galleries.view |
| GET\|HEAD | /admin/galleries/create | admin.galleries.create | app/Livewire/Admin/Galleries/GalleryCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:galleries.create |
| GET\|HEAD | /admin/galleries/{gallery}/edit | admin.galleries.edit | app/Livewire/Admin/Galleries/GalleryEdit.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:galleries.view; Illuminate\Auth\Middleware\Authorize:galleries.update |
| GET\|HEAD | /admin/hero-slides | admin.hero-slides.index | app/Livewire/Admin/HeroSlides/HeroSlideIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:hero-slides.view |
| GET\|HEAD | /admin/media | admin.media.index | app/Livewire/Admin/Media/MediaIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:media.view |
| GET\|HEAD | /admin/media/upload | admin.media.upload | app/Livewire/Admin/Media/MediaUpload.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:media.upload |
| GET\|HEAD | /admin/media/{media}/edit | admin.media.edit | app/Livewire/Admin/Media/MediaEdit.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:media.view |
| GET\|HEAD | /admin/news | admin.news.index | app/Livewire/Admin/News/NewsIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.view |
| GET\|HEAD | /admin/news/categories | admin.news.categories.index | app/Livewire/Admin/News/NewsCategoryIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.categories.manage |
| GET\|HEAD | /admin/news/create | admin.news.create | app/Livewire/Admin/News/NewsCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.create |
| GET\|HEAD | /admin/news/{newsId}/translations/{locale}/create | admin.news.translations.create | app/Livewire/Admin/News/NewsCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.create |
| GET\|HEAD | /admin/news/{news}/edit | admin.news.edit | app/Livewire/Admin/News/NewsEdit.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.view; Illuminate\Auth\Middleware\Authorize:news.update |
| GET\|HEAD | /admin/news/{news}/preview | admin.news.preview | app/Http/Controllers/Admin/NewsPreviewController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.view |
| GET\|HEAD | /admin/news/{news}/revisions | admin.news.revisions | app/Livewire/Admin/News/NewsRevisionHistory.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:news.view |
| GET\|HEAD | /admin/pages | admin.pages.index | app/Livewire/Admin/Pages/PageIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.view |
| GET\|HEAD | /admin/pages/create | admin.pages.create | app/Livewire/Admin/Pages/PageCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.create |
| GET\|HEAD | /admin/pages/{pageId}/translations/{locale}/create | admin.pages.translations.create | app/Livewire/Admin/Pages/PageCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.create |
| GET\|HEAD | /admin/pages/{page}/edit | admin.pages.edit | app/Livewire/Admin/Pages/PageEdit.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.update |
| GET\|HEAD | /admin/pages/{page}/preview | admin.pages.preview | app/Http/Controllers/Admin/PagePreviewController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.view |
| GET\|HEAD | /admin/pages/{page}/revisions | admin.pages.revisions | app/Livewire/Admin/Pages/PageRevisionHistory.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:pages.revisions.view |
| GET\|HEAD | /admin/redirects | admin.redirects.index | app/Livewire/Admin/Redirects/RedirectIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:redirects.manage |
| GET\|HEAD | /admin/roles | admin.roles.index | app/Livewire/Admin/Roles/RolePermissionIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:roles.manage |
| GET\|HEAD | /admin/school-leaders | admin.school-leaders.index | app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:school-leaders.view |
| GET\|HEAD | /admin/school-leadership-positions | admin.school-leadership-positions.index | app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:school-leaders.view |
| GET\|HEAD | /admin/school-leadership-profiles | admin.school-leadership-profiles.index | app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:school-leaders.view |
| GET\|HEAD | /admin/site-settings | admin.site-settings.index | app/Livewire/Admin/Settings/SiteSettingsIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:settings.manage |
| GET\|HEAD | /admin/theme-layouts | admin.theme-layouts.index | app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:theme-layouts.manage |
| GET\|HEAD | /admin/users | admin.users.index | app/Livewire/Admin/Users/UserIndex.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:users.view |
| GET\|HEAD | /admin/users/create | admin.users.create | app/Livewire/Admin/Users/UserCreate.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:users.create; Illuminate\Auth\Middleware\Authorize:users.assign-role |
| GET\|HEAD | /admin/users/{user}/edit | admin.users.edit | app/Livewire/Admin/Users/UserEdit.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\Authorize:admin.access; Illuminate\Auth\Middleware\Authorize:users.update |
| GET\|HEAD | /contact | contact.create | app/Http/Controllers/PublicContactController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /contact | contact.store | app/Http/Controllers/PublicContactController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:5,1; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /dashboard | dashboard | routes/web.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified |
| GET\|HEAD | /document-files/{locale}/{slug}/download | document-files.download | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /document-files/{locale}/{slug}/view | document-files.view | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /documents | documents.index | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /documents/{slug} | documents.show | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /documents/{slug}/download | documents.download | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /documents/{slug}/view | documents.view | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /email/verification-notification | verification.send | vendor/laravel/fortify/src/Http/Controllers/EmailVerificationNotificationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:6,1; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /email/verify | verification.notice | vendor/laravel/fortify/src/Http/Controllers/EmailVerificationPromptController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /email/verify/{id}/{hash} | verification.verify | vendor/laravel/fortify/src/Http/Controllers/VerifyEmailController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:6,1; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Routing\Middleware\ValidateSignature |
| GET\|HEAD | /en/history/past-chief-instructors | history.past-chief-instructors | app/Http/Controllers/PastChiefInstructorController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /en/history/past-commandants | history.past-commandants | app/Http/Controllers/PastCommandantController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /en/history/past-sgt-major | history.past-sgt-major | app/Http/Controllers/PastSergeantMajorController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /flux/editor.css | (unnamed) | vendor/livewire/flux/src/AssetManager.php |  |
| GET\|HEAD | /flux/editor.js | (unnamed) | vendor/livewire/flux/src/AssetManager.php |  |
| GET\|HEAD | /flux/editor.min.js | (unnamed) | vendor/livewire/flux/src/AssetManager.php |  |
| GET\|HEAD | /flux/flux.js | (unnamed) | vendor/livewire/flux/src/AssetManager.php |  |
| GET\|HEAD | /flux/flux.min.js | (unnamed) | vendor/livewire/flux/src/AssetManager.php |  |
| GET\|HEAD | /forgot-password | password.request | vendor/laravel/fortify/src/Http/Controllers/PasswordResetLinkController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| POST | /forgot-password | password.email | vendor/laravel/fortify/src/Http/Controllers/PasswordResetLinkController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| GET\|HEAD | /galleries | galleries.index | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /galleries/news/{newsId} | galleries.news | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /galleries/{slug} | galleries.show | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /livewire-b92175e7/css/{component}.css | (unnamed) | vendor/livewire/livewire/src/Features/SupportCssModules/SupportCssModules.php |  |
| GET\|HEAD | /livewire-b92175e7/css/{component}.global.css | (unnamed) | vendor/livewire/livewire/src/Features/SupportCssModules/SupportCssModules.php |  |
| GET\|HEAD | /livewire-b92175e7/js/{component}.js | (unnamed) | vendor/livewire/livewire/src/Features/SupportJsModules/SupportJsModules.php |  |
| GET\|HEAD | /livewire-b92175e7/livewire.csp.min.js.map | (unnamed) | vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php |  |
| GET\|HEAD | /livewire-b92175e7/livewire.js | (unnamed) | vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php |  |
| GET\|HEAD | /livewire-b92175e7/livewire.min.js.map | (unnamed) | vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php |  |
| GET\|HEAD | /livewire-b92175e7/preview-file/{filename} | livewire.preview-file | vendor/livewire/livewire/src/Features/SupportFileUploads/FilePreviewController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /livewire-b92175e7/update | default-livewire.update | vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders |
| POST | /livewire-b92175e7/upload-file | livewire.upload-file | vendor/livewire/livewire/src/Features/SupportFileUploads/FileUploadController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:60,1; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /login | login | vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| POST | /login | login.store | vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:login; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| POST | /logout | logout | vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /news | news.index | app/Http/Controllers/PublicNewsController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /news/{slug} | news.show | app/Http/Controllers/PublicNewsController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /pages/{slug} | pages.show | app/Http/Controllers/PublicPageController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /passkeys/confirm | passkey.confirm | vendor/laravel/passkeys/src/Http/Controllers/PasskeyConfirmationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /passkeys/confirm/options | passkey.confirm-options | vendor/laravel/passkeys/src/Http/Controllers/PasskeyConfirmationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /passkeys/login | passkey.login | vendor/laravel/passkeys/src/Http/Controllers/PasskeyLoginController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| GET\|HEAD | /passkeys/login/options | passkey.login-options | vendor/laravel/passkeys/src/Http/Controllers/PasskeyLoginController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| POST | /reset-password | password.update | vendor/laravel/fortify/src/Http/Controllers/NewPasswordController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| GET\|HEAD | /reset-password/{token} | password.reset | vendor/laravel/fortify/src/Http/Controllers/NewPasswordController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| GET\|HEAD | /school-leadership | school-leadership.index | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /school-leadership/commandant | school-leadership.commandant | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /school-leadership/history | school-leadership.history | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /school-leadership/profiles/{leader} | school-leadership.show | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /school-leadership/{appointment} | school-leadership.profile | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD\|POST\|PUT\|PATCH\|DELETE\|OPTIONS | /settings | (unnamed) | vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive |
| GET\|HEAD | /settings/appearance | appearance.edit | app/Livewire/Settings/Appearance.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified |
| GET\|HEAD | /settings/profile | profile.edit | app/Livewire/Settings/Profile.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive |
| GET\|HEAD | /settings/security | security.edit | app/Livewire/Settings/Security.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; App\Http\Middleware\EnsureUserIsActive; Illuminate\Auth\Middleware\EnsureEmailIsVerified; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /si/history/past-chief-instructors | history.past-chief-instructors.localized | app/Http/Controllers/PastChiefInstructorController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /si/history/past-commandants | history.past-commandants.localized | app/Http/Controllers/PastCommandantController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /si/history/past-sgt-major | history.past-sgt-major.localized | app/Http/Controllers/PastSergeantMajorController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /storage/{path} | storage.local | vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php |  |
| PUT | /storage/{path} | storage.local.upload | vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php |  |
| GET\|HEAD | /two-factor-challenge | two-factor.login | vendor/laravel/fortify/src/Http/Controllers/TwoFactorAuthenticatedSessionController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| POST | /two-factor-challenge | two-factor.login.store | vendor/laravel/fortify/src/Http/Controllers/TwoFactorAuthenticatedSessionController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:two-factor; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RedirectIfAuthenticated:web |
| GET\|HEAD | /up | (unnamed) | vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php |  |
| GET\|HEAD | /user/confirm-password | password.confirm | vendor/laravel/fortify/src/Http/Controllers/ConfirmablePasswordController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /user/confirm-password | password.confirm.store | vendor/laravel/fortify/src/Http/Controllers/ConfirmablePasswordController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /user/confirmed-password-status | password.confirmation | vendor/laravel/fortify/src/Http/Controllers/ConfirmedPasswordStatusController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /user/confirmed-two-factor-authentication | two-factor.confirm | vendor/laravel/fortify/src/Http/Controllers/ConfirmedTwoFactorAuthenticationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| POST | /user/passkeys | passkey.store | vendor/laravel/passkeys/src/Http/Controllers/PasskeyRegistrationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /user/passkeys/options | passkey.registration-options | vendor/laravel/passkeys/src/Http/Controllers/PasskeyRegistrationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\ThrottleRequests:passkeys; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| DELETE | /user/passkeys/{passkey} | passkey.destroy | vendor/laravel/passkeys/src/Http/Controllers/PasskeyRegistrationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| PUT | /user/password | user-password.update | vendor/laravel/fortify/src/Http/Controllers/PasswordController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| PUT | /user/profile-information | user-profile-information.update | vendor/laravel/fortify/src/Http/Controllers/ProfileInformationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /user/two-factor-authentication | two-factor.enable | vendor/laravel/fortify/src/Http/Controllers/TwoFactorAuthenticationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| DELETE | /user/two-factor-authentication | two-factor.disable | vendor/laravel/fortify/src/Http/Controllers/TwoFactorAuthenticationController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /user/two-factor-qr-code | two-factor.qr-code | vendor/laravel/fortify/src/Http/Controllers/TwoFactorQrCodeController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /user/two-factor-recovery-codes | two-factor.recovery-codes | vendor/laravel/fortify/src/Http/Controllers/RecoveryCodeController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| POST | /user/two-factor-recovery-codes | two-factor.regenerate-recovery-codes | vendor/laravel/fortify/src/Http/Controllers/RecoveryCodeController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /user/two-factor-secret-key | two-factor.secret-key | vendor/laravel/fortify/src/Http/Controllers/TwoFactorSecretKeyController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Auth\Middleware\Authenticate:web; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost; Illuminate\Auth\Middleware\RequirePassword |
| GET\|HEAD | /{fallbackPlaceholder} | (unnamed) | app/Http/Controllers/PublicRedirectController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale} | home.localized | app/Http/Controllers/PublicHomeController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/contact | contact.create.localized | app/Http/Controllers/PublicContactController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| POST | /{locale}/contact | contact.store.localized | app/Http/Controllers/PublicContactController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\ThrottleRequests:5,1; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/documents | documents.index.localized | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/documents/{slug} | documents.show.localized | app/Http/Controllers/PublicDocumentController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/galleries | galleries.index.localized | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/galleries/news/{newsId} | galleries.news.localized | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/galleries/{slug} | galleries.show.localized | app/Http/Controllers/PublicGalleryController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/news | news.index.localized | app/Http/Controllers/PublicNewsController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/news/{slug} | news.show.localized | app/Http/Controllers/PublicNewsController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/pages/{slug} | pages.show.localized | app/Http/Controllers/PublicPageController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/school-leadership | school-leadership.localized | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/school-leadership/commandant | school-leadership.commandant.localized | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/school-leadership/history | school-leadership.history.localized | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/school-leadership/profiles/{leader} | school-leadership.show.localized | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |
| GET\|HEAD | /{locale}/school-leadership/{appointment} | school-leadership.profile.localized | app/Http/Controllers/PublicSchoolLeadershipController.php | Illuminate\Cookie\Middleware\EncryptCookies; Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; Illuminate\Session\Middleware\StartSession; Illuminate\View\Middleware\ShareErrorsFromSession; Illuminate\Foundation\Http\Middleware\PreventRequestForgery; Illuminate\Routing\Middleware\SubstituteBindings; App\Http\Middleware\EnsureSiteIsPublic; Laravel\Boost\Middleware\InjectBoost |

# Appendix C — Models, tables and relationships

## audit_logs
[app/Models/AuditLog.php](#file-f0b1464bc270) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| actor | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "actor_id"] |

Schema migration files: [database/migrations/2026_07_30_174658_create_audit_logs_table.php](#file-931a322d6e8e)
Casts: `{"id": "int", "old_values": "array", "new_values": "array", "created_at": "immutable_datetime"}`

## contact_messages
[app/Models/ContactMessage.php](#file-60b85836b768) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| handler | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "handled_by"] |

Schema migration files: [database/migrations/2026_08_27_170005_create_contact_messages_table.php](#file-95554a2eb929)
Casts: `{"id": "int", "read_at": "immutable_datetime", "resolved_at": "immutable_datetime"}`

## documents
[app/Models/Document.php](#file-a5395ee5355b) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| category | belongsTo | [{"class": "App\\Models\\DocumentCategory", "constant": "class"}, "document_category_id"] |
| versions | hasMany | [{"class": "App\\Models\\DocumentVersion", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| publisher | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "published_by"] |
| archiver | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "archived_by"] |

Schema migration files: [database/migrations/2026_08_25_163254_create_documents_table.php](#file-8432a6d239a4), [database/migrations/2026_09_29_082712_add_sinhala_fields_to_documents_table.php](#file-88332f27f038), [database/migrations/2026_09_29_114230_add_current_version_si_to_documents_table.php](#file-e9d81c1238ac)
Casts: `{"id": "int", "document_date": "date", "current_version": "integer", "current_version_si": "integer", "status": "App\\Enums\\DocumentStatus", "published_at": "datetime", "archived_at": "datetime", "deleted_at": "datetime"}`

## document_categories
[app/Models/DocumentCategory.php](#file-43cbc2f55250) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| documents | hasMany | [{"class": "App\\Models\\Document", "constant": "class"}, "document_category_id"] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |

Schema migration files: [database/migrations/2026_08_25_163253_create_document_categories_table.php](#file-4a5630301f80)
Casts: `{"id": "int", "is_active": "boolean", "sort_order": "integer", "deleted_at": "datetime"}`

## document_versions
[app/Models/DocumentVersion.php](#file-dc28558361e9) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| document | belongsTo | [{"class": "App\\Models\\Document", "constant": "class"}] |
| media | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "media_asset_id"] |
| uploader | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "uploaded_by"] |

Schema migration files: [database/migrations/2026_08_25_163255_create_document_versions_table.php](#file-f6faaf6d36ba)
Casts: `{"id": "int", "version": "integer"}`

## galleries
[app/Models/Gallery.php](#file-87d2aaf50cbb) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| coverMedia | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "cover_media_id"] |
| images | hasMany | [{"class": "App\\Models\\GalleryImage", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| publisher | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "published_by"] |
| archiver | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "archived_by"] |

Schema migration files: [database/migrations/2026_08_24_203114_create_galleries_table.php](#file-990b340a082e), [database/migrations/2026_09_28_090000_add_sinhala_title_to_galleries_table.php](#file-7f9820166d5c)
Casts: `{"id": "int", "event_date": "date", "status": "App\\Enums\\GalleryStatus", "published_at": "datetime", "archived_at": "datetime", "deleted_at": "datetime"}`

## gallery_images
[app/Models/GalleryImage.php](#file-cc816d7f9cf9) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| gallery | belongsTo | [{"class": "App\\Models\\Gallery", "constant": "class"}] |
| media | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "media_asset_id"] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |

Schema migration files: [database/migrations/2026_08_24_203115_create_gallery_images_table.php](#file-6c96d8f47c44)
Casts: `{"id": "int", "sort_order": "integer"}`

## hero_slides
[app/Models/HeroSlide.php](#file-c36ed5aa1b49) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| image | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "image_media_id"] |
| translations | hasMany | [{"class": "App\\Models\\HeroSlideTranslation", "constant": "class"}] |

Schema migration files: [database/migrations/2026_08_27_170004_create_hero_slides_table.php](#file-4752f166062e)
Casts: `{"id": "int", "image_media_id": "integer", "is_active": "boolean", "sort_order": "integer", "created_by": "integer", "updated_by": "integer"}`

## hero_slide_translations
[app/Models/HeroSlideTranslation.php](#file-f30398e71d18) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| heroSlide | belongsTo | [{"class": "App\\Models\\HeroSlide", "constant": "class"}] |

Schema migration files: [database/migrations/2026_09_07_120621_create_hero_slide_translations_table.php](#file-1d4963af7a71)
Casts: `{"id": "int", "hero_slide_id": "integer", "locale": "App\\Enums\\PageLocale"}`

## media_assets
[app/Models/MediaAsset.php](#file-4712fd861864) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| uploader | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "uploaded_by"] |
| variants | hasMany | [{"class": "App\\Models\\MediaVariant", "constant": "class"}] |

Schema migration files: [database/migrations/2026_08_18_140000_create_media_assets_table.php](#file-58dd900a54df)
Casts: `{"id": "int", "type": "App\\Enums\\MediaType", "source": "App\\Enums\\MediaSource", "visibility": "App\\Enums\\MediaVisibility", "metadata": "array", "size_bytes": "integer", "width": "integer", "height": "integer", "deleted_at": "datetime"}`

## media_variants
[app/Models/MediaVariant.php](#file-da95f7d80c02) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| mediaAsset | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}] |

Schema migration files: [database/migrations/2026_08_19_091418_create_media_variants_table.php](#file-e46591bdb95e)
Casts: `{"id": "int", "name": "App\\Enums\\MediaVariantPreset", "width": "integer", "height": "integer", "size_bytes": "integer", "generated_at": "immutable_datetime"}`

## menus
[app/Models/Menu.php](#file-72f78bded920) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| items | hasMany | [{"class": "App\\Models\\MenuItem", "constant": "class"}] |
| rootItems | hasMany | [{"class": "App\\Models\\MenuItem", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |

Schema migration files: [database/migrations/2026_08_27_170000_create_menus_table.php](#file-2592bb5fac61)
Casts: `{"id": "int", "is_active": "boolean", "deleted_at": "datetime"}`

## menu_items
[app/Models/MenuItem.php](#file-55fd9fcfc0cd) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| menu | belongsTo | [{"class": "App\\Models\\Menu", "constant": "class"}] |
| parent | belongsTo | [{"class": "self", "constant": "class"}, "parent_id"] |
| children | hasMany | [{"class": "self", "constant": "class"}, "parent_id"] |
| translations | hasMany | [{"class": "App\\Models\\MenuItemTranslation", "constant": "class"}] |

Schema migration files: [database/migrations/2026_08_27_170001_create_menu_items_table.php](#file-601ba28fb85d)
Casts: `{"id": "int", "reference_id": "integer", "open_in_new_tab": "boolean", "is_active": "boolean", "sort_order": "integer"}`

## menu_item_translations
[app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| menuItem | belongsTo | [{"class": "App\\Models\\MenuItem", "constant": "class"}] |

Schema migration files: [database/migrations/2026_09_02_160000_create_menu_item_translations_table.php](#file-36bc8a17d4b4)
Casts: `{"id": "int", "locale": "App\\Enums\\MenuLocale"}`

## news
[app/Models/News.php](#file-9584cdf2eac3) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| revisions | hasMany | [{"class": "App\\Models\\NewsRevision", "constant": "class"}] |
| images | hasMany | [{"class": "App\\Models\\NewsImage", "constant": "class"}] |
| translationVersions | hasMany | [{"class": "self", "constant": "class"}, "translation_group", "translation_group"] |
| category | belongsTo | [{"class": "App\\Models\\NewsCategory", "constant": "class"}, "category_id"] |
| featuredImage | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "featured_image_id"] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| submitter | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "submitted_by"] |
| approver | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "approved_by"] |
| publisher | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "published_by"] |
| archiver | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "archived_by"] |
| changesRequestedBy | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "changes_requested_by"] |

Schema migration files: [database/migrations/2026_08_19_181914_create_news_table.php](#file-4a2176338404), [database/migrations/2026_08_24_192347_add_review_workflow_fields_to_news_table.php](#file-1ce6c5531f1c), [database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php](#file-87db2ff63fe7), [database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php](#file-c741ddca09d7)
Casts: `{"id": "int", "locale": "App\\Enums\\NewsLocale", "editor_mode": "App\\Enums\\NewsEditorMode", "status": "App\\Enums\\NewsStatus", "is_featured": "boolean", "show_in_gallery": "boolean", "published_at": "datetime", "submitted_at": "datetime", "approved_at": "datetime", "archived_at": "datetime", "changes_requested_at": "datetime", "deleted_at": "datetime"}`

## news_categories
[app/Models/NewsCategory.php](#file-f5d3b9e23d57) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| news | hasMany | [{"class": "App\\Models\\News", "constant": "class"}, "category_id"] |

Schema migration files: [database/migrations/2026_08_19_181912_create_news_categories_table.php](#file-25651cfdaf21)
Casts: `{"id": "int", "is_active": "boolean", "sort_order": "integer", "deleted_at": "datetime"}`

## news_images
[app/Models/NewsImage.php](#file-865f4356df79) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| news | belongsTo | [{"class": "App\\Models\\News", "constant": "class"}] |
| media | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "media_asset_id"] |

Schema migration files: [database/migrations/2026_09_02_143000_create_news_images_table.php](#file-d851a19b76a6)
Casts: `{"id": "int"}`

## news_revisions
[app/Models/NewsRevision.php](#file-e5608ebee6bd) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| news | belongsTo | [{"class": "App\\Models\\News", "constant": "class"}] |
| category | belongsTo | [{"class": "App\\Models\\NewsCategory", "constant": "class"}] |
| featuredImage | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "featured_image_id"] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |

Schema migration files: [database/migrations/2026_08_19_204327_create_news_revisions_table.php](#file-e072df78cd8b), [database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php](#file-87db2ff63fe7), [database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php](#file-c741ddca09d7)
Casts: `{"id": "int", "revision_number": "integer", "locale": "App\\Enums\\NewsLocale", "editor_mode": "App\\Enums\\NewsEditorMode", "is_featured": "boolean", "status": "App\\Enums\\NewsStatus", "published_at": "datetime"}`

## pages
[app/Models/Page.php](#file-b56bbabec185) · key: `id` · soft deletes: True

| Relationship | Type | Arguments |
| --- | --- | --- |
| revisions | hasMany | [{"class": "App\\Models\\PageRevision", "constant": "class"}] |
| translationVersions | hasMany | [{"class": "self", "constant": "class"}, "translation_group", "translation_group"] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| approver | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "approved_by"] |

Schema migration files: [database/migrations/2026_07_30_203704_create_pages_table.php](#file-28325b9ecbcd), [database/migrations/2026_08_17_191344_add_seo_fields_to_pages_table.php](#file-fa467b9249c9), [database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php](#file-248f792f0323), [database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php](#file-fcc1329ced49), [database/migrations/2026_09_01_130000_add_localisation_to_pages.php](#file-5ff441c1318d), [database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php](#file-94773c7c17bf)
Casts: `{"id": "int", "blocks": "array", "locale": "App\\Enums\\PageLocale", "show_title": "boolean", "editor_mode": "App\\Enums\\PageEditorMode", "status": "App\\Enums\\PageStatus", "robots_index": "boolean", "submitted_at": "immutable_datetime", "approved_at": "immutable_datetime", "published_at": "immutable_datetime", "archived_at": "immutable_datetime", "deleted_at": "datetime"}`

## page_revisions
[app/Models/PageRevision.php](#file-6bb9cc9d8d9b) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| page | belongsTo | [{"class": "App\\Models\\Page", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |

Schema migration files: [database/migrations/2026_07_31_145549_create_page_revisions_table.php](#file-e010058feef2), [database/migrations/2026_08_17_192013_add_seo_fields_to_page_revisions_table.php](#file-53982f05d42f), [database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php](#file-248f792f0323), [database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php](#file-fcc1329ced49), [database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php](#file-94773c7c17bf)
Casts: `{"id": "int", "blocks": "array", "show_title": "boolean", "editor_mode": "App\\Enums\\PageEditorMode", "status": "App\\Enums\\PageStatus", "robots_index": "boolean", "created_at": "immutable_datetime"}`

## past_chief_instructors
[app/Models/PastChiefInstructor.php](#file-fca0ee8b2525) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| image | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "image_media_id"] |

Schema migration files: [database/migrations/2026_09_11_130000_create_past_chief_instructors_table.php](#file-60cf77e4e26d)
Casts: `{"id": "int", "from_date": "date", "to_date": "date", "image_media_id": "integer", "created_by": "integer", "updated_by": "integer"}`

## past_commandants
[app/Models/PastCommandant.php](#file-2227ba16f07a) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| image | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "image_media_id"] |

Schema migration files: [database/migrations/2026_09_09_130000_create_past_commandants_table.php](#file-4c4ff27bd934)
Casts: `{"id": "int", "from_date": "date", "to_date": "date", "image_media_id": "integer", "created_by": "integer", "updated_by": "integer"}`

## redirects
[app/Models/Redirect.php](#file-1efb27655fd7) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |

Schema migration files: [database/migrations/2026_08_27_170006_create_redirects_table.php](#file-781628afda80)
Casts: `{"id": "int", "http_status": "integer", "is_active": "boolean", "hit_count": "integer", "last_hit_at": "immutable_datetime"}`

## school_leaders
[app/Models/SchoolLeader.php](#file-7f28db275867) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| position | belongsTo | [{"class": "App\\Models\\SchoolLeadershipPosition", "constant": "class"}, "position_id"] |
| image | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "image_media_id"] |

Schema migration files: [database/migrations/2026_09_09_112158_create_school_leaders_table.php](#file-1ab05208ef29), [database/migrations/2026_10_01_150056_add_history_fields_to_school_leaders_table.php](#file-9f99b37e38d5), [database/migrations/2026_10_01_191603_drop_unique_role_key_from_school_leaders_table.php](#file-e6e3a7c6756f), [database/migrations/2026_10_02_075948_add_position_id_to_school_leaders_table.php](#file-c0eac7092403), [database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php](#file-3934d68405bd)
Casts: `{"id": "int", "position_id": "integer", "image_media_id": "integer", "start_date": "date", "end_date": "date", "is_active": "boolean", "profile_enabled": "boolean", "sort_order": "integer", "created_by": "integer", "updated_by": "integer"}`

## school_leadership_positions
[app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| leaders | hasMany | [{"class": "App\\Models\\SchoolLeader", "constant": "class"}, "position_id"] |

Schema migration files: [database/migrations/2026_10_02_075947_create_school_leadership_positions_table.php](#file-0deec8b68bbd)
Casts: `{"id": "int", "rank_group": "App\\Enums\\SchoolLeadershipRankGroup", "sort_order": "integer", "show_on_home": "boolean", "is_active": "boolean"}`

## site_settings
[app/Models/SiteSetting.php](#file-bb56abed8eb0) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| logo | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "logo_media_id"] |
| favicon | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "favicon_media_id"] |
| commanderImage | belongsTo | [{"class": "App\\Models\\MediaAsset", "constant": "class"}, "commander_image_media_id"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |

Schema migration files: [database/migrations/2026_08_27_170002_create_site_settings_table.php](#file-c054ddab61c7), [database/migrations/2026_09_28_093500_add_sinhala_fields_to_site_settings_table.php](#file-3f6e1f6d25d6)
Casts: `{"id": "int", "logo_media_id": "integer", "favicon_media_id": "integer", "commander_image_media_id": "integer", "maintenance_mode": "boolean"}`

## social_links
[app/Models/SocialLink.php](#file-217d07b8a20a) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |

Schema migration files: [database/migrations/2026_08_27_170003_create_social_links_table.php](#file-71f7a3941b31)
Casts: `{"id": "int", "is_active": "boolean", "sort_order": "integer"}`

## theme_layouts
[app/Models/ThemeLayout.php](#file-839afe994d82) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| revisions | hasMany | [{"class": "App\\Models\\ThemeLayoutRevision", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "updated_by"] |
| publisher | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "published_by"] |

Schema migration files: [database/migrations/2026_09_03_090000_create_theme_layouts_tables.php](#file-86c73fbb2cd2), [database/migrations/2026_09_03_110000_add_locale_to_theme_layouts_table.php](#file-df0f3591f4e4)
Casts: `{"id": "int", "region": "App\\Enums\\ThemeLayoutRegion", "locale": "App\\Enums\\ThemeLayoutLocale", "status": "App\\Enums\\ThemeLayoutStatus", "revision_number": "integer", "published_at": "immutable_datetime"}`

## theme_layout_revisions
[app/Models/ThemeLayoutRevision.php](#file-0becd2558476) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| themeLayout | belongsTo | [{"class": "App\\Models\\ThemeLayout", "constant": "class"}] |
| creator | belongsTo | [{"class": "App\\Models\\User", "constant": "class"}, "created_by"] |

Schema migration files: [database/migrations/2026_09_03_090000_create_theme_layouts_tables.php](#file-86c73fbb2cd2)
Casts: `{"id": "int", "revision_number": "integer", "status": "App\\Enums\\ThemeLayoutStatus", "created_at": "immutable_datetime"}`

## users
[app/Models/User.php](#file-a90f2e50cb06) · key: `id` · soft deletes: False

| Relationship | Type | Arguments |
| --- | --- | --- |
| creator | belongsTo | [{"class": "self", "constant": "class"}, "created_by"] |
| updater | belongsTo | [{"class": "self", "constant": "class"}, "updated_by"] |

Schema migration files: [database/migrations/0001_01_01_000000_create_users_table.php](#file-1697e8e6206b), [database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php](#file-997067e5680d), [database/migrations/2026_07_30_072444_add_management_fields_to_users_table.php](#file-c24eb66899ea)
Casts: `{"id": "int", "email_verified_at": "datetime", "password": "hashed", "is_active": "boolean", "last_login_at": "datetime"}`


# Appendix D — Every project file and detected connections

579-entry file catalog count is generated from the snapshot; dependency/generated/private content is in all-files.csv. Imports/declarations and conditional/conventional references do not prove that a branch executes on every request.

<a id="file-ef4010c96b9a"></a>
## .cursor/mcp.json

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 187 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-d8b3ca753f00"></a>
## .cursor/skills/fluxui-development/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 2,742 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-617d712b81dc"></a>
## .cursor/skills/fortify-development/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 7,575 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-49fb3a846a89"></a>
## .cursor/skills/laravel-best-practices/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 4,638 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-40692c42df55"></a>
## .cursor/skills/laravel-best-practices/rules/advanced-queries.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,936 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-a97814556025"></a>
## .cursor/skills/laravel-best-practices/rules/architecture.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 5,106 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-832860b23c5b"></a>
## .cursor/skills/laravel-best-practices/rules/blade-views.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,211 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-70d62bc53bbb"></a>
## .cursor/skills/laravel-best-practices/rules/caching.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 2,435 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-d8f25399f204"></a>
## .cursor/skills/laravel-best-practices/rules/collections.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,300 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-d8275cf5f57b"></a>
## .cursor/skills/laravel-best-practices/rules/config.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,568 bytes.

Config reads: `services.key`.

**Uses:**
- [config/services.php](#file-67cde7ebf0c8) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-3060f516e0f0"></a>
## .cursor/skills/laravel-best-practices/rules/db-performance.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 4,197 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b3373156ff34"></a>
## .cursor/skills/laravel-best-practices/rules/eloquent.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 4,008 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-eba48ffc4f43"></a>
## .cursor/skills/laravel-best-practices/rules/error-handling.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 2,210 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-03e616d39fc8"></a>
## .cursor/skills/laravel-best-practices/rules/events-notifications.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,669 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-36a8f79cd443"></a>
## .cursor/skills/laravel-best-practices/rules/http-client.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,664 bytes.

Config reads: `services.github.token`.

**Uses:**
- [config/services.php](#file-67cde7ebf0c8) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-905ae40069f5"></a>
## .cursor/skills/laravel-best-practices/rules/mail.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,251 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-ed87f483c7cc"></a>
## .cursor/skills/laravel-best-practices/rules/migrations.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,229 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-5dd2717f52ee"></a>
## .cursor/skills/laravel-best-practices/rules/queue-jobs.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,322 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-6fdc9eeeebaa"></a>
## .cursor/skills/laravel-best-practices/rules/routing.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 2,173 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-85e9a9826c76"></a>
## .cursor/skills/laravel-best-practices/rules/scheduling.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,342 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-9a21b1286265"></a>
## .cursor/skills/laravel-best-practices/rules/security.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,660 bytes.

Config reads: `services.api_key`.

**Uses:**
- [config/services.php](#file-67cde7ebf0c8) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-90fcd89b5053"></a>
## .cursor/skills/laravel-best-practices/rules/style.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 4,447 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b6688da0b2dd"></a>
## .cursor/skills/laravel-best-practices/rules/testing.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,544 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b4dbb246ef3c"></a>
## .cursor/skills/laravel-best-practices/rules/validation.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,690 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-6d493a4e286d"></a>
## .cursor/skills/livewire-development/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 7,552 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-0acc7e1ec875"></a>
## .cursor/skills/livewire-development/reference/javascript-hooks.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 1,073 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-5dca2b8f32e5"></a>
## .cursor/skills/pest-testing/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 5,752 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-31927365bbb7"></a>
## .cursor/skills/tailwindcss-development/SKILL.md

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 3,848 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-626a747c8694"></a>
## .editorconfig

Editor whitespace/encoding rules.

Category: Project source/configuration. Size: 275 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-3c84dcdc6bbe"></a>
## .env

Private environment configuration; contents මේ report එකෙන් කියවා/export කර නැහැ.

Category: Private environment (contents excluded). Size: 1,435 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — environment variable lookup
- [config/auth.php](#file-cddba0f39ef2) — environment variable lookup
- [config/awcms.php](#file-2eb8ad7c121f) — environment variable lookup
- [config/cache.php](#file-85b552139c7f) — environment variable lookup
- [config/database.php](#file-69e3bd8ddf95) — environment variable lookup
- [config/filesystems.php](#file-0851bcf6cd09) — environment variable lookup
- [config/fortify.php](#file-55087024cd3f) — environment variable lookup
- [config/images.php](#file-7d9b42158214) — environment variable lookup
- [config/livewire.php](#file-2ea25d0e0793) — environment variable lookup
- [config/logging.php](#file-e1f0359815a7) — environment variable lookup
- [config/mail.php](#file-2d414a971ebe) — environment variable lookup
- [config/media.php](#file-708751864f39) — environment variable lookup
- [config/queue.php](#file-eb49dc3fa726) — environment variable lookup
- [config/services.php](#file-67cde7ebf0c8) — environment variable lookup
- [config/session.php](#file-e1025c27321c) — environment variable lookup

<a id="file-d4dae00d1185"></a>
## .env.example

Environment variable template; actual production values සඳහා reference එකක් පමණි.

Category: Project source/configuration. Size: 1,224 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-24139dae6567"></a>
## .gitattributes

Git text normalization සහ repository attributes.

Category: Project source/configuration. Size: 210 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-091aff741808"></a>
## .github/dependabot.yml

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 222 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-6b8eb1151e11"></a>
## .github/workflows/tests.yml

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 789 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-a5cc2925ca82"></a>
## .gitignore

Version control තුළ generated files/secrets/dependencies exclude කරන patterns.

Category: Project source/configuration. Size: 274 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-12453d06e6e3"></a>
## .npmrc

npm project settings; dependency installation behavior වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 31 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-89aa447020cd"></a>
## .vscode/settings.json

Development/CI configuration; ordinary public request runtime එකට සාමාන්‍යයෙන් අවශ්‍ය නැහැ.

Category: Development/automation. Size: 249 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f69b2ee37384"></a>
## app/Actions/Fortify/CreateNewUser.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 834 bytes.

Symbol: `App\Actions\Fortify\CreateNewUser`.
Public methods (source line): `create():20`.

**Uses:**
- [app/Concerns/PasswordValidationRules.php](#file-6f0ab5d225ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Concerns/ProfileValidationRules.php](#file-45f030676db0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7779cc52d7e8"></a>
## app/Actions/Fortify/ResetUserPassword.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 686 bytes.

Symbol: `App\Actions\Fortify\ResetUserPassword`.
Public methods (source line): `reset():19`.

**Uses:**
- [app/Concerns/PasswordValidationRules.php](#file-6f0ab5d225ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-6f0ab5d225ca"></a>
## app/Concerns/PasswordValidationRules.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 736 bytes.

Symbol: `App\Concerns\PasswordValidationRules`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Actions/Fortify/CreateNewUser.php](#file-f69b2ee37384) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Actions/Fortify/ResetUserPassword.php](#file-7779cc52d7e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Settings/DeleteUserForm.php](#file-41247b44803c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Settings/Security.php](#file-2ba603ddc070) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-45f030676db0"></a>
## app/Concerns/ProfileValidationRules.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 1,258 bytes.

Symbol: `App\Concerns\ProfileValidationRules`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Actions/Fortify/CreateNewUser.php](#file-f69b2ee37384) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Settings/Profile.php](#file-8000c38dd596) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-fe9122512578"></a>
## app/Console/Commands/BackfillPageRevisionsCommand.php

Custom Artisan command. Signature සහ public handle() entry point බලන්න.

Category: Project source/configuration. Size: 2,555 bytes.

Symbol: `App\Console\Commands\BackfillPageRevisionsCommand` extends `Illuminate\Console\Command`.
Public methods (source line): `handle():24`.

**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-05cd0e6bd241"></a>
## app/Console/Commands/CreateSuperAdministrator.php

Custom Artisan command. Signature සහ public handle() entry point බලන්න.

Category: Project source/configuration. Size: 2,566 bytes.

Symbol: `App\Console\Commands\CreateSuperAdministrator` extends `Illuminate\Console\Command`.
Public methods (source line): `handle():20`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-9e1f8eeb7239"></a>
## app/Console/Commands/PublishThemeAssetsCommand.php

Custom Artisan command. Signature සහ public handle() entry point බලන්න.

Category: Project source/configuration. Size: 1,090 bytes.

Symbol: `App\Console\Commands\PublishThemeAssetsCommand` extends `Illuminate\Console\Command`.
Public methods (source line): `handle():17`.

**Uses:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-1e2dbe3f43db"></a>
## app/Enums/DocumentStatus.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 552 bytes.

Symbol: `App\Enums\DocumentStatus`.
Enum cases: `{"Draft": "draft", "Published": "published", "Archived": "archived"}`.
Public methods (source line): `label():13`, `isEditable():24`, `isPublic():29`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/documents/document-edit.blade.php](#file-87dc67173ff8) — fully qualified class reference
- [resources/views/livewire/admin/documents/document-index.blade.php](#file-250954eab702) — fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7d3027533936"></a>
## app/Enums/GalleryStatus.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 551 bytes.

Symbol: `App\Enums\GalleryStatus`.
Enum cases: `{"Draft": "draft", "Published": "published", "Archived": "archived"}`.
Public methods (source line): `label():13`, `isEditable():24`, `isPublic():29`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/GalleryFactory.php](#file-dcf0922ef067) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/galleries/gallery-edit.blade.php](#file-26d9997db568) — fully qualified class reference
- [resources/views/livewire/admin/galleries/gallery-index.blade.php](#file-c0bd13e9ac3d) — fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-09b190a08482"></a>
## app/Enums/MediaSource.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 302 bytes.

Symbol: `App\Enums\MediaSource`.
Enum cases: `{"Upload": "upload", "External": "external"}`.
Public methods (source line): `label():11`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/MediaAssetFactory.php](#file-92fa9c81b1bc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/migrations/2026_08_18_140000_create_media_assets_table.php](#file-58dd900a54df) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/media/media-edit.blade.php](#file-8306e12be497) — fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-f394e04b3bee"></a>
## app/Enums/MediaType.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 410 bytes.

Symbol: `App\Enums\MediaType`.
Enum cases: `{"Image": "image", "Document": "document", "Video": "video", "Other": "other"}`.
Public methods (source line): `label():15`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/MediaAssetFactory.php](#file-92fa9c81b1bc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/media/media-index.blade.php](#file-b9e7c6af277a) — fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7502de646059"></a>
## app/Enums/MediaVariantPreset.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 918 bytes.

Symbol: `App\Enums\MediaVariantPreset`.
Enum cases: `{"Thumbnail": "thumbnail", "Medium": "medium"}`.
Public methods (source line): `label():11`, `maximumWidth():22`, `maximumHeight():33`, `quality():44`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e9d006104665"></a>
## app/Enums/MediaVisibility.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 377 bytes.

Symbol: `App\Enums\MediaVisibility`.
Enum cases: `{"Public": "public", "Internal": "internal", "Restricted": "restricted"}`.
Public methods (source line): `label():13`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/MediaAssetFactory.php](#file-92fa9c81b1bc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/migrations/2026_08_18_140000_create_media_assets_table.php](#file-58dd900a54df) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/media/media-index.blade.php](#file-b9e7c6af277a) — fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-1aae4096f3ab"></a>
## app/Enums/MenuLocale.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 507 bytes.

Symbol: `App\Enums\MenuLocale`.
Enum cases: `{"English": "en", "Sinhala": "si", "Tamil": "ta"}`.
Public methods (source line): `label():11`, `fromApplicationLocale():20`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-bf209172286b"></a>
## app/Enums/NewsEditorMode.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 294 bytes.

Symbol: `App\Enums\NewsEditorMode`.
Enum cases: `{"Visual": "visual", "Html": "html"}`.
Public methods (source line): `label():10`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/news/preview.blade.php](#file-0b76d7cd09a9) — fully qualified class reference
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — fully qualified class reference
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — fully qualified class reference

<a id="file-61eca7d6e2d7"></a>
## app/Enums/NewsLocale.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 796 bytes.

Symbol: `App\Enums\NewsLocale`.
Enum cases: `{"English": "en", "Sinhala": "si", "Tamil": "ta"}`.
Public methods (source line): `label():11`, `nativeLabel():20`, `values():32`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/news/news-create.blade.php](#file-0c7497b1a13e) — fully qualified class reference
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — fully qualified class reference
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsLanguageTest.php](#file-0576177ff099) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5c17bf9c3011"></a>
## app/Enums/NewsStatus.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 958 bytes.

Symbol: `App\Enums\NewsStatus`.
Enum cases: `{"Draft": "draft", "Submitted": "submitted", "ChangesRequested": "changes_requested", "Approved": "approved", "Published": "published", "Archived": "archived"}`.
Public methods (source line): `label():19`, `isEditable():36`, `isPublic():48`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsRevisionFactory.php](#file-9e65735d446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — fully qualified class reference
- [resources/views/livewire/admin/news/news-index.blade.php](#file-bc54552a078e) — fully qualified class reference
- [resources/views/livewire/admin/news/news-revision-history.blade.php](#file-f522e26b1eac) — fully qualified class reference
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5ed0972d66b5"></a>
## app/Enums/PageBlockType.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 704 bytes.

Symbol: `App\Enums\PageBlockType`.
Enum cases: `{"Heading": "heading", "Text": "text", "Image": "image", "Button": "button", "TwoColumns": "two_columns", "Callout": "callout", "Divider": "divider", "Spacer": "spacer"}`.
Public methods (source line): `label():23`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageBlockFactory.php](#file-17020ca9e01b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-394b30936172"></a>
## app/Enums/PageEditorMode.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 294 bytes.

Symbol: `App\Enums\PageEditorMode`.
Enum cases: `{"Visual": "visual", "Html": "html"}`.
Public methods (source line): `label():10`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/PageFactory.php](#file-5f84696da2e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — fully qualified class reference
- [resources/views/pages/show.blade.php](#file-071059c97417) — fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — fully qualified class reference

<a id="file-289fe689e2ea"></a>
## app/Enums/PageLocale.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 796 bytes.

Symbol: `App\Enums\PageLocale`.
Enum cases: `{"English": "en", "Sinhala": "si", "Tamil": "ta"}`.
Public methods (source line): `label():11`, `nativeLabel():20`, `values():32`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSlugger.php](#file-a75b698dd50e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/PageFactory.php](#file-5f84696da2e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php](#file-a2efc400ea87) — fully qualified class reference
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — fully qualified class reference
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — fully qualified class reference
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/HeroSlideTranslationModelTest.php](#file-c2d127affbdc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicPageLanguageTest.php](#file-aeee5dd5126b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-93d1c151c8ef"></a>
## app/Enums/PageStatus.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 1,435 bytes.

Symbol: `App\Enums\PageStatus`.
Enum cases: `{"Draft": "draft", "Submitted": "submitted", "Approved": "approved", "Published": "published", "Archived": "archived"}`.
Public methods (source line): `label():13`, `allowedTransitions():27`, `canTransitionTo():58`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageDeletionService.php](#file-ebc069215375) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/PageFactory.php](#file-5f84696da2e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — fully qualified class reference
- [resources/views/livewire/admin/pages/page-index.blade.php](#file-75131d5829a0) — fully qualified class reference
- [resources/views/livewire/admin/pages/page-revision-history.blade.php](#file-2ff2d274429c) — fully qualified class reference
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageFoundationTest.php](#file-7fc9257c3083) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicPageRenderingTest.php](#file-e2f984c6764a) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-960291d5cc58"></a>
## app/Enums/SchoolLeadershipAppointment.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 1,738 bytes.

Symbol: `App\Enums\SchoolLeadershipAppointment`.
Enum cases: `{"Commandant": "commandant", "ChiefInstructor": "chief_instructor", "Adjutant": "adjutant", "WarrantOfficer": "warrant_officer"}`.
Public methods (source line): `label():12`, `labelEn():17`, `labelSi():27`, `labelForLocale():37`, `adminLabel():44`, `requiresCommissionedRank():53`, `homeOrder():58`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-482ec6e3487c"></a>
## app/Enums/SchoolLeadershipRank.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 4,051 bytes.

Symbol: `App\Enums\SchoolLeadershipRank`.
Enum cases: `{"FieldMarshal": "field_marshal", "General": "general", "LieutenantGeneral": "lieutenant_general", "MajorGeneral": "major_general", "Brigadier": "brigadier", "Colonel": "colonel", "LieutenantColonel": "lieutenant_colonel", "Major": "major", "Captain": "captain", "Lieutenant": "lieutenant", "SecondLieutenant": "second_lieutenant", "WarrantOfficerClassOne": "warrant_officer_class_i", "WarrantOfficerClassTwo": "warrant_officer_class_ii", "StaffSergeant": "staff_sergeant", "Sergeant": "sergeant", "Corporal": "corporal", "LanceCorporal": "lance_corporal", "Private": "private"}`.
Public methods (source line): `label():27`, `labelEn():32`, `labelSi():57`, `labelForLocale():82`, `adminLabel():89`, `isCommissioned():98`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-fc25089655a6"></a>
## app/Enums/SchoolLeadershipRankGroup.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 346 bytes.

Symbol: `App\Enums\SchoolLeadershipRankGroup`.
Enum cases: `{"Commissioned": "commissioned", "OtherRanks": "other_ranks"}`.
Public methods (source line): `label():10`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-ce4e9dedc3a3"></a>
## app/Enums/ThemeLayoutLocale.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 828 bytes.

Symbol: `App\Enums\ThemeLayoutLocale`.
Enum cases: `{"English": "en", "Sinhala": "si", "Tamil": "ta"}`.
Public methods (source line): `label():13`, `nativeLabel():22`, `fromApplicationLocale():31`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-623139e20943"></a>
## app/Enums/ThemeLayoutRegion.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 287 bytes.

Symbol: `App\Enums\ThemeLayoutRegion`.
Enum cases: `{"Header": "header", "Footer": "footer"}`.
Public methods (source line): `label():10`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutModelTest.php](#file-e307f4b73342) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — fully qualified class reference

<a id="file-21966b2dd730"></a>
## app/Enums/ThemeLayoutStatus.php

Allowed values/labels/transitions සඳහා PHP enum. Case values සහ consumers බලන්න.

Category: Project source/configuration. Size: 295 bytes.

Symbol: `App\Enums\ThemeLayoutStatus`.
Enum cases: `{"Draft": "draft", "Published": "published"}`.
Public methods (source line): `label():10`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutModelTest.php](#file-e307f4b73342) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-b42815b416ad"></a>
## app/Http/Controllers/Admin/DashboardController.php

Admin dashboard statistics/data සකස් කර admin view render කරයි.

Category: Project source/configuration. Size: 767 bytes.

Symbol: `App\Http\Controllers\Admin\DashboardController`.
Public methods (source line): `__invoke():15`.
Registered URLs: `admin` (admin.dashboard).

**Uses:**
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-451877cfc6da"></a>
## app/Http/Controllers/Admin/NewsPreviewController.php

Authorized admin news preview data සහ rendering.

Category: Project source/configuration. Size: 1,577 bytes.

Symbol: `App\Http\Controllers\Admin\NewsPreviewController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__invoke():14`.
Registered URLs: `admin/news/{news}/preview` (admin.news.preview).

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/news/preview.blade.php](#file-0b76d7cd09a9) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-66ad49bdd5c6"></a>
## app/Http/Controllers/Admin/PagePreviewController.php

Authorized admin page preview; public rendering helper/sanitization සම්බන්ධ වේ.

Category: Project source/configuration. Size: 1,860 bytes.

Symbol: `App\Http\Controllers\Admin\PagePreviewController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__invoke():15`.
Registered URLs: `admin/pages/{page}/preview` (admin.pages.preview).

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockRenderer.php](#file-e6b5c44d7560) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-2ed23e1749f0"></a>
## app/Http/Controllers/Controller.php

Shared Laravel controller base class.

Category: Project source/configuration. Size: 77 bytes.

Symbol: `App\Http\Controllers\Controller`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — PHP class/type reference
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — PHP class/type reference
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — PHP class/type reference
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference

<a id="file-9d37b14e3dd9"></a>
## app/Http/Controllers/PastChiefInstructorController.php

SchoolLeader chief_instructor records වලින් history page සහ locale handling.

Category: Project source/configuration. Size: 4,929 bytes.

Symbol: `App\Http\Controllers\PastChiefInstructorController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():16`, `__invoke():20`.
Registered URLs: `en/history/past-chief-instructors` (history.past-chief-instructors), `si/history/past-chief-instructors` (history.past-chief-instructors.localized).
Config reads: `app.name`.

**Uses:**
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/public/history/past-chief-instructors.blade.php](#file-efe1f99ce875) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-188a3669d996"></a>
## app/Http/Controllers/PastCommandantController.php

SchoolLeader commandant records වලින් history page සාදයි; theme view සහ currently missing core fallback reference එකක් ඇත.

Category: Project source/configuration. Size: 4,819 bytes.

Symbol: `App\Http\Controllers\PastCommandantController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():16`, `__invoke():20`.
Registered URLs: `en/history/past-commandants` (history.past-commandants), `si/history/past-commandants` (history.past-commandants.localized).
Config reads: `app.name`.
Unresolved literal views: `public.history.past-commandants` at line 68. Check conditional/package fallback handling.

**Uses:**
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/history/past-commandants.blade.php](#file-16d30421a085) — active-theme view candidate

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-962b3174f155"></a>
## app/Http/Controllers/PastSergeantMajorController.php

SchoolLeader warrant_officer appointment records වලින් sergeant major history සාදයි.

Category: Project source/configuration. Size: 3,603 bytes.

Symbol: `App\Http\Controllers\PastSergeantMajorController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():16`, `__invoke():20`.
Registered URLs: `en/history/past-sgt-major` (history.past-sgt-major), `si/history/past-sgt-major` (history.past-sgt-major.localized).
Config reads: `app.name`.

**Uses:**
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/public/history/past-sgt-major.blade.php](#file-83fd2c429c70) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-c20f494d48ea"></a>
## app/Http/Controllers/PublicContactController.php

Localized contact form display සහ throttled contact submission persistence.

Category: Project source/configuration. Size: 2,311 bytes.

Symbol: `App\Http\Controllers\PublicContactController`.
Public methods (source line): `create():14`, `store():29`.
Registered URLs: `contact` (contact.create), `contact` (contact.store), `{locale}/contact` (contact.create.localized), `{locale}/contact` (contact.store.localized).

**Uses:**
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/public/contact/index.blade.php](#file-e2f6ee5f6119) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-56250b59a420"></a>
## app/Http/Controllers/PublicDocumentController.php

Current bilingual public document list/detail සහ safe view/download streams.

Category: Project source/configuration. Size: 8,627 bytes.

Symbol: `App\Http\Controllers\PublicDocumentController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():15`, `index():19`, `show():35`, `showLocalized():40`, `view():51`, `download():60`, `viewFile():69`, `downloadFile():91`.
Registered URLs: `document-files/{locale}/{slug}/download` (document-files.download), `document-files/{locale}/{slug}/view` (document-files.view), `documents` (documents.index), `documents/{slug}` (documents.show), `documents/{slug}/download` (documents.download), `documents/{slug}/view` (documents.view), `{locale}/documents` (documents.index.localized), `{locale}/documents/{slug}` (documents.show.localized).

**Uses:**
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/public/documents/upload-index.blade.php](#file-2f43765e807c) — renders/layout view
- [resources/views/public/documents/upload-show.blade.php](#file-b27df7a59ee5) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-5a7c921b82e7"></a>
## app/Http/Controllers/PublicGalleryController.php

Manual/news gallery feeds, detail pages සහ localized album routing.

Category: Project source/configuration. Size: 5,578 bytes.

Symbol: `App\Http\Controllers\PublicGalleryController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `index():17`, `newsAlbum():30`, `show():46`, `localizedIndex():160`, `localizedShow():165`, `localizedNews():170`.
Registered URLs: `galleries` (galleries.index), `galleries/news/{newsId}` (galleries.news), `galleries/{slug}` (galleries.show), `{locale}/galleries` (galleries.index.localized), `{locale}/galleries/news/{newsId}` (galleries.news.localized), `{locale}/galleries/{slug}` (galleries.show.localized).

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — renders/layout view
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — renders/layout view
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-fbf276278997"></a>
## app/Http/Controllers/PublicHomeController.php

Homepage data aggregate කර active theme homepage හෝ core fallback view render කරයි.

Category: Project source/configuration. Size: 9,159 bytes.

Symbol: `App\Http\Controllers\PublicHomeController`.
Public methods (source line): `__construct():19`, `__invoke():23`.
Registered URLs: `/` (home), `{locale}` (home.localized).
Config reads: `awcms.active_theme`, `awcms.theme_locales.`.

**Uses:**
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-21bb7cdf2247"></a>
## app/Http/Controllers/PublicNewsController.php

Published localized news list/detail සහ theme fallback handling.

Category: Project source/configuration. Size: 6,835 bytes.

Symbol: `App\Http\Controllers\PublicNewsController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():18`, `index():22`, `show():50`, `imageUrl():171`.
Registered URLs: `news` (news.index), `news/{slug}` (news.show), `{locale}/news` (news.index.localized), `{locale}/news/{slug}` (news.show.localized).

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/admin/news/preview.blade.php](#file-0b76d7cd09a9) — fully qualified class reference
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — fully qualified class reference
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — fully qualified class reference
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [themes/school-of-signals/views/news/index.blade.php](#file-8fdadeebde29) — fully qualified class reference
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — fully qualified class reference

<a id="file-8c7b58255701"></a>
## app/Http/Controllers/PublicPageController.php

Locale/slug/published date අනුව CMS page load කර sanitized HTML/blocks/SEO render කරයි.

Category: Project source/configuration. Size: 6,552 bytes.

Symbol: `App\Http\Controllers\PublicPageController` extends `App\Http\Controllers\Controller`.
Public methods (source line): `__construct():19`, `__invoke():23`.
Registered URLs: `pages/{slug}` (pages.show), `{locale}/pages/{slug}` (pages.show.localized).

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Controller.php](#file-2ed23e1749f0) — PHP class/type reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockRenderer.php](#file-e6b5c44d7560) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/pages/show.blade.php](#file-071059c97417) — renders/layout view
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — active-theme view candidate

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-4c0a934c620d"></a>
## app/Http/Controllers/PublicRedirectController.php

Unmatched request සඳහා configured redirect lookup/fallback.

Category: Project source/configuration. Size: 898 bytes.

Symbol: `App\Http\Controllers\PublicRedirectController`.
Public methods (source line): `__invoke():13`.
Registered URLs: `{fallbackPlaceholder}` (None).

**Uses:**
- [app/Models/Redirect.php](#file-1efb27655fd7) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-49482468b2fa"></a>
## app/Http/Controllers/PublicSchoolLeadershipController.php

Current/history/commandant/profile public leadership endpoints.

Category: Project source/configuration. Size: 8,253 bytes.

Symbol: `App\Http\Controllers\PublicSchoolLeadershipController`.
Public methods (source line): `index():14`, `commandant():64`, `history():69`, `show():96`, `profile():112`.
Registered URLs: `school-leadership` (school-leadership.index), `school-leadership/commandant` (school-leadership.commandant), `school-leadership/history` (school-leadership.history), `school-leadership/profiles/{leader}` (school-leadership.show), `school-leadership/{appointment}` (school-leadership.profile), `{locale}/school-leadership` (school-leadership.localized), `{locale}/school-leadership/commandant` (school-leadership.commandant.localized), `{locale}/school-leadership/history` (school-leadership.history.localized), `{locale}/school-leadership/profiles/{leader}` (school-leadership.show.localized), `{locale}/school-leadership/{appointment}` (school-leadership.profile.localized).
Config reads: `awcms.theme_locales.school-of-signals`.

**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [themes/school-of-signals/views/school-leadership.blade.php](#file-81978c1aae5b) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-8fd9e4844fce"></a>
## app/Http/Middleware/EnsureSiteIsPublic.php

Request pipeline guard; next handler වෙත යැවීම හෝ response/redirect තේරීම.

Category: Project source/configuration. Size: 1,119 bytes.

Symbol: `App\Http\Middleware\EnsureSiteIsPublic`.
Public methods (source line): `handle():15`.
Explicit permission literals: `admin.access`.

**Uses:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/public/maintenance.blade.php](#file-073e6bcc97a1) — renders/layout view

**Used by:**
- [bootstrap/app.php](#file-f4e0869689d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-1e17969532c6"></a>
## app/Http/Middleware/EnsureUserIsActive.php

Request pipeline guard; next handler වෙත යැවීම හෝ response/redirect තේරීම.

Category: Project source/configuration. Size: 844 bytes.

Symbol: `App\Http\Middleware\EnsureUserIsActive`.
Public methods (source line): `handle():13`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [bootstrap/app.php](#file-f4e0869689d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-d373b0ae148b"></a>
## app/Livewire/Actions/Logout.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 501 bytes.

Symbol: `App\Livewire\Actions\Logout`.
Public methods (source line): `__invoke():15`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Settings/DeleteUserForm.php](#file-41247b44803c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-536a5e50ec90"></a>
## app/Livewire/Admin/AuditLogs/AuditLogIndex.php

AuditLogs server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 5,694 bytes.

Symbol: `App\Livewire\Admin\AuditLogs\AuditLogIndex` extends `Livewire\Component`.
Public methods (source line): `mount():45`, `updatedSearch():52`, `updatedEvent():57`, `updatedDateFrom():62`, `updatedDateTo():67`, `updatedPerPage():72`, `resetFilters():79`, `viewLog():91`, `closeDetails():100`, `render():105`.
Registered URLs: `admin/audit-logs` (admin.audit-logs.index).
Explicit permission literals: `audit.view`.

**Uses:**
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/audit-logs/audit-log-index.blade.php](#file-3b0429b36634) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-3041eb541004"></a>
## app/Livewire/Admin/ContactMessages/ContactMessageIndex.php

ContactMessages server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 2,647 bytes.

Symbol: `App\Livewire\Admin\ContactMessages\ContactMessageIndex` extends `Livewire\Component`.
Public methods (source line): `mount():26`, `updatedSearch():31`, `updatedStatusFilter():36`, `openMessage():41`, `resolve():48`, `render():55`.
Registered URLs: `admin/contact-messages` (admin.contact-messages.index).
Explicit permission literals: `contacts.manage`.

**Uses:**
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContactMessageService.php](#file-6e739a301761) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/contact-messages/contact-message-index.blade.php](#file-e518ccdf4bb1) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7aaafb6cc76d"></a>
## app/Livewire/Admin/Documents/DocumentCategoryIndex.php

Documents server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 8,715 bytes.

Symbol: `App\Livewire\Admin\Documents\DocumentCategoryIndex` extends `Livewire\Component`.
Public methods (source line): `mount():33`, `openCreate():40`, `openEdit():51`, `closeForm():101`, `save():106`, `toggleActive():176`, `clearSearch():223`, `render():259`.
Registered URLs: `admin/documents/categories` (admin.documents.categories.index).
Explicit permission literals: `documents.categories.manage`.

**Uses:**
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentCategoryService.php](#file-e2617061d806) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/documents/document-category-index.blade.php](#file-0dab354351c1) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/Documents/DocumentCategoryUiTest.php](#file-419e4044300e) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c47199c40bce"></a>
## app/Livewire/Admin/Documents/DocumentCreate.php

Documents server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 5,740 bytes.

Symbol: `App\Livewire\Admin\Documents\DocumentCreate` extends `Livewire\Component`.
Public methods (source line): `mount():32`, `save():39`, `render():94`.
Explicit permission literals: `documents.create`.

**Uses:**
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/documents/document-create.blade.php](#file-cf8447dd5e55) — renders/layout view

**Used by:**
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-2f6ee329ac54"></a>
## app/Livewire/Admin/Documents/DocumentEdit.php

Documents server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 16,571 bytes.

Symbol: `App\Livewire\Admin\Documents\DocumentEdit` extends `Livewire\Component`.
Public methods (source line): `mount():59`, `save():86`, `addVersion():145`, `restoreVersion():232`, `publish():274`, `archive():305`, `render():318`.
Explicit permission literals: `documents.publish`, `documents.update`, `documents.view`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/documents/document-edit.blade.php](#file-87dc67173ff8) — renders/layout view

**Used by:**
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-be4cff5447cb"></a>
## app/Livewire/Admin/Documents/DocumentIndex.php

Documents server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 7,173 bytes.

Symbol: `App\Livewire\Admin\Documents\DocumentIndex` extends `Livewire\Component`.
Public methods (source line): `mount():33`, `updatedSearch():40`, `updatedStatusFilter():45`, `updatedPerPage():50`, `resetFilters():70`, `sort():87`, `delete():130`, `render():158`.
Explicit permission literals: `documents.delete`, `documents.view`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/documents/document-index.blade.php](#file-250954eab702) — renders/layout view

**Used by:**
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-63c6dcb10c0e"></a>
## app/Livewire/Admin/Documents/DocumentUploads.php

Documents server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 10,344 bytes.

Symbol: `App\Livewire\Admin\Documents\DocumentUploads` extends `Livewire\Component`.
Public methods (source line): `mount():50`, `updatedSearch():59`, `updatedStatusFilter():64`, `updatedPerPage():81`, `sortBy():98`, `clearTableFilters():126`, `create():134`, `edit():141`, `cancel():165`, `save():172`, `delete():254`, `render():275`.
Registered URLs: `admin/documents` (admin.documents.index), `admin/documents/create` (admin.documents.create), `admin/documents/{document}/edit` (admin.documents.edit).
Explicit permission literals: `documents.create`, `documents.publish`, `documents.update`, `documents.view`, `media.upload`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-e4f04c336ba1"></a>
## app/Livewire/Admin/Galleries/GalleryCreate.php

Galleries server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 3,760 bytes.

Symbol: `App\Livewire\Admin\Galleries\GalleryCreate` extends `Livewire\Component`.
Public methods (source line): `mount():31`, `removeImage():36`, `save():44`, `render():102`.
Registered URLs: `admin/galleries/create` (admin.galleries.create).
Explicit permission literals: `galleries.create`, `galleries.update`, `media.upload`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/galleries/gallery-create.blade.php](#file-79a5e6e972ca) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c088855056da"></a>
## app/Livewire/Admin/Galleries/GalleryEdit.php

Galleries server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 26,843 bytes.

Symbol: `App\Livewire\Admin\Galleries\GalleryEdit` extends `Livewire\Component`.
Public methods (source line): `mount():83`, `save():112`, `addImage():172`, `updateImage():257`, `removeImage():319`, `moveImageUp():351`, `moveImageDown():361`, `publish():377`, `unpublish():415`, `uploadImages():446`, `selectCover():471`, `delete():480`, `render():488`.
Registered URLs: `admin/galleries/{gallery}/edit` (admin.galleries.edit).
Explicit permission literals: `galleries.delete`, `galleries.publish`, `galleries.update`, `galleries.view`, `media.upload`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/galleries/gallery-edit.blade.php](#file-26d9997db568) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-67e7ffe3f965"></a>
## app/Livewire/Admin/Galleries/GalleryIndex.php

Galleries server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 7,276 bytes.

Symbol: `App\Livewire\Admin\Galleries\GalleryIndex` extends `Livewire\Component`.
Public methods (source line): `mount():33`, `updatedSearch():40`, `updatedStatusFilter():45`, `updatedPerPage():50`, `resetFilters():70`, `sort():87`, `delete():129`, `publish():157`, `unpublish():166`, `render():175`.
Registered URLs: `admin/galleries` (admin.galleries.index).
Explicit permission literals: `galleries.delete`, `galleries.publish`, `galleries.view`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/galleries/gallery-index.blade.php](#file-c0bd13e9ac3d) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-4e671f1927da"></a>
## app/Livewire/Admin/HeroSlides/HeroSlideIndex.php

HeroSlides server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 17,320 bytes.

Symbol: `App\Livewire\Admin\HeroSlides\HeroSlideIndex` extends `Livewire\Component`.
Public methods (source line): `mount():63`, `startCreate():68`, `save():77`, `edit():277`, `cancelEdit():335`, `closeEditor():340`, `toggleActive():347`, `moveUp():367`, `moveDown():374`, `delete():381`, `render():398`, `updatedNewImage():476`, `clearNewImage():483`.
Registered URLs: `admin/hero-slides` (admin.hero-slides.index).
Explicit permission literals: `hero-slides.create`, `hero-slides.delete`, `hero-slides.update`, `hero-slides.view`, `media.upload`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php](#file-a2efc400ea87) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-21-hero-modal-deployment.py](#file-b58be82cd8da) — literal project path
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolModulePermissionUiTest.php](#file-5493846d3c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c25fa36484ed"></a>
## app/Livewire/Admin/Media/MediaEdit.php

Media server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 11,808 bytes.

Symbol: `App\Livewire\Admin\Media\MediaEdit` extends `Livewire\Component`.
Public methods (source line): `mount():42`, `save():68`, `replaceFile():124`, `render():254`.
Registered URLs: `admin/media/{media}/edit` (admin.media.edit).
Explicit permission literals: `media.replace`, `media.update`, `media.view`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/media/media-edit.blade.php](#file-8306e12be497) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-cc0494b8c04b"></a>
## app/Livewire/Admin/Media/MediaIndex.php

Media server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 28,890 bytes.

Symbol: `App\Livewire\Admin\Media\MediaIndex` extends `Livewire\Component`.
Public methods (source line): `mount():41`, `updatedSearch():46`, `updatedTypeFilter():51`, `updatedVisibilityFilter():56`, `updatedUsageType():61`, `updatedView():81`, `setLibraryMode():97`, `setUsageType():118`, `openUsageGroup():139`, `closeUsageGroup():164`, `clearFilters():172`, `deleteMedia():181`, `restoreMedia():201`, `forceDeleteMedia():221`, `render():241`.
Registered URLs: `admin/media` (admin.media.index).
Explicit permission literals: `media.delete`, `media.view`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaDeletionService.php](#file-beb486d6c9dc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/MediaIndexDeletionUiTest.php](#file-689bc91f5112) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-25d5e88e3901"></a>
## app/Livewire/Admin/Media/MediaUpload.php

Media server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 8,164 bytes.

Symbol: `App\Livewire\Admin\Media\MediaUpload` extends `Livewire\Component`.
Public methods (source line): `mount():38`, `updatedType():45`, `updatedFile():54`, `clearFile():61`, `save():70`, `render():239`.
Registered URLs: `admin/media/upload` (admin.media.upload).
Explicit permission literals: `media.upload`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/media/media-upload.blade.php](#file-109c570ea309) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e17c1268834d"></a>
## app/Livewire/Admin/News/NewsCategoryIndex.php

News server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 7,885 bytes.

Symbol: `App\Livewire\Admin\News\NewsCategoryIndex` extends `Livewire\Component`.
Public methods (source line): `mount():33`, `openCreate():40`, `openEdit():51`, `closeForm():100`, `save():105`, `toggleActive():176`, `clearSearch():221`, `render():257`.
Registered URLs: `admin/news/categories` (admin.news.categories.index).
Explicit permission literals: `news.categories.manage`.

**Uses:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsCategoryService.php](#file-c836ba39f3a1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/news/news-category-index.blade.php](#file-5a25b8e17834) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/NewsCategoryUiTest.php](#file-7df7d018446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5abc13eeaeb5"></a>
## app/Livewire/Admin/News/NewsCreate.php

News server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 24,191 bytes.

Symbol: `App\Livewire\Admin\News\NewsCreate` extends `Livewire\Component`.
Public methods (source line): `mount():84`, `updatedTitle():202`, `updatedSlug():215`, `updatedLocale():224`, `regenerateSlug():241`, `save():250`, `saveAndPublish():334`, `reorderPendingGalleryImages():382`, `render():654`.
Registered URLs: `admin/news/create` (admin.news.create), `admin/news/{newsId}/translations/{locale}/create` (admin.news.translations.create).
Explicit permission literals: `media.upload`, `news.create`, `news.publish`, `news.update`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/news/news-create.blade.php](#file-0c7497b1a13e) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-484456672e53"></a>
## app/Livewire/Admin/News/NewsEdit.php

News server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 35,684 bytes.

Symbol: `App\Livewire\Admin\News\NewsEdit` extends `Livewire\Component`.
Public methods (source line): `mount():86`, `updatedTitle():175`, `updatedSlug():188`, `regenerateSlug():197`, `save():206`, `uploadGalleryImages():404`, `removeGalleryImage():861`, `selectFeaturedImage():960`, `clearFeaturedImage():1081`, `reorderGalleryImages():1233`, `render():1384`.
Registered URLs: `admin/news/{news}/edit` (admin.news.edit).
Explicit permission literals: `media.upload`, `news.update`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e77d9eae07c9"></a>
## app/Livewire/Admin/News/NewsIndex.php

News server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 8,640 bytes.

Symbol: `App\Livewire\Admin\News\NewsIndex` extends `Livewire\Component`.
Public methods (source line): `mount():60`, `updatedSearch():66`, `updatedStatus():71`, `updatedCategory():77`, `updatedLocale():83`, `updatedRecordState():89`, `updatedPerPage():95`, `resetFilters():101`, `sort():114`, `publishArticle():134`, `unpublishArticle():149`, `deleteArticle():164`, `restoreArticle():180`, `render():196`.
Registered URLs: `admin/news` (admin.news.index).
Explicit permission literals: `news.categories.manage`, `news.view`.

**Uses:**
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/news/news-index.blade.php](#file-bc54552a078e) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c9270ab1eb3b"></a>
## app/Livewire/Admin/News/NewsRevisionHistory.php

News server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 4,989 bytes.

Symbol: `App\Livewire\Admin\News\NewsRevisionHistory` extends `Livewire\Component`.
Public methods (source line): `mount():27`, `selectRevision():44`, `clearSelection():66`, `restoreSelectedRevision():74`, `render():123`.
Registered URLs: `admin/news/{news}/revisions` (admin.news.revisions).
Explicit permission literals: `news.update`, `news.view`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/news/news-revision-history.blade.php](#file-f522e26b1eac) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler

<a id="file-55f854022c60"></a>
## app/Livewire/Admin/Pages/PageCreate.php

Pages server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 17,731 bytes.

Symbol: `App\Livewire\Admin\Pages\PageCreate` extends `Livewire\Component`.
Public methods (source line): `mount():75`, `updatedTitle():162`, `updatedSlug():173`, `updatedLocale():182`, `regenerateSlug():197`, `setEditorMode():210`, `addBlock():246`, `removeBlock():274`, `moveBlockUp():299`, `moveBlockDown():322`, `save():342`, `render():616`.
Registered URLs: `admin/pages/create` (admin.pages.create), `admin/pages/{pageId}/translations/{locale}/create` (admin.pages.translations.create).
Explicit permission literals: `pages.create`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Rules/ValidPageCss.php](#file-d416e9fb861c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageBlockFactory.php](#file-17020ca9e01b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSlugger.php](#file-a75b698dd50e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-bfa6a6753fc6"></a>
## app/Livewire/Admin/Pages/PageEdit.php

Pages server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 26,431 bytes.

Symbol: `App\Livewire\Admin\Pages\PageEdit` extends `Livewire\Component`.
Public methods (source line): `mount():74`, `updatedTitle():167`, `updatedSlug():178`, `regenerateSlug():187`, `setEditorMode():200`, `addBlock():240`, `removeBlock():266`, `moveBlockUp():289`, `moveBlockDown():310`, `save():328`, `render():882`.
Registered URLs: `admin/pages/{page}/edit` (admin.pages.edit).
Explicit permission literals: `pages.update`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Rules/ValidPageCss.php](#file-d416e9fb861c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageBlockFactory.php](#file-17020ca9e01b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSlugger.php](#file-a75b698dd50e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e179a105470c"></a>
## app/Livewire/Admin/Pages/PageIndex.php

Pages server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 9,533 bytes.

Symbol: `App\Livewire\Admin\Pages\PageIndex` extends `Livewire\Component`.
Public methods (source line): `mount():65`, `updatedSearch():72`, `updatedStatus():77`, `updatedLocale():83`, `updatedRecordState():89`, `updatedPerPage():95`, `resetFilters():101`, `sort():114`, `submitPage():136`, `approvePage():151`, `publishPage():166`, `unpublishPage():181`, `returnPageToDraft():196`, `deletePage():211`, `restorePage():227`, `render():245`.
Registered URLs: `admin/pages` (admin.pages.index).
Explicit permission literals: `pages.view`.

**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageDeletionService.php](#file-ebc069215375) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/pages/page-index.blade.php](#file-75131d5829a0) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c4c24d2889f6"></a>
## app/Livewire/Admin/Pages/PageRevisionHistory.php

Pages server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 4,554 bytes.

Symbol: `App\Livewire\Admin\Pages\PageRevisionHistory` extends `Livewire\Component`.
Public methods (source line): `mount():23`, `selectRevision():41`, `restoreRevision():56`, `render():93`.
Registered URLs: `admin/pages/{page}/revisions` (admin.pages.revisions).
Explicit permission literals: `pages.revisions.restore`, `pages.revisions.view`.

**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/pages/page-revision-history.blade.php](#file-2ff2d274429c) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5c69212d1a67"></a>
## app/Livewire/Admin/Redirects/RedirectIndex.php

Redirects server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 2,968 bytes.

Symbol: `App\Livewire\Admin\Redirects\RedirectIndex` extends `Livewire\Component`.
Public methods (source line): `mount():26`, `edit():31`, `save():41`, `delete():64`, `cancel():73`, `render():78`.
Registered URLs: `admin/redirects` (admin.redirects.index).
Explicit permission literals: `redirects.manage`.

**Uses:**
- [app/Models/Redirect.php](#file-1efb27655fd7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/RedirectService.php](#file-7ca08192da71) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/redirects/redirect-index.blade.php](#file-252bb8a9c7c9) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e09106fdf445"></a>
## app/Livewire/Admin/Roles/RolePermissionIndex.php

Roles server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 9,191 bytes.

Symbol: `App\Livewire\Admin\Roles\RolePermissionIndex` extends `Livewire\Component`.
Public methods (source line): `mount():29`, `selectRole():45`, `createRole():58`, `savePermissions():106`, `render():164`.
Registered URLs: `admin/roles` (admin.roles.index).
Explicit permission literals: `roles.manage`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/roles/role-permission-index.blade.php](#file-8529b03919da) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/RolePermissionManagementTest.php](#file-82b88f2fd69a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolModulePermissionUiTest.php](#file-5493846d3c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5167e1c4a98c"></a>
## app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php

SchoolLeaders server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 23,002 bytes.

Symbol: `App\Livewire\Admin\SchoolLeaders\SchoolLeaderIndex` extends `Livewire\Component`.
Public methods (source line): `mount():76`, `startCreate():85`, `edit():96`, `closeEditor():165`, `updatedSearch():174`, `updatedStatusFilter():179`, `updatedPositionFilter():196`, `updatedPerPage():208`, `sortBy():225`, `resetTableFilters():251`, `updatedRoleKey():260`, `updatedIsCurrent():315`, `save():334`, `delete():621`, `updatedNewImage():655`, `clearNewImage():664`, `render():675`.
Registered URLs: `admin/school-leaders` (admin.school-leaders.index).
Explicit permission literals: `media.upload`, `school-leaders.update`, `school-leaders.view`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/SchoolLeadershipRank.php](#file-482ec6e3487c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolModulePermissionUiTest.php](#file-5493846d3c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-b9f46b401967"></a>
## app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php

SchoolLeaders server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 7,874 bytes.

Symbol: `App\Livewire\Admin\SchoolLeaders\SchoolLeaderProfiles` extends `Livewire\Component`.
Public methods (source line): `mount():39`, `updatedSelectedLeaderId():52`, `insertTemplate():59`, `preview():82`, `save():90`, `render():136`.
Registered URLs: `admin/school-leadership-profiles` (admin.school-leadership-profiles.index).
Explicit permission literals: `school-leaders.update`, `school-leaders.view`.

**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php](#file-b464536172c0) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-a53f8a5c6b37"></a>
## app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php

SchoolLeaders server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 12,084 bytes.

Symbol: `App\Livewire\Admin\SchoolLeaders\SchoolLeadershipPositionIndex` extends `Livewire\Component`.
Public methods (source line): `mount():55`, `startCreate():62`, `edit():70`, `closeEditor():92`, `updatedSearch():101`, `updatedStatusFilter():106`, `updatedHomepageFilter():123`, `updatedRankGroupFilter():140`, `updatedPerPage():154`, `sortBy():171`, `resetTableFilters():200`, `save():210`, `render():296`.
Registered URLs: `admin/school-leadership-positions` (admin.school-leadership-positions.index).
Explicit permission literals: `school-leaders.update`, `school-leaders.view`.

**Uses:**
- [app/Enums/SchoolLeadershipRankGroup.php](#file-fc25089655a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-484bcafc91d7"></a>
## app/Livewire/Admin/Settings/SiteSettingsIndex.php

Settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 12,547 bytes.

Symbol: `App\Livewire\Admin\Settings\SiteSettingsIndex` extends `Livewire\Component`.
Public methods (source line): `mount():79`, `save():88`, `addSocialLink():173`, `deleteSocialLink():223`, `render():239`.
Registered URLs: `admin/site-settings` (admin.site-settings.index).
Explicit permission literals: `settings.manage`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SocialLink.php](#file-217d07b8a20a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/SiteSettingService.php](#file-c9d2f82fdd61) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/settings/site-settings-index.blade.php](#file-d5bf1003d076) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-371d04803d32"></a>
## app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php

ThemeLayouts server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 6,795 bytes.

Symbol: `App\Livewire\Admin\ThemeLayouts\ThemeLayoutIndex` extends `Livewire\Component`.
Public methods (source line): `mount():35`, `selectLocale():56`, `selectRegion():73`, `saveDraft():90`, `publish():119`, `render():146`.
Registered URLs: `admin/theme-layouts` (admin.theme-layouts.index).
Config reads: `awcms.active_theme`.
Explicit permission literals: `theme-layouts.manage`.

**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/theme-layouts/theme-layout-index.blade.php](#file-a1e9a891a029) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c07bf0f22937"></a>
## app/Livewire/Admin/Users/UserCreate.php

Users server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 4,235 bytes.

Symbol: `App\Livewire\Admin\Users\UserCreate` extends `Livewire\Component`.
Public methods (source line): `mount():31`, `save():37`, `render():110`.
Registered URLs: `admin/users/create` (admin.users.create).
Explicit permission literals: `users.assign-role`, `users.create`, `users.update`, `users.view`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/users/user-create.blade.php](#file-c31aaeb421c0) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserManagementFormTest.php](#file-29370412f779) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-162a921b1cfe"></a>
## app/Livewire/Admin/Users/UserEdit.php

Users server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 11,558 bytes.

Symbol: `App\Livewire\Admin\Users\UserEdit` extends `Livewire\Component`.
Public methods (source line): `mount():29`, `save():47`, `resetPassword():180`, `render():296`.
Registered URLs: `admin/users/{user}/edit` (admin.users.edit).
Explicit permission literals: `users.assign-role`, `users.reset-password`, `users.update`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/users/user-edit.blade.php](#file-44618f864c33) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserManagementFormTest.php](#file-29370412f779) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e81e9bfe9fb5"></a>
## app/Livewire/Admin/Users/UserIndex.php

Users server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 6,433 bytes.

Symbol: `App\Livewire\Admin\Users\UserIndex` extends `Livewire\Component`.
Public methods (source line): `mount():46`, `updatedSearch():52`, `updatedRole():57`, `updatedStatus():63`, `updatedPerPage():69`, `resetFilters():75`, `sort():83`, `toggleActive():99`, `render():145`.
Registered URLs: `admin/users` (admin.users.index).
Explicit permission literals: `users.update`, `users.view`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — renders/layout view
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — renders/layout view

**Used by:**
- [routes/web.php](#file-6b9a783d9b43) — registered route handler
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserManagementIndexTest.php](#file-e113722c846b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-082cda167128"></a>
## app/Livewire/Settings/Appearance.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 175 bytes.

Symbol: `App\Livewire\Settings\Appearance` extends `Livewire\Component`.
Registered URLs: `settings/appearance` (appearance.edit).

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [routes/settings.php](#file-ef358245ae87) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler

<a id="file-41247b44803c"></a>
## app/Livewire/Settings/DeleteUserForm.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 613 bytes.

Symbol: `App\Livewire\Settings\DeleteUserForm` extends `Livewire\Component`.
Public methods (source line): `deleteUser():19`.

**Uses:**
- [app/Concerns/PasswordValidationRules.php](#file-6f0ab5d225ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Actions/Logout.php](#file-d373b0ae148b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [resources/views/livewire/settings/profile.blade.php](#file-70890f0fb0f2) — embedded Livewire component

<a id="file-8000c38dd596"></a>
## app/Livewire/Settings/Profile.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 1,953 bytes.

Symbol: `App\Livewire\Settings\Profile` extends `Livewire\Component`.
Public methods (source line): `mount():25`, `updateProfileInformation():34`, `resendVerificationNotification():54`, `hasUnverifiedEmail():69`, `showDeleteUser():77`.
Registered URLs: `settings/profile` (profile.edit).

**Uses:**
- [app/Concerns/ProfileValidationRules.php](#file-45f030676db0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [routes/settings.php](#file-ef358245ae87) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [tests/Feature/Settings/ProfileUpdateTest.php](#file-5abd44a4a7b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-2ba603ddc070"></a>
## app/Livewire/Settings/Security.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 8,658 bytes.

Symbol: `App\Livewire\Settings\Security` extends `Livewire\Component`.
Public methods (source line): `mount():75`, `updatePassword():98`, `loadPasskeys():123`, `confirmDelete():142`, `deletePasskey():154`, `closeDeleteModal():172`, `enable():182`, `showVerificationIfNecessary():215`, `confirmTwoFactor():231`, `resetVerification():245`, `disable():255`, `closeModal():265`, `modalConfig():287`.
Registered URLs: `settings/security` (security.edit).

**Uses:**
- [app/Concerns/PasswordValidationRules.php](#file-6f0ab5d225ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [routes/settings.php](#file-ef358245ae87) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [tests/Feature/Settings/SecurityTest.php](#file-4a00d81e65e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-de88561c9579"></a>
## app/Livewire/Settings/TwoFactor/RecoveryCodes.php

account/settings server-side Livewire component: state, UI actions, authorization සහ render/layout binding.

Category: Project source/configuration. Size: 1,247 bytes.

Symbol: `App\Livewire\Settings\TwoFactor\RecoveryCodes` extends `Livewire\Component`.
Public methods (source line): `mount():19`, `regenerateRecoveryCodes():27`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/settings/security.blade.php](#file-12046faf6f8a) — embedded Livewire component

<a id="file-f0b1464bc270"></a>
## app/Models/AuditLog.php

Eloquent model -> audit_logs; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 822 bytes.

Symbol: `App\Models\AuditLog` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `actor():37`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_07_30_174658_create_audit_logs_table.php](#file-931a322d6e8e) — model table schema

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/AuditLogs/AuditLogIndex.php](#file-536a5e50ec90) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/AuditLoggingTest.php](#file-ff3731f0ea22) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-60b85836b768"></a>
## app/Models/ContactMessage.php

Eloquent model -> contact_messages; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,642 bytes.

Symbol: `App\Models\ContactMessage` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `handler():64`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_08_27_170005_create_contact_messages_table.php](#file-95554a2eb929) — model table schema

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContactMessageService.php](#file-6e739a301761) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicContactTest.php](#file-b191f82c0ffd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-a5395ee5355b"></a>
## app/Models/Document.php

Eloquent model -> documents; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 5,184 bytes.

Symbol: `App\Models\Document` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `category():73`, `versions():84`, `creator():94`, `updater():102`, `publisher():110`, `archiver():118`, `titleForLocale():123`, `descriptionForLocale():140`, `scopePublished():168`, `scopeRecent():180`, `isPublished():188`, `isEditable():205`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_08_25_163254_create_documents_table.php](#file-8432a6d239a4) — model table schema
- [database/migrations/2026_09_29_082712_add_sinhala_fields_to_documents_table.php](#file-88332f27f038) — model table schema
- [database/migrations/2026_09_29_114230_add_current_version_si_to_documents_table.php](#file-e9d81c1238ac) — model table schema

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-43cbc2f55250"></a>
## app/Models/DocumentCategory.php

Eloquent model -> document_categories; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 2,669 bytes.

Symbol: `App\Models\DocumentCategory` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `documents():66`, `creator():77`, `updater():88`, `scopeActive():100`, `scopeOrdered():113`.

**Uses:**
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_08_25_163253_create_document_categories_table.php](#file-4a5630301f80) — model table schema

**Used by:**
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Services/DocumentCategoryService.php](#file-e2617061d806) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php](#file-cbb07bdddbcb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentCategoryUiTest.php](#file-419e4044300e) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-dc28558361e9"></a>
## app/Models/DocumentVersion.php

Eloquent model -> document_versions; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,797 bytes.

Symbol: `App\Models\DocumentVersion` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `document():58`, `media():68`, `uploader():79`.

**Uses:**
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_08_25_163255_create_document_versions_table.php](#file-f6faaf6d36ba) — model table schema

**Used by:**
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-87d2aaf50cbb"></a>
## app/Models/Gallery.php

Eloquent model -> galleries; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 5,292 bytes.

Symbol: `App\Models\Gallery` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `titleForLocale():60`, `coverMedia():99`, `images():110`, `creator():126`, `updater():137`, `publisher():148`, `archiver():159`, `scopePublished():171`, `scopeRecent():193`, `isPublished():208`, `isEditable():236`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/GalleryFactory.php](#file-dcf0922ef067) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_24_203114_create_galleries_table.php](#file-990b340a082e) — model table schema
- [database/migrations/2026_09_28_090000_add_sinhala_title_to_galleries_table.php](#file-7f9820166d5c) — model table schema

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/GalleryFactory.php](#file-dcf0922ef067) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/GalleryImageFactory.php](#file-3036ddba0583) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-cc816d7f9cf9"></a>
## app/Models/GalleryImage.php

Eloquent model -> gallery_images; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,542 bytes.

Symbol: `App\Models\GalleryImage` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `gallery():41`, `media():51`, `creator():62`, `updater():73`.

**Uses:**
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/GalleryImageFactory.php](#file-3036ddba0583) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_24_203115_create_gallery_images_table.php](#file-6c96d8f47c44) — model table schema

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/GalleryImageFactory.php](#file-3036ddba0583) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c36ed5aa1b49"></a>
## app/Models/HeroSlide.php

Eloquent model -> hero_slides; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 4,901 bytes.

Symbol: `App\Models\HeroSlide` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `image():70`, `translations():79`, `translation():86`, `resolvedButtonUrl():115`, `scopeActive():158`.

**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [database/migrations/2026_08_27_170004_create_hero_slides_table.php](#file-4752f166062e) — model table schema

**Used by:**
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — PHP class/type reference
- [scripts/build-2026-10-21-hero-link-deployment.py](#file-cff8ad197a2e) — literal project path
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/HeroSlideTranslationModelTest.php](#file-c2d127affbdc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-f30398e71d18"></a>
## app/Models/HeroSlideTranslation.php

Eloquent model -> hero_slide_translations; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 996 bytes.

Symbol: `App\Models\HeroSlideTranslation` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `heroSlide():40`.

**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference
- [database/migrations/2026_09_07_120621_create_hero_slide_translations_table.php](#file-1d4963af7a71) — model table schema

**Used by:**
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference
- [tests/Feature/Admin/HeroSlideTranslationModelTest.php](#file-c2d127affbdc) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-4712fd861864"></a>
## app/Models/MediaAsset.php

Eloquent model -> media_assets; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 3,147 bytes.

Symbol: `App\Models\MediaAsset` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `uploader():77`, `variants():88`, `variant():95`, `isImage():106`, `isDocument():116`, `isExternal():126`, `isPublic():136`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/MediaAssetFactory.php](#file-92fa9c81b1bc) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_18_140000_create_media_assets_table.php](#file-58dd900a54df) — model table schema

**Used by:**
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference
- [app/Models/PastChiefInstructor.php](#file-fca0ee8b2525) — PHP class/type reference
- [app/Models/PastCommandant.php](#file-2227ba16f07a) — PHP class/type reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaDeletionService.php](#file-beb486d6c9dc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/MediaAssetFactory.php](#file-92fa9c81b1bc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexDeletionUiTest.php](#file-689bc91f5112) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsMultipleImagesTest.php](#file-90cd0d72347c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-da95f7d80c02"></a>
## app/Models/MediaVariant.php

Eloquent model -> media_variants; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 828 bytes.

Symbol: `App\Models\MediaVariant` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `mediaAsset():39`.

**Uses:**
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [database/migrations/2026_08_19_091418_create_media_variants_table.php](#file-e46591bdb95e) — model table schema

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-72f78bded920"></a>
## app/Models/Menu.php

Eloquent model -> menus; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 2,201 bytes.

Symbol: `App\Models\Menu` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `items():57`, `rootItems():63`, `creator():72`, `updater():78`, `scopeActive():87`.

**Uses:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_08_27_170000_create_menus_table.php](#file-2592bb5fac61) — model table schema

**Used by:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-55fd9fcfc0cd"></a>
## app/Models/MenuItem.php

Eloquent model -> menu_items; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 7,474 bytes.

Symbol: `App\Models\MenuItem` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `menu():73`, `parent():79`, `children():85`, `translations():93`, `labelForLocale():98`, `resolvedUrl():114`.

**Uses:**
- [app/Enums/MenuLocale.php](#file-1aae4096f3ab) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference
- [app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) — PHP class/type reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference
- [database/migrations/2026_08_27_170001_create_menu_items_table.php](#file-601ba28fb85d) — model table schema
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference
- [app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) — PHP class/type reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MenuTranslationServiceTest.php](#file-49da0724cc08) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-bb96ee3b18cb"></a>
## app/Models/MenuItemTranslation.php

Eloquent model -> menu_item_translations; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 802 bytes.

Symbol: `App\Models\MenuItemTranslation` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `menuItem():35`.

**Uses:**
- [app/Enums/MenuLocale.php](#file-1aae4096f3ab) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [database/migrations/2026_09_02_160000_create_menu_item_translations_table.php](#file-36bc8a17d4b4) — model table schema

**Used by:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-9584cdf2eac3"></a>
## app/Models/News.php

Eloquent model -> news; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 6,325 bytes.

Symbol: `App\Models\News` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `revisions():79`, `images():91`, `translationVersions():103`, `category():134`, `featuredImage():145`, `creator():156`, `updater():167`, `submitter():178`, `approver():189`, `publisher():200`, `archiver():211`, `changesRequestedBy():222`, `scopePublished():234`, `scopeFeatured():256`, `isPublished():265`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_19_181914_create_news_table.php](#file-4a2176338404) — model table schema
- [database/migrations/2026_08_24_192347_add_review_workflow_fields_to_news_table.php](#file-1ce6c5531f1c) — model table schema
- [database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php](#file-87db2ff63fe7) — model table schema
- [database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php](#file-c741ddca09d7) — model table schema

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsRevisionFactory.php](#file-9e65735d446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — fully qualified class reference
- [resources/views/livewire/admin/news/news-index.blade.php](#file-bc54552a078e) — fully qualified class reference
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — fully qualified class reference
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — fully qualified class reference
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsMultipleImagesTest.php](#file-90cd0d72347c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsLanguageTest.php](#file-0576177ff099) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/news/index.blade.php](#file-8fdadeebde29) — fully qualified class reference
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — fully qualified class reference

<a id="file-f5d3b9e23d57"></a>
## app/Models/NewsCategory.php

Eloquent model -> news_categories; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,992 bytes.

Symbol: `App\Models\NewsCategory` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `creator():30`, `updater():41`, `news():52`, `scopeActive():64`, `scopeOrdered():77`.

**Uses:**
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/NewsCategoryFactory.php](#file-fb1b9502fd41) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_19_181912_create_news_categories_table.php](#file-25651cfdaf21) — model table schema

**Used by:**
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsCategoryService.php](#file-c836ba39f3a1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsCategoryFactory.php](#file-fb1b9502fd41) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsFactory.php](#file-1b46348b4e65) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsCategoryServiceTest.php](#file-f0a5049c6b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsCategoryUiTest.php](#file-7df7d018446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-865f4356df79"></a>
## app/Models/NewsImage.php

Eloquent model -> news_images; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 645 bytes.

Symbol: `App\Models\NewsImage` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `news():20`, `media():30`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [database/migrations/2026_09_02_143000_create_news_images_table.php](#file-d851a19b76a6) — model table schema

**Used by:**
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsMultipleImagesTest.php](#file-90cd0d72347c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e5608ebee6bd"></a>
## app/Models/NewsRevision.php

Eloquent model -> news_revisions; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 2,243 bytes.

Symbol: `App\Models\NewsRevision` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `news():60`, `category():66`, `featuredImage():72`, `creator():81`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/factories/NewsRevisionFactory.php](#file-9e65735d446c) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_08_19_204327_create_news_revisions_table.php](#file-e072df78cd8b) — model table schema
- [database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php](#file-87db2ff63fe7) — model table schema
- [database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php](#file-c741ddca09d7) — model table schema

**Used by:**
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsRevisionFactory.php](#file-9e65735d446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-b56bbabec185"></a>
## app/Models/Page.php

Eloquent model -> pages; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 5,816 bytes.

Symbol: `App\Models\Page` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `revisions():57`, `translationVersions():69`, `creator():81`, `updater():92`, `approver():103`, `setExcerptAttribute():111`, `scopePublished():210`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageCss.php](#file-d33f6597c9ec) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/PageFactory.php](#file-5f84696da2e3) — PHP class/type reference, PHP import (declaration)
- [database/migrations/2026_07_30_203704_create_pages_table.php](#file-28325b9ecbcd) — model table schema
- [database/migrations/2026_08_17_191344_add_seo_fields_to_pages_table.php](#file-fa467b9249c9) — model table schema
- [database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php](#file-248f792f0323) — model table schema
- [database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php](#file-fcc1329ced49) — model table schema
- [database/migrations/2026_09_01_130000_add_localisation_to_pages.php](#file-5ff441c1318d) — model table schema
- [database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php](#file-94773c7c17bf) — model table schema

**Used by:**
- [app/Console/Commands/BackfillPageRevisionsCommand.php](#file-fe9122512578) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference
- [app/Services/PageBlockRenderer.php](#file-e6b5c44d7560) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageDeletionService.php](#file-ebc069215375) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageMeta.php](#file-8a4ff76965ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSlugger.php](#file-a75b698dd50e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/PageFactory.php](#file-5f84696da2e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — fully qualified class reference
- [resources/views/livewire/admin/pages/page-index.blade.php](#file-75131d5829a0) — fully qualified class reference
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageFoundationTest.php](#file-7fc9257c3083) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ContentSanitizationTest.php](#file-80f503e1d766) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageCustomCssRenderingTest.php](#file-a233a15c12f7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicPageLanguageTest.php](#file-aeee5dd5126b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicPageRenderingTest.php](#file-e2f984c6764a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicPageSeoTest.php](#file-43c114ebc463) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-6bb9cc9d8d9b"></a>
## app/Models/PageRevision.php

Eloquent model -> page_revisions; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,117 bytes.

Symbol: `App\Models\PageRevision` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `page():39`, `creator():49`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_07_31_145549_create_page_revisions_table.php](#file-e010058feef2) — model table schema
- [database/migrations/2026_08_17_192013_add_seo_fields_to_page_revisions_table.php](#file-53982f05d42f) — model table schema
- [database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php](#file-248f792f0323) — model table schema
- [database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php](#file-fcc1329ced49) — model table schema
- [database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php](#file-94773c7c17bf) — model table schema

**Used by:**
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-fca0ee8b2525"></a>
## app/Models/PastChiefInstructor.php

Eloquent model -> past_chief_instructors; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,277 bytes.

Symbol: `App\Models\PastChiefInstructor` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `image():37`, `nameForLocale():42`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [database/migrations/2026_09_11_130000_create_past_chief_instructors_table.php](#file-60cf77e4e26d) — model table schema

**Used by:**
- [tests/Feature/PastChiefInstructorTest.php](#file-7a26d9e04789) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-2227ba16f07a"></a>
## app/Models/PastCommandant.php

Eloquent model -> past_commandants; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,390 bytes.

Symbol: `App\Models\PastCommandant` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `image():45`, `nameForLocale():50`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [database/migrations/2026_09_09_130000_create_past_commandants_table.php](#file-4c4ff27bd934) — model table schema

**Used by:**
- [tests/Feature/PastCommandantTest.php](#file-e999cb7856f9) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-1efb27655fd7"></a>
## app/Models/Redirect.php

Eloquent model -> redirects; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,673 bytes.

Symbol: `App\Models\Redirect` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `scopeActive():65`.

**Uses:**
- [database/migrations/2026_08_27_170006_create_redirects_table.php](#file-781628afda80) — model table schema

**Used by:**
- [app/Http/Controllers/PublicRedirectController.php](#file-4c0a934c620d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/RedirectService.php](#file-7ca08192da71) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/RedirectManagerTest.php](#file-332bb5013c6b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7f28db275867"></a>
## app/Models/SchoolLeader.php

Eloquent model -> school_leaders; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 7,177 bytes.

Symbol: `App\Models\SchoolLeader` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `position():92`, `image():103`, `scopeActive():115`, `scopeCurrent():128`, `titleForLocale():136`, `nameForLocale():159`, `appointment():180`, `rankOption():187`, `appointmentLabel():196`, `appointmentLabelForLocale():209`, `rankLabel():234`, `rankLabelForLocale():240`, `isCurrentAppointment():254`, `allowedRoles():266`, `supportsProfile():276`, `hasPublicProfile():282`, `profileUrlForLocale():287`.

**Uses:**
- [app/Enums/SchoolLeadershipAppointment.php](#file-960291d5cc58) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/SchoolLeadershipRank.php](#file-482ec6e3487c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference
- [database/migrations/2026_09_09_112158_create_school_leaders_table.php](#file-1ab05208ef29) — model table schema
- [database/migrations/2026_10_01_150056_add_history_fields_to_school_leaders_table.php](#file-9f99b37e38d5) — model table schema
- [database/migrations/2026_10_01_191603_drop_unique_role_key_from_school_leaders_table.php](#file-e6e3a7c6756f) — model table schema
- [database/migrations/2026_10_02_075948_add_position_id_to_school_leaders_table.php](#file-c0eac7092403) — model table schema
- [database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php](#file-3934d68405bd) — model table schema
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/SchoolLeadersSeeder.php](#file-26a97948796c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastChiefInstructorTest.php](#file-7a26d9e04789) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastCommandantTest.php](#file-e999cb7856f9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastSergeantMajorTest.php](#file-6ae4dc4b1756) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicSchoolLeadershipTest.php](#file-7bf1009b45e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-9c3415918b4c"></a>
## app/Models/SchoolLeadershipPosition.php

Eloquent model -> school_leadership_positions; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,398 bytes.

Symbol: `App\Models\SchoolLeadershipPosition` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `leaders():40`, `nameForLocale():48`, `requiresCommissionedRank():60`.

**Uses:**
- [app/Enums/SchoolLeadershipRankGroup.php](#file-fc25089655a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference
- [database/migrations/2026_10_02_075947_create_school_leadership_positions_table.php](#file-0deec8b68bbd) — model table schema

**Used by:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastSergeantMajorTest.php](#file-6ae4dc4b1756) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicSchoolLeadershipTest.php](#file-7bf1009b45e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-bb56abed8eb0"></a>
## app/Models/SiteSetting.php

Eloquent model -> site_settings; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 4,308 bytes.

Symbol: `App\Models\SiteSetting` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `localized():78`, `logo():105`, `favicon():111`, `commanderImage():117`, `updater():123`, `current():128`.
Config reads: `app.name`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [database/migrations/2026_08_27_170002_create_site_settings_table.php](#file-c054ddab61c7) — model table schema
- [database/migrations/2026_09_28_093500_add_sinhala_fields_to_site_settings_table.php](#file-3f6e1f6d25d6) — model table schema

**Used by:**
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Middleware/EnsureSiteIsPublic.php](#file-8fd9e4844fce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/SiteSettingService.php](#file-c9d2f82fdd61) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/DatabaseSeeder.php](#file-aca1efccd410) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/partials/cms-contact.blade.php](#file-17b45718646c) — fully qualified class reference

<a id="file-217d07b8a20a"></a>
## app/Models/SocialLink.php

Eloquent model -> social_links; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,487 bytes.

Symbol: `App\Models\SocialLink` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `scopeActive():60`.

**Uses:**
- [database/migrations/2026_08_27_170003_create_social_links_table.php](#file-71f7a3941b31) — model table schema

**Used by:**
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-839afe994d82"></a>
## app/Models/ThemeLayout.php

Eloquent model -> theme_layouts; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 2,891 bytes.

Symbol: `App\Models\ThemeLayout` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `revisions():79`, `creator():89`, `updater():98`, `publisher():107`.

**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_09_03_090000_create_theme_layouts_tables.php](#file-86c73fbb2cd2) — model table schema
- [database/migrations/2026_09_03_110000_add_locale_to_theme_layouts_table.php](#file-df0f3591f4e4) — model table schema

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutModelTest.php](#file-e307f4b73342) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-0becd2558476"></a>
## app/Models/ThemeLayoutRevision.php

Eloquent model -> theme_layout_revisions; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 1,218 bytes.

Symbol: `App\Models\ThemeLayoutRevision` extends `Illuminate\Database\Eloquent\Model`.
Public methods (source line): `themeLayout():40`, `creator():46`.

**Uses:**
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference
- [database/migrations/2026_09_03_090000_create_theme_layouts_tables.php](#file-86c73fbb2cd2) — model table schema

**Used by:**
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-a90f2e50cb06"></a>
## app/Models/User.php

Eloquent model -> users; relationships, casts සහ query/model events මෙහි බලන්න.

Category: Project source/configuration. Size: 2,594 bytes.

Symbol: `App\Models\User` extends `Illuminate\Foundation\Auth\User`.
Public methods (source line): `initials():76`, `creator():88`, `updater():96`.

**Uses:**
- [database/factories/UserFactory.php](#file-3401747fc769) — PHP class/type reference, PHP import (declaration)
- [database/migrations/0001_01_01_000000_create_users_table.php](#file-1697e8e6206b) — model table schema
- [database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php](#file-997067e5680d) — model table schema
- [database/migrations/2026_07_30_072444_add_management_fields_to_users_table.php](#file-c24eb66899ea) — model table schema

**Used by:**
- [.cursor/skills/laravel-best-practices/rules/style.md](#file-90fcd89b5053) — fully qualified class reference
- [app/Actions/Fortify/CreateNewUser.php](#file-f69b2ee37384) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Actions/Fortify/ResetUserPassword.php](#file-7779cc52d7e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Concerns/ProfileValidationRules.php](#file-45f030676db0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Console/Commands/CreateSuperAdministrator.php](#file-05cd0e6bd241) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Middleware/EnsureSiteIsPublic.php](#file-8fd9e4844fce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContactMessageService.php](#file-6e739a301761) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentCategoryService.php](#file-e2617061d806) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaDeletionService.php](#file-beb486d6c9dc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsCategoryService.php](#file-c836ba39f3a1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageDeletionService.php](#file-ebc069215375) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/RedirectService.php](#file-7ca08192da71) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/SiteSettingService.php](#file-c9d2f82fdd61) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/auth.php](#file-cddba0f39ef2) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/NewsRevisionFactory.php](#file-9e65735d446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/factories/UserFactory.php](#file-3401747fc769) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/DatabaseSeeder.php](#file-aca1efccd410) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — fully qualified class reference
- [tests/Feature/Admin/AdminAccessTest.php](#file-d1364c3b48d4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/AdminLayoutTest.php](#file-61561546aa52) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/AuditLoggingTest.php](#file-ff3731f0ea22) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php](#file-cbb07bdddbcb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentCategoryUiTest.php](#file-419e4044300e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexDeletionUiTest.php](#file-689bc91f5112) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MenuTranslationServiceTest.php](#file-49da0724cc08) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsCategoryServiceTest.php](#file-f0a5049c6b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsCategoryUiTest.php](#file-7df7d018446c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageFoundationTest.php](#file-7fc9257c3083) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/RedirectManagerTest.php](#file-332bb5013c6b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/RolePermissionManagementTest.php](#file-82b88f2fd69a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SchoolModulePermissionUiTest.php](#file-5493846d3c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserManagementFormTest.php](#file-29370412f779) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserManagementIndexTest.php](#file-e113722c846b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/UserSecurityTest.php](#file-0a8c21330d85) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Auth/AuthenticationTest.php](#file-ad5adf97ee88) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Auth/EmailVerificationTest.php](#file-a975c7792e9d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Auth/PasswordConfirmationTest.php](#file-ead00e8fcd5b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Auth/PasswordResetTest.php](#file-5a82152246b9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Auth/TwoFactorChallengeTest.php](#file-a2c8f70c1411) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/DashboardTest.php](#file-7f7cd4403a59) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastChiefInstructorTest.php](#file-7a26d9e04789) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/PastCommandantTest.php](#file-e999cb7856f9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Settings/ProfileUpdateTest.php](#file-5abd44a4a7b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Settings/SecurityTest.php](#file-4a00d81e65e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-48b8d8112e9b"></a>
## app/Providers/AppServiceProvider.php

Application boot integration: service registration, authentication/theme/view/authorization hooks.

Category: Project source/configuration. Size: 6,684 bytes.

Symbol: `App\Providers\AppServiceProvider` extends `Illuminate\Support\ServiceProvider`.
Public methods (source line): `register():30`, `boot():40`.

**Uses:**
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SocialLink.php](#file-217d07b8a20a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [bootstrap/providers.php](#file-dd419d607fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration

<a id="file-685ad7ca161b"></a>
## app/Providers/FortifyServiceProvider.php

Application boot integration: service registration, authentication/theme/view/authorization hooks.

Category: Project source/configuration. Size: 3,133 bytes.

Symbol: `App\Providers\FortifyServiceProvider` extends `Illuminate\Support\ServiceProvider`.
Public methods (source line): `register():21`, `boot():29`.

**Uses:**
- [app/Actions/Fortify/CreateNewUser.php](#file-f69b2ee37384) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Actions/Fortify/ResetUserPassword.php](#file-7779cc52d7e8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/auth/confirm-password.blade.php](#file-812fc0679de9) — renders/layout view
- [resources/views/livewire/auth/forgot-password.blade.php](#file-be96505c8974) — renders/layout view
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — renders/layout view
- [resources/views/livewire/auth/register.blade.php](#file-76e556d57fde) — renders/layout view
- [resources/views/livewire/auth/reset-password.blade.php](#file-7c27b12ba036) — renders/layout view
- [resources/views/livewire/auth/two-factor-challenge.blade.php](#file-c15830ca9cca) — renders/layout view
- [resources/views/livewire/auth/verify-email.blade.php](#file-cc7cf4cd43b6) — renders/layout view

**Used by:**
- [bootstrap/providers.php](#file-dd419d607fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration

<a id="file-63fabea05421"></a>
## app/Providers/ThemeServiceProvider.php

Application boot integration: service registration, authentication/theme/view/authorization hooks.

Category: Project source/configuration. Size: 951 bytes.

Symbol: `App\Providers\ThemeServiceProvider` extends `Illuminate\Support\ServiceProvider`.
Public methods (source line): `boot():11`.

**Uses:**
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/lang/en/home.php](#file-ed8819e01b29) — translation resource discovery
- [themes/school-of-signals/lang/si/home.php](#file-956065fd9ae9) — translation resource discovery

**Used by:**
- [bootstrap/providers.php](#file-dd419d607fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration

<a id="file-d416e9fb861c"></a>
## app/Rules/ValidPageCss.php

Laravel custom validation rule; related form/model/service validation සම්බන්ධ වේ.

Category: Project source/configuration. Size: 420 bytes.

Symbol: `App\Rules\ValidPageCss`.
Public methods (source line): `validate():11`.

**Uses:**
- [app/Services/PageCss.php](#file-d33f6597c9ec) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-f6d357e44fba"></a>
## app/Services/AuditLogger.php

Authentication සහ business actions සඳහා audit log records ලියයි.

Category: Project source/configuration. Size: 4,533 bytes.

Symbol: `App\Services\AuditLogger`.
Public methods (source line): `log():38`.

**Uses:**
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContactMessageService.php](#file-6e739a301761) — PHP class/type reference
- [app/Services/DocumentCategoryService.php](#file-e2617061d806) — PHP class/type reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference
- [app/Services/DocumentUploadManager.php](#file-f9738f2f423a) — PHP class/type reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference
- [app/Services/MediaDeletionService.php](#file-beb486d6c9dc) — PHP class/type reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference
- [app/Services/NewsCategoryService.php](#file-c836ba39f3a1) — PHP class/type reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference
- [app/Services/PageDeletionService.php](#file-ebc069215375) — PHP class/type reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference
- [app/Services/RedirectService.php](#file-7ca08192da71) — PHP class/type reference
- [app/Services/SiteSettingService.php](#file-c9d2f82fdd61) — PHP class/type reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/AuditLoggingTest.php](#file-ff3731f0ea22) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-6e739a301761"></a>
## app/Services/ContactMessageService.php

Contact message read/resolve state වෙනස් කර audit කරයි.

Category: Project source/configuration. Size: 1,191 bytes.

Symbol: `App\Services\ContactMessageService`.
Public methods (source line): `markRead():11`, `resolve():23`.
Explicit permission literals: `contacts.manage`.

**Uses:**
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-32886f1db6f6"></a>
## app/Services/ContentSanitizer.php

Shared rich text සහ plain-text values sanitize කරයි.

Category: Project source/configuration. Size: 5,549 bytes.

Symbol: `App\Services\ContentSanitizer`.
Public methods (source line): `__construct():14`, `sanitize():160`, `plainText():202`.

**Uses:**
- `storage/framework/cache/htmlpurifier` — filesystem path reference

**Used by:**
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference
- [app/Support/PageMeta.php](#file-8a4ff76965ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ContentSanitizationTest.php](#file-80f503e1d766) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e2617061d806"></a>
## app/Services/DocumentCategoryService.php

Document category create/update/active state rules.

Category: Project source/configuration. Size: 11,893 bytes.

Symbol: `App\Services\DocumentCategoryService`.
Public methods (source line): `create():14`, `update():106`, `setActive():241`.
Explicit permission literals: `documents.categories.manage`.

**Uses:**
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php](#file-cbb07bdddbcb) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-036a8ff92b3e"></a>
## app/Services/DocumentService.php

Legacy document metadata, versions සහ workflow API; current simplified uploader වෙනම service එකක් භාවිතා කරයි.

Category: Project source/configuration. Size: 40,282 bytes.

Symbol: `App\Services\DocumentService`.
Public methods (source line): `__construct():19`, `create():29`, `update():225`, `addVersion():491`, `restoreVersion():669`, `publish():838`, `archive():993`, `delete():1014`.
Explicit permission literals: `documents.archive`, `documents.create`, `documents.delete`, `documents.publish`, `documents.update`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-f9738f2f423a"></a>
## app/Services/DocumentUploadManager.php

Current bilingual document upload/version selection/save-and-publish flow.

Category: Project source/configuration. Size: 11,465 bytes.

Symbol: `App\Services\DocumentUploadManager`.
Public methods (source line): `save():33`, `delete():211`, `versionNumber():236`, `currentVersion():246`, `applyFileRules():270`.
Explicit permission literals: `documents.delete`, `documents.publish`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-937f20b58173"></a>
## app/Services/GalleryLocale.php

Public gallery/news album URLs සහ language alternatives සකස් කරයි.

Category: Project source/configuration. Size: 2,009 bytes.

Symbol: `App\Services\GalleryLocale`.
Public methods (source line): `indexUrl():9`, `showUrl():14`, `newsUrl():21`, `versions():29`.

**Uses:**
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — fully qualified class reference
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — fully qualified class reference
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — fully qualified class reference
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — fully qualified class reference

<a id="file-2ececd7ff7b6"></a>
## app/Services/GalleryService.php

Gallery metadata, cover/image ordering සහ publication rules.

Category: Project source/configuration. Size: 43,609 bytes.

Symbol: `App\Services\GalleryService`.
Public methods (source line): `__construct():18`, `create():28`, `update():203`, `addImage():441`, `updateImage():584`, `removeImage():700`, `reorderImages():802`, `publish():942`, `unpublish():1169`, `archive():1205`, `delete():1220`.
Explicit permission literals: `galleries.create`, `galleries.delete`, `galleries.publish`, `galleries.update`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5192f65a3932"></a>
## app/Services/LeadershipProfileContent.php

Profile HTML/CSS sanitize කර Node/Tailwind compiler එකෙන් CSS සකස් කර sandbox document එක සාදයි.

Category: Project source/configuration. Size: 4,325 bytes.

Symbol: `App\Services\LeadershipProfileContent`.
Public methods (source line): `__construct():12`, `prepare():18`, `forLeader():69`, `document():79`.

**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference
- [scripts/compile-leadership-profile.mjs](#file-b125b4555dea) — filesystem path reference, literal project path
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — filesystem path reference, literal project path

**Used by:**
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-beb486d6c9dc"></a>
## app/Services/MediaDeletionService.php

Media soft deletion, restore සහ guarded physical removal.

Category: Project source/configuration. Size: 7,958 bytes.

Symbol: `App\Services\MediaDeletionService`.
Public methods (source line): `delete():15`, `restore():58`, `forceDelete():107`.
Explicit permission literals: `media.delete`.

**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-b85a526868fe"></a>
## app/Services/MediaImageVariantService.php

Uploaded images සඳහා derived image variants සහ cleanup.

Category: Project source/configuration. Size: 13,830 bytes.

Symbol: `App\Services\MediaImageVariantService`.
Public methods (source line): `generate():23`, `cleanupPhysicalFiles():389`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-09f6b1bf60d9"></a>
## app/Services/MediaMetadataService.php

Media title/alt/caption වැනි metadata validate/update කරයි.

Category: Project source/configuration. Size: 16,588 bytes.

Symbol: `App\Services\MediaMetadataService`.
Public methods (source line): `__construct():19`, `update():23`.
Explicit permission literals: `media.update`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-1ee54b331819"></a>
## app/Services/MediaReplacementService.php

Existing uploaded asset replace කිරීමේදී permissions, MIME/dimensions, file state සහ variants පාලනය කරයි.

Category: Project source/configuration. Size: 24,696 bytes.

Symbol: `App\Services\MediaReplacementService`.
Public methods (source line): `__construct():22`, `replace():28`.
Config reads: `media.disks.private`, `media.disks.public`.
Explicit permission literals: `media.replace`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — PHP class/type reference
- [config/media.php](#file-708751864f39) — configuration read

**Used by:**
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-1421bdb7a1e5"></a>
## app/Services/MediaUploadSecurity.php

Upload MIME/extension/size/pixel allowlists config එකෙන් resolve කරයි.

Category: Project source/configuration. Size: 5,282 bytes.

Symbol: `App\Services\MediaUploadSecurity`.
Public methods (source line): `uploadsAllowed():9`, `maximumKilobytes():18`, `mimeExtensionMap():34`, `allowedMimeTypes():93`, `allowsMimeType():103`, `allowsExtensionForMimeType():121`, `preferredExtensionForMimeType():149`, `isForbiddenExtension():168`, `maximumImageWidth():212`, `maximumImageHeight():220`, `maximumImagePixels():228`.
Config reads: `media.forbidden_extensions`, `media.uploads.{$type->value}.enabled`, `media.uploads.{$type->value}.max_kb`, `media.uploads.{$type->value}.mime_extensions`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/media.php](#file-708751864f39) — configuration read

**Used by:**
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-efd55e66e6b6"></a>
## app/Services/MediaUploadService.php

UploadedFile validation -> physical storage -> MediaAsset/variants workflow.

Category: Project source/configuration. Size: 19,070 bytes.

Symbol: `App\Services\MediaUploadService`.
Public methods (source line): `__construct():22`, `upload():28`.
Config reads: `media.directory`.
Explicit permission literals: `media.upload`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — PHP class/type reference
- [config/media.php](#file-708751864f39) — configuration read

**Used by:**
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-145ef1119a77"></a>
## app/Services/MediaUrlService.php

Uploaded/external media සඳහා original/thumbnail/medium URLs සහ fallbacks.

Category: Project source/configuration. Size: 3,989 bytes.

Symbol: `App\Services\MediaUrlService`.
Public methods (source line): `original():14`, `variant():40`, `thumbnail():93`, `medium():102`, `thumbnailOrOriginal():111`, `mediumOrOriginal():118`.
Config reads: `media.disks.public`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/media.php](#file-708751864f39) — configuration read

**Used by:**
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — fully qualified class reference
- [resources/views/livewire/admin/galleries/gallery-index.blade.php](#file-c0bd13e9ac3d) — fully qualified class reference
- [resources/views/public/history/past-chief-instructors.blade.php](#file-efe1f99ce875) — PHP class/type reference, fully qualified class reference
- [resources/views/public/history/past-sgt-major.blade.php](#file-83fd2c429c70) — fully qualified class reference
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — fully qualified class reference
- [themes/school-of-signals/views/history/past-commandants.blade.php](#file-16d30421a085) — fully qualified class reference
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — fully qualified class reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — fully qualified class reference
- [themes/school-of-signals/views/leader-profile.blade.php](#file-6843e7ac23ba) — fully qualified class reference
- [themes/school-of-signals/views/partials/commandant-feature.blade.php](#file-e3a71403d4f8) — fully qualified class reference
- [themes/school-of-signals/views/school-leadership.blade.php](#file-81978c1aae5b) — fully qualified class reference

<a id="file-3748da885f11"></a>
## app/Services/MenuService.php

Menu/items/translations CRUD සහ nested ordering; public menus තවම මෙයට අදාළයි.

Category: Project source/configuration. Size: 17,718 bytes.

Symbol: `App\Services\MenuService`.
Public methods (source line): `createMenu():17`, `updateMenu():53`, `addItem():76`, `updateItem():167`, `deleteMenu():272`, `placeBefore():291`, `deleteItem():329`, `moveItem():346`.
Explicit permission literals: `menus.manage`.

**Uses:**
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/MenuTranslationServiceTest.php](#file-49da0724cc08) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-8695936ef235"></a>
## app/Services/NewsArticleService.php

News article content create/update සහ associated validation.

Category: Project source/configuration. Size: 24,769 bytes.

Symbol: `App\Services\NewsArticleService`.
Public methods (source line): `__construct():20`, `create():25`, `update():241`.
Explicit permission literals: `news.create`, `news.update`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c836ba39f3a1"></a>
## app/Services/NewsCategoryService.php

News category CRUD සහ active state.

Category: Project source/configuration. Size: 10,056 bytes.

Symbol: `App\Services\NewsCategoryService`.
Public methods (source line): `create():14`, `update():98`, `setActive():208`.
Explicit permission literals: `news.categories.manage`.

**Uses:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsCategoryServiceTest.php](#file-f0a5049c6b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-23c43b2d93e6"></a>
## app/Services/NewsDeletionService.php

News soft delete/restore සහ related invariants.

Category: Project source/configuration. Size: 2,899 bytes.

Symbol: `App\Services\NewsDeletionService`.
Public methods (source line): `delete():14`, `restore():64`.
Explicit permission literals: `news.delete`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-a5945259a50d"></a>
## app/Services/NewsImageService.php

News image attachments සහ featured image selection.

Category: Project source/configuration. Size: 7,297 bytes.

Symbol: `App\Services\NewsImageService`.
Public methods (source line): `addImage():16`, `removeImage():87`, `setFeaturedImage():139`, `clearFeaturedImage():185`.
Explicit permission literals: `news.update`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-06d5383c2edd"></a>
## app/Services/NewsRevisionService.php

News revision snapshots සහ restoration.

Category: Project source/configuration. Size: 24,492 bytes.

Symbol: `App\Services\NewsRevisionService`.
Public methods (source line): `createSnapshot():21`, `restore():61`.
Explicit permission literals: `news.update`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-498368288f8f"></a>
## app/Services/NewsWorkflowService.php

News review/publish/unpublish transitions; retired archive entry point ද තවම ඇත.

Category: Project source/configuration. Size: 13,693 bytes.

Symbol: `App\Services\NewsWorkflowService`.
Public methods (source line): `__construct():14`, `submit():18`, `requestChanges():100`, `approve():189`, `publish():253`, `publishImmediately():321`, `unpublish():383`, `archive():449`.
Explicit permission literals: `news.approve`, `news.publish`, `news.request-changes`, `news.submit`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e6b5c44d7560"></a>
## app/Services/PageBlockRenderer.php

Legacy structured page blocks safe display data බවට පරිවර්තනය කරයි.

Category: Project source/configuration. Size: 1,499 bytes.

Symbol: `App\Services\PageBlockRenderer`.
Public methods (source line): `__construct():10`, `forPage():26`.

**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference

**Used by:**
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-e7b4ea1426b4"></a>
## app/Services/PageBlockSanitizer.php

Structured page block payload normalize/validate කරයි.

Category: Project source/configuration. Size: 13,516 bytes.

Symbol: `App\Services\PageBlockSanitizer`.
Public methods (source line): `__construct():13`, `normalize():25`.

**Uses:**
- [app/Enums/PageBlockType.php](#file-5ed0972d66b5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageBlockRenderer.php](#file-e6b5c44d7560) — PHP class/type reference
- [tests/Feature/Admin/PageBlockFoundationTest.php](#file-82dd53475672) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-d33f6597c9ec"></a>
## app/Services/PageCss.php

Custom page CSS validate කර page-specific selector scope යටතේ output කරයි.

Category: Project source/configuration. Size: 6,950 bytes.

Symbol: `App\Services\PageCss`.
Public methods (source line): `error():11`, `forPage():23`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Rules/ValidPageCss.php](#file-d416e9fb861c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/components/page/custom-styles.blade.php](#file-8e69b1288783) — fully qualified class reference
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Unit/PageCssTest.php](#file-4412c57413d4) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-ebc069215375"></a>
## app/Services/PageDeletionService.php

Page delete/restore rules සහ related audit handling.

Category: Project source/configuration. Size: 4,055 bytes.

Symbol: `App\Services\PageDeletionService`.
Public methods (source line): `delete():14`, `restore():63`.
Explicit permission literals: `pages.delete`.

**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-33281f363390"></a>
## app/Services/PageHtmlSanitizer.php

CMS HTML සහ visual-editor HTML වෙන වෙනම allowlists අනුව sanitize කරයි.

Category: Project source/configuration. Size: 8,436 bytes.

Symbol: `App\Services\PageHtmlSanitizer`.
Public methods (source line): `__construct():69`, `sanitize():105`, `sanitizeVisual():119`.

**Uses:**
- `storage/framework/cache/htmlpurifier-page` — filesystem path reference

**Used by:**
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5c0b5a534567"></a>
## app/Services/PageRevisionService.php

Page content/metadata/CSS revision capture සහ restore.

Category: Project source/configuration. Size: 19,233 bytes.

Symbol: `App\Services\PageRevisionService`.
Public methods (source line): `capture():19`, `restore():48`.
Explicit permission literals: `pages.revisions.restore`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Support/PageSlugger.php](#file-a75b698dd50e) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Console/Commands/BackfillPageRevisionsCommand.php](#file-fe9122512578) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-3ef41ff28549"></a>
## app/Services/PageWorkflowService.php

Page review/publish/unpublish/return-to-draft transitions.

Category: Project source/configuration. Size: 11,685 bytes.

Symbol: `App\Services\PageWorkflowService`.
Public methods (source line): `submit():14`, `approve():23`, `publish():32`, `publishImmediately():41`, `unpublish():100`, `archive():161`, `returnToDraft():168`.
Explicit permission literals: `pages.publish`.

**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-31231bd51b5f"></a>
## app/Services/PublicGalleryFeed.php

Eligible manual galleries සහ news albums SQL pagination එකක එකතු කරයි.

Category: Project source/configuration. Size: 3,929 bytes.

Symbol: `App\Services\PublicGalleryFeed`.
Public methods (source line): `publicImage():18`, `imageUrl():24`, `galleries():42`, `news():50`, `paginate():59`.

**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — PHP class/type reference
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — fully qualified class reference
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — fully qualified class reference
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — fully qualified class reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7ca08192da71"></a>
## app/Services/RedirectService.php

Redirect destination validation සහ CRUD.

Category: Project source/configuration. Size: 4,223 bytes.

Symbol: `App\Services\RedirectService`.
Public methods (source line): `save():13`, `delete():88`.
Explicit permission literals: `redirects.manage`.

**Uses:**
- [app/Models/Redirect.php](#file-1efb27655fd7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/RedirectManagerTest.php](#file-332bb5013c6b) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c9d2f82fdd61"></a>
## app/Services/SiteSettingService.php

Site settings, localized fields සහ media relationships update කරයි.

Category: Project source/configuration. Size: 1,112 bytes.

Symbol: `App\Services\SiteSettingService`.
Public methods (source line): `update():13`.
Explicit permission literals: `settings.manage`.

**Uses:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-c218aac66c8b"></a>
## app/Services/ThemeAssetPublisher.php

Validated theme assets source -> public/themes copies publish කරයි.

Category: Project source/configuration. Size: 3,470 bytes.

Symbol: `App\Services\ThemeAssetPublisher`.
Public methods (source line): `__construct():31`, `publish():35`.

**Uses:**
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference
- [themes/school-of-signals/assets/css/navbar.css](#file-ad82ea274b07) — theme asset source
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — theme asset source
- [themes/school-of-signals/assets/fonts/Exo2-Regular.woff2](#file-340a7247a290) — theme asset source
- [themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2](#file-5436c94b6541) — theme asset source
- [themes/school-of-signals/assets/fonts/fontawesome-7/css/all.min.css](#file-f8bb44ba029b) — theme asset source
- [themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-brands-400.woff2](#file-56f9e0e187a4) — theme asset source
- [themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-regular-400.woff2](#file-748071dc7688) — theme asset source
- [themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-solid-900.woff2](#file-4db58b2ad5bc) — theme asset source
- [themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-v4compatibility.woff2](#file-a3b93e13dc8d) — theme asset source
- [themes/school-of-signals/assets/images/image-placeholder.svg](#file-14a8c2987a0e) — theme asset source
- [themes/school-of-signals/assets/images/logo.png](#file-5704d9c17561) — theme asset source
- [themes/school-of-signals/assets/js/theme.js](#file-224a28a1e76c) — theme asset source

**Used by:**
- [app/Console/Commands/PublishThemeAssetsCommand.php](#file-9e1f8eeb7239) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-371dbeaa302b"></a>
## app/Services/ThemeLayoutRenderer.php

Published localized header/footer HTML/CSS sanitize කර known placeholders replace කරයි.

Category: Project source/configuration. Size: 17,534 bytes.

Symbol: `App\Services\ThemeLayoutRenderer`.
Public methods (source line): `__construct():19`, `render():31`.
Config reads: `awcms.theme_locales.`.

**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SocialLink.php](#file-217d07b8a20a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — PHP class/type reference
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — fully qualified class reference

<a id="file-bcf26409fe4a"></a>
## app/Services/ThemeLayoutSanitizer.php

DB-managed layout/profile HTML සහ CSS rules sanitize/validate කරයි.

Category: Project source/configuration. Size: 6,734 bytes.

Symbol: `App\Services\ThemeLayoutSanitizer`.
Public methods (source line): `__construct():27`, `sanitizeHtml():31`, `sanitizeCss():59`.

**Uses:**
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference
- [tests/Feature/Admin/ThemeLayoutSanitizerTest.php](#file-dc469ecd3bdf) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-4b0918515100"></a>
## app/Services/ThemeLayoutService.php

Header/footer drafts, publishing සහ revision records.

Category: Project source/configuration. Size: 6,519 bytes.

Symbol: `App\Services\ThemeLayoutService`.
Public methods (source line): `__construct():17`, `saveDraft():23`, `publish():110`.
Explicit permission literals: `theme-layouts.manage`.

**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-57c809432778"></a>
## app/Services/ThemeManager.php

Installed themes, safe slugs සහ theme.json manifests resolve කරයි.

Category: Project source/configuration. Size: 5,315 bytes.

Symbol: `App\Services\ThemeManager`.
Public methods (source line): `all():24`, `find():52`, `viewsPath():63`.

**Uses:**
- `themes` — filesystem path reference

**Used by:**
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Providers/ThemeServiceProvider.php](#file-63fabea05421) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — PHP class/type reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference
- [tests/Feature/ThemeManagerTest.php](#file-c6948e53b7c2) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-5049505de5bb"></a>
## app/Services/ThemeViewResolver.php

Active theme එකේ requested view එක validated filesystem path එකකට resolve කරයි.

Category: Project source/configuration. Size: 1,447 bytes.

Symbol: `App\Services\ThemeViewResolver`.
Public methods (source line): `__construct():7`, `resolve():16`.
Config reads: `awcms.active_theme`.

**Uses:**
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read

**Used by:**
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/ThemeViewResolverTest.php](#file-d1928a9edd00) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-17020ca9e01b"></a>
## app/Support/PageBlockFactory.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 1,979 bytes.

Symbol: `App\Support\PageBlockFactory`.
Public methods (source line): `make():18`.

**Uses:**
- [app/Enums/PageBlockType.php](#file-5ed0972d66b5) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-8a4ff76965ef"></a>
## app/Support/PageMeta.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 732 bytes.

Symbol: `App\Support\PageMeta`.
Public methods (source line): `description():10`.
Config reads: `app.name`.

**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-c916378ce234"></a>
## app/Support/PageSeo.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 5,084 bytes.

Symbol: `App\Support\PageSeo`.
Public methods (source line): `title():11`, `description():29`, `canonicalUrl():71`, `robots():106`, `openGraphTitle():114`, `openGraphDescription():131`, `openGraphImage():148`.
Config reads: `app.name`.

**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-a75b698dd50e"></a>
## app/Support/PageSlugger.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 1,352 bytes.

Symbol: `App\Support\PageSlugger`.
Public methods (source line): `unique():11`.

**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-ab07cd3dce6a"></a>
## app/Support/UserManagementRules.php

Shared helper/validation/action; callers සහ public methods පහත දැක්වේ.

Category: Project source/configuration. Size: 5,230 bytes.

Symbol: `App\Support\UserManagementRules`.
Public methods (source line): `canManageTarget():14`, `assertTargetManageable():34`, `assignableRoleNames():44`, `ensureRoleCanBeAssigned():67`, `ensureRoleChangeAllowed():78`, `ensureStatusChangeAllowed():101`, `ensurePasswordResetAllowed():123`, `lockSuperAdministrators():138`.
Explicit permission literals: `users.assign-role`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — fully qualified class reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — PHP class/type reference, PHP import (declaration), fully qualified class reference

<a id="file-7b7170263018"></a>
## artisan

Console entry point -> Composer autoloader -> bootstrap/app.php.

Category: Project source/configuration. Size: 425 bytes.


**Uses:**
- [bootstrap/app.php](#file-f4e0869689d9) — console bootstrap
- `vendor/autoload.php` — Composer autoloader

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b60194887b46"></a>
## boost.json

Laravel Boost development/AI integration settings.

Category: Project source/configuration. Size: 357 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f4e0869689d9"></a>
## bootstrap/app.php

Framework bootstrap/provider registration file.

Category: Project source/configuration. Size: 1,007 bytes.


**Uses:**
- [app/Http/Middleware/EnsureSiteIsPublic.php](#file-8fd9e4844fce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Http/Middleware/EnsureUserIsActive.php](#file-1e17969532c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [bootstrap/providers.php](#file-dd419d607fba) — provider discovery convention
- [routes/console.php](#file-dd5f3b20dd4f) — console route registration
- [routes/web.php](#file-6b9a783d9b43) — web route registration

**Used by:**
- [artisan](#file-7b7170263018) — console bootstrap
- [public/index.php](#file-0e61f67641b4) — HTTP bootstrap

<a id="file-dd419d607fba"></a>
## bootstrap/providers.php

Framework bootstrap/provider registration file.

Category: Project source/configuration. Size: 239 bytes.


**Uses:**
- [app/Providers/AppServiceProvider.php](#file-48b8d8112e9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration
- [app/Providers/ThemeServiceProvider.php](#file-63fabea05421) — PHP class/type reference, PHP import (declaration), fully qualified class reference, service provider registration

**Used by:**
- [bootstrap/app.php](#file-f4e0869689d9) — provider discovery convention

<a id="file-3957b91a0d84"></a>
## composer.json

PHP requirements, PSR-4 autoload, development tools සහ setup/test/deploy scripts.

Category: Project source/configuration. Size: 3,847 bytes.


**Uses:**
- [composer.lock](#file-c3262e2a0879) — locked PHP dependency versions
- `database/database.sqlite` — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-c3262e2a0879"></a>
## composer.lock

Locked PHP dependency versions; vendor tree reproducibility සඳහා අවශ්‍යයි.

Category: Project source/configuration. Size: 431,927 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [composer.json](#file-3957b91a0d84) — locked PHP dependency versions

<a id="file-734f7be25e80"></a>
## config/app.php

Configuration namespace app.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 4,312 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- [lang/en/pagination.php](#file-b8c6a2355feb) — application translation discovery
- [lang/en/public.php](#file-0cfafa819fc8) — application translation discovery
- [lang/si.json](#file-ed97d9d20f65) — application translation discovery
- [lang/si/pagination.php](#file-aae4e2133f5e) — application translation discovery
- [lang/si/public.php](#file-ca86d39f5f55) — application translation discovery
- [lang/si/validation.php](#file-f13e24ec92c2) — application translation discovery

**Used by:**
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — configuration read
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — configuration read
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — configuration read
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — configuration read
- [app/Support/PageMeta.php](#file-8a4ff76965ef) — configuration read
- [app/Support/PageSeo.php](#file-c916378ce234) — configuration read
- [config/fortify.php](#file-55087024cd3f) — configuration read
- [resources/views/components/app-logo.blade.php](#file-0493bb4f6295) — configuration read
- [resources/views/components/forms/page-seo-fields.blade.php](#file-f8ae6c46ba4a) — configuration read
- [resources/views/layouts/auth/card.blade.php](#file-e746e67ca790) — configuration read
- [resources/views/layouts/auth/simple.blade.php](#file-55bffcaa86b1) — configuration read
- [resources/views/layouts/auth/split.blade.php](#file-a27380f174bb) — configuration read
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — configuration read
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — configuration read
- [resources/views/public/contact/index.blade.php](#file-e2f6ee5f6119) — configuration read
- [resources/views/public/documents/index.blade.php](#file-0dd58f2966d6) — configuration read
- [resources/views/public/documents/show.blade.php](#file-ec5c1df9b13b) — configuration read
- [resources/views/public/documents/upload-index.blade.php](#file-2f43765e807c) — configuration read
- [resources/views/public/documents/upload-show.blade.php](#file-b27df7a59ee5) — configuration read
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — configuration read
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — configuration read
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — configuration read
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — configuration read
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — configuration read
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — configuration read
- [tests/Feature/Admin/ThemeLayoutSanitizerTest.php](#file-dc469ecd3bdf) — configuration read
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — configuration read
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — configuration read
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — configuration read
- [themes/school-of-signals/views/news/index.blade.php](#file-8fdadeebde29) — configuration read
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — configuration read
- [themes/school-of-signals/views/partials/cms-contact.blade.php](#file-17b45718646c) — configuration read

<a id="file-cddba0f39ef2"></a>
## config/auth.php

Configuration namespace auth.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 4,040 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-2eb8ad7c121f"></a>
## config/awcms.php

Configuration namespace awcms.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 1,155 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — configuration read
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — configuration read
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — configuration read
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — configuration read
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — configuration read
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — configuration read
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — configuration read
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — configuration read
- [resources/views/public/contact/index.blade.php](#file-e2f6ee5f6119) — configuration read
- [resources/views/public/documents/index.blade.php](#file-0dd58f2966d6) — configuration read
- [resources/views/public/documents/show.blade.php](#file-ec5c1df9b13b) — configuration read
- [resources/views/public/documents/upload-index.blade.php](#file-2f43765e807c) — configuration read
- [resources/views/public/documents/upload-show.blade.php](#file-b27df7a59ee5) — configuration read
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — configuration read
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — configuration read
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — configuration read
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — configuration read
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — configuration read
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — configuration read
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — configuration read

<a id="file-85b552139c7f"></a>
## config/cache.php

Configuration namespace cache.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 4,369 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `storage/framework/cache/data` — filesystem path reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-69e3bd8ddf95"></a>
## config/database.php

Configuration namespace database.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 6,723 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `database/database.sqlite` — filesystem path reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-0851bcf6cd09"></a>
## config/filesystems.php

Configuration namespace filesystems.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 2,541 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `public/storage` — configured public storage link, filesystem path reference
- `storage/app/private` — filesystem path reference, private disk root
- `storage/app/public` — filesystem path reference, public disk root

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-55087024cd3f"></a>
## config/fortify.php

Configuration namespace fortify.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 6,044 bytes.

Config reads: `app.key`, `app.url`.

**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- [config/app.php](#file-734f7be25e80) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-7d9b42158214"></a>
## config/images.php

Configuration namespace images.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 537 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-2ea25d0e0793"></a>
## config/livewire.php

Configuration namespace livewire.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 11,038 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `resources/views/components` — filesystem path reference
- `resources/views/layouts` — filesystem path reference
- `resources/views/livewire` — filesystem path reference
- `resources/views/pages` — filesystem path reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-e1f0359815a7"></a>
## config/logging.php

Configuration namespace logging.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 4,340 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `storage/logs/laravel.log` — filesystem path reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-2d414a971ebe"></a>
## config/mail.php

Configuration namespace mail.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 3,631 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-708751864f39"></a>
## config/media.php

Configuration namespace media.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 2,877 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — configuration read
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — configuration read
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — configuration read
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — configuration read

<a id="file-29f0acc6d934"></a>
## config/permission.php

Configuration namespace permission.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 7,688 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [database/migrations/2026_07_29_210841_create_permission_tables.php](#file-56b79fe1fae9) — configuration read

<a id="file-eb49dc3fa726"></a>
## config/queue.php

Configuration namespace queue.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 4,199 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-67cde7ebf0c8"></a>
## config/services.php

Configuration namespace services.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 1,039 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup

**Used by:**
- [.cursor/skills/laravel-best-practices/rules/config.md](#file-d8275cf5f57b) — configuration read
- [.cursor/skills/laravel-best-practices/rules/http-client.md](#file-36a8f79cd443) — configuration read
- [.cursor/skills/laravel-best-practices/rules/security.md](#file-9a21b1286265) — configuration read

<a id="file-e1025c27321c"></a>
## config/session.php

Configuration namespace session.*; reads කරන consumers සහ environment bindings බලන්න.

Category: Project source/configuration. Size: 8,424 bytes.


**Uses:**
- [.env](#file-3c84dcdc6bbe) — environment variable lookup
- `storage/framework/sessions` — filesystem path reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-0641c680b942"></a>
## database/.gitignore

Project resource/configuration. Detected references සහ parent folder responsibility බලන්න.

Category: Project source/configuration. Size: 10 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dcf0922ef067"></a>
## database/factories/GalleryFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 1,928 bytes.

Symbol: `Database\Factories\GalleryFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():21`, `published():72`, `archived():83`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration)

<a id="file-3036ddba0583"></a>
## database/factories/GalleryImageFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 1,013 bytes.

Symbol: `Database\Factories\GalleryImageFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():20`.

**Uses:**
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration)

<a id="file-92fa9c81b1bc"></a>
## database/factories/MediaAssetFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 2,758 bytes.

Symbol: `Database\Factories\MediaAssetFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():21`, `document():72`, `externalVideo():97`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration)

<a id="file-fb1b9502fd41"></a>
## database/factories/NewsCategoryFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 1,202 bytes.

Symbol: `Database\Factories\NewsCategoryFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():19`.

**Uses:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration)

<a id="file-1b46348b4e65"></a>
## database/factories/NewsFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 2,578 bytes.

Symbol: `Database\Factories\NewsFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():23`, `published():108`, `featured():119`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration)

<a id="file-9e65735d446c"></a>
## database/factories/NewsRevisionFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 1,279 bytes.

Symbol: `Database\Factories\NewsRevisionFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():21`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — PHP class/type reference, PHP import (declaration)

<a id="file-5f84696da2e3"></a>
## database/factories/PageFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 2,322 bytes.

Symbol: `Database\Factories\PageFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():19`, `submitted():45`, `approved():55`, `published():66`, `archived():78`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration)

<a id="file-3401747fc769"></a>
## database/factories/UserFactory.php

Automated tests/development සඳහා model fixture generation.

Category: Project source/configuration. Size: 2,853 bytes.

Symbol: `Database\Factories\UserFactory` extends `Illuminate\Database\Eloquent\Factories\Factory`.
Public methods (source line): `definition():25`, `unverified():61`, `active():73`, `disabled():85`, `withLastLogin():97`, `withTwoFactor():110`.

**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration)

<a id="file-1697e8e6206b"></a>
## database/migrations/0001_01_01_000000_create_users_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,473 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():43`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/User.php](#file-a90f2e50cb06) — model table schema

<a id="file-da76b36044da"></a>
## database/migrations/0001_01_01_000001_create_cache_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 873 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():30`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f818be19792b"></a>
## database/migrations/0001_01_01_000002_create_jobs_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,883 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():53`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dcb7a88bf3b6"></a>
## database/migrations/2024_01_01_000000_create_passkeys_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 838 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():30`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-997067e5680d"></a>
## database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 953 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():24`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/User.php](#file-a90f2e50cb06) — model table schema

<a id="file-56b79fe1fae9"></a>
## database/migrations/2026_07_29_210841_create_permission_tables.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 6,442 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():12`, `down():125`.
Config reads: `permission.cache.key`, `permission.cache.store`, `permission.column_names`, `permission.table_names`, `permission.teams`, `permission.testing`.

**Uses:**
- [config/permission.php](#file-29f0acc6d934) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-c24eb66899ea"></a>
## database/migrations/2026_07_30_072444_add_management_fields_to_users_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,445 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():39`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/User.php](#file-a90f2e50cb06) — model table schema

<a id="file-931a322d6e8e"></a>
## database/migrations/2026_07_30_174658_create_audit_logs_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,664 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():67`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/AuditLog.php](#file-f0b1464bc270) — model table schema

<a id="file-28325b9ecbcd"></a>
## database/migrations/2026_07_30_203704_create_pages_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,847 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():64`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema

<a id="file-e010058feef2"></a>
## database/migrations/2026_07_31_145549_create_page_revisions_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,075 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():79`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — model table schema

<a id="file-fa467b9249c9"></a>
## database/migrations/2026_08_17_191344_add_seo_fields_to_pages_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,858 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():52`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema

<a id="file-53982f05d42f"></a>
## database/migrations/2026_08_17_192013_add_seo_fields_to_page_revisions_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,876 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():52`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — model table schema

<a id="file-58dd900a54df"></a>
## database/migrations/2026_08_18_140000_create_media_assets_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 4,067 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():11`, `down():151`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — model table schema

<a id="file-e46591bdb95e"></a>
## database/migrations/2026_08_19_091418_create_media_variants_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,675 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():67`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — model table schema

<a id="file-25651cfdaf21"></a>
## database/migrations/2026_08_19_181912_create_news_categories_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,809 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():72`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — model table schema

<a id="file-4a2176338404"></a>
## database/migrations/2026_08_19_181914_create_news_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 5,178 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():198`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/News.php](#file-9584cdf2eac3) — model table schema

<a id="file-e072df78cd8b"></a>
## database/migrations/2026_08_19_204327_create_news_revisions_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,958 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():75`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — model table schema

<a id="file-1ce6c5531f1c"></a>
## database/migrations/2026_08_24_192347_add_review_workflow_fields_to_news_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,614 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():46`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/News.php](#file-9584cdf2eac3) — model table schema

<a id="file-990b340a082e"></a>
## database/migrations/2026_08_24_203114_create_galleries_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 3,840 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():143`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — model table schema

<a id="file-6c96d8f47c44"></a>
## database/migrations/2026_08_24_203115_create_gallery_images_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,118 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():81`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — model table schema

<a id="file-4a5630301f80"></a>
## database/migrations/2026_08_25_163253_create_document_categories_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,207 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():84`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — model table schema

<a id="file-8432a6d239a4"></a>
## database/migrations/2026_08_25_163254_create_documents_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 4,560 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():168`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Document.php](#file-a5395ee5355b) — model table schema

<a id="file-f6faaf6d36ba"></a>
## database/migrations/2026_08_25_163255_create_document_versions_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,411 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():91`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — model table schema

<a id="file-2592bb5fac61"></a>
## database/migrations/2026_08_27_170000_create_menus_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 873 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():24`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Menu.php](#file-72f78bded920) — model table schema

<a id="file-601ba28fb85d"></a>
## database/migrations/2026_08_27_170001_create_menu_items_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,412 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():31`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — model table schema

<a id="file-c054ddab61c7"></a>
## database/migrations/2026_08_27_170002_create_site_settings_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,022 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():39`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — model table schema

<a id="file-71f7a3941b31"></a>
## database/migrations/2026_08_27_170003_create_social_links_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 959 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():25`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SocialLink.php](#file-217d07b8a20a) — model table schema

<a id="file-4752f166062e"></a>
## database/migrations/2026_08_27_170004_create_hero_slides_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,144 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():27`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — model table schema

<a id="file-95554a2eb929"></a>
## database/migrations/2026_08_27_170005_create_contact_messages_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,120 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():29`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/ContactMessage.php](#file-60b85836b768) — model table schema

<a id="file-781628afda80"></a>
## database/migrations/2026_08_27_170006_create_redirects_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,056 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():26`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Redirect.php](#file-1efb27655fd7) — model table schema

<a id="file-248f792f0323"></a>
## database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 912 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():24`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — model table schema

<a id="file-fcc1329ced49"></a>
## database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 932 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():24`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — model table schema

<a id="file-5ff441c1318d"></a>
## database/migrations/2026_09_01_130000_add_localisation_to_pages.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,956 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():11`, `down():79`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema

<a id="file-87db2ff63fe7"></a>
## database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 3,669 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():11`, `down():97`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/News.php](#file-9584cdf2eac3) — model table schema
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — model table schema

<a id="file-d851a19b76a6"></a>
## database/migrations/2026_09_02_143000_create_news_images_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 978 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():38`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/NewsImage.php](#file-865f4356df79) — model table schema

<a id="file-36bc8a17d4b4"></a>
## database/migrations/2026_09_02_160000_create_menu_item_translations_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,821 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():11`, `down():49`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/MenuItemTranslation.php](#file-bb96ee3b18cb) — model table schema

<a id="file-86c73fbb2cd2"></a>
## database/migrations/2026_09_03_090000_create_theme_layouts_tables.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,104 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():46`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/ThemeLayout.php](#file-839afe994d82) — model table schema
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — model table schema

<a id="file-df0f3591f4e4"></a>
## database/migrations/2026_09_03_110000_add_locale_to_theme_layouts_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,915 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():10`, `down():44`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/ThemeLayout.php](#file-839afe994d82) — model table schema

<a id="file-1d4963af7a71"></a>
## database/migrations/2026_09_07_120621_create_hero_slide_translations_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,259 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():40`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — model table schema

<a id="file-1ab05208ef29"></a>
## database/migrations/2026_09_09_112158_create_school_leaders_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,605 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():57`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — model table schema

<a id="file-4c4ff27bd934"></a>
## database/migrations/2026_09_09_130000_create_past_commandants_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,144 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():35`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/PastCommandant.php](#file-2227ba16f07a) — model table schema

<a id="file-60cf77e4e26d"></a>
## database/migrations/2026_09_11_130000_create_past_chief_instructors_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,002 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():25`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/PastChiefInstructor.php](#file-fca0ee8b2525) — model table schema

<a id="file-c741ddca09d7"></a>
## database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 822 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():19`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/News.php](#file-9584cdf2eac3) — model table schema
- [app/Models/NewsRevision.php](#file-e5608ebee6bd) — model table schema

<a id="file-7f9820166d5c"></a>
## database/migrations/2026_09_28_090000_add_sinhala_title_to_galleries_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 531 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():16`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — model table schema

<a id="file-3f6e1f6d25d6"></a>
## database/migrations/2026_09_28_093500_add_sinhala_fields_to_site_settings_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,124 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():23`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — model table schema

<a id="file-88332f27f038"></a>
## database/migrations/2026_09_29_082712_add_sinhala_fields_to_documents_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 654 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():17`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Document.php](#file-a5395ee5355b) — model table schema

<a id="file-e9d81c1238ac"></a>
## database/migrations/2026_09_29_114230_add_current_version_si_to_documents_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 555 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():16`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Document.php](#file-a5395ee5355b) — model table schema

<a id="file-9f99b37e38d5"></a>
## database/migrations/2026_10_01_150056_add_history_fields_to_school_leaders_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,279 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():36`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — model table schema

<a id="file-e6e3a7c6756f"></a>
## database/migrations/2026_10_01_191603_drop_unique_role_key_from_school_leaders_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 627 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():16`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — model table schema

<a id="file-0deec8b68bbd"></a>
## database/migrations/2026_10_02_075947_create_school_leadership_positions_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 2,888 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():10`, `down():87`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — model table schema

<a id="file-c0eac7092403"></a>
## database/migrations/2026_10_02_075948_add_position_id_to_school_leaders_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 1,520 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():10`, `down():45`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — model table schema

<a id="file-3934d68405bd"></a>
## database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 922 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():20`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — model table schema
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-e96e95daa7de"></a>
## database/migrations/2026_10_20_120000_localize_school_footer_content.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 7,265 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():8`, `down():141`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — filesystem path reference

<a id="file-ca9dc51f9fac"></a>
## database/migrations/2026_10_20_130000_match_school_footer_layouts.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 3,295 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():8`, `down():87`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-94773c7c17bf"></a>
## database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php

Schema/data migration. Create/alter/down declarations Database catalog එකේ බලන්න; current live DB state බව නොසලකන්න.

Category: Project source/configuration. Size: 804 bytes.

Symbol: `(anonymous)` extends `Illuminate\Database\Migrations\Migration`.
Public methods (source line): `up():9`, `down():20`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Models/Page.php](#file-b56bbabec185) — model table schema
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — model table schema
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-aca1efccd410"></a>
## database/seeders/DatabaseSeeder.php

Database bootstrap/data/permission seeder. Run කළ විට records වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 703 bytes.

Symbol: `Database\Seeders\DatabaseSeeder` extends `Illuminate\Database\Seeder`.
Public methods (source line): `run():14`.

**Uses:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference
- [database/seeders/SchoolLeadersSeeder.php](#file-26a97948796c) — PHP class/type reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b35802b438dd"></a>
## database/seeders/RetireArchivePermissionsSeeder.php

Database bootstrap/data/permission seeder. Run කළ විට records වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 1,168 bytes.

Symbol: `Database\Seeders\RetireArchivePermissionsSeeder` extends `Illuminate\Database\Seeder`.
Public methods (source line): `run():12`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — PHP class/type reference, PHP import (declaration)

<a id="file-78d32794773e"></a>
## database/seeders/RolesAndPermissionsSeeder.php

Database bootstrap/data/permission seeder. Run කළ විට records වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 16,095 bytes.

Symbol: `Database\Seeders\RolesAndPermissionsSeeder` extends `Illuminate\Database\Seeder`.
Public methods (source line): `run():12`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [database/seeders/DatabaseSeeder.php](#file-aca1efccd410) — PHP class/type reference
- [tests/Feature/Admin/AdminAccessTest.php](#file-d1364c3b48d4) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/AdminLayoutTest.php](#file-61561546aa52) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php](#file-cbb07bdddbcb) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Documents/DocumentCategoryUiTest.php](#file-419e4044300e) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaIndexDeletionUiTest.php](#file-689bc91f5112) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/MenuTranslationServiceTest.php](#file-49da0724cc08) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/NewsCategoryServiceTest.php](#file-f0a5049c6b3e) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/NewsCategoryUiTest.php](#file-7df7d018446c) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/RedirectManagerTest.php](#file-332bb5013c6b) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/RolePermissionManagementTest.php](#file-82b88f2fd69a) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/UserManagementFormTest.php](#file-29370412f779) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/Admin/UserManagementIndexTest.php](#file-e113722c846b) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/DashboardTest.php](#file-7f7cd4403a59) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — PHP class/type reference, PHP import (declaration)

<a id="file-26a97948796c"></a>
## database/seeders/SchoolLeadersSeeder.php

Database bootstrap/data/permission seeder. Run කළ විට records වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 1,473 bytes.

Symbol: `Database\Seeders\SchoolLeadersSeeder` extends `Illuminate\Database\Seeder`.
Public methods (source line): `run():10`.

**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
- [database/seeders/DatabaseSeeder.php](#file-aca1efccd410) — PHP class/type reference

<a id="file-ab3dd097df35"></a>
## database/seeders/SchoolModulePermissionsSeeder.php

Database bootstrap/data/permission seeder. Run කළ විට records වෙනස් කළ හැකියි.

Category: Project source/configuration. Size: 1,942 bytes.

Symbol: `Database\Seeders\SchoolModulePermissionsSeeder` extends `Illuminate\Database\Seeder`.
Public methods (source line): `run():13`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-3f5b65946098"></a>
## deploy/apache/README.md

Deployment/Apache configuration documentation; actual hosting state තහවුරු කළ යුතුයි.

Category: Project source/configuration. Size: 2,818 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-a247004ac093"></a>
## deploy/apache/sch-of-slsc.conf

Deployment/Apache configuration documentation; actual hosting state තහවුරු කළ යුතුයි.

Category: Project source/configuration. Size: 565 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b8c6a2355feb"></a>
## lang/en/pagination.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 87 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-0cfafa819fc8"></a>
## lang/en/public.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 3,346 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-ed97d9d20f65"></a>
## lang/si.json

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 306 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-aae4e2133f5e"></a>
## lang/si/pagination.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 125 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-ca86d39f5f55"></a>
## lang/si/public.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 5,568 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-f13e24ec92c2"></a>
## lang/si/validation.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 1,137 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [config/app.php](#file-734f7be25e80) — application translation discovery

<a id="file-fa288d1472d2"></a>
## package-lock.json

Locked Node dependencies සහ platform-specific optional packages.

Category: Project source/configuration. Size: 66,353 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [package.json](#file-7030d0b2f71b) — locked Node dependency versions

<a id="file-7030d0b2f71b"></a>
## package.json

Node build/runtime dependencies සහ Vite scripts.

Category: Project source/configuration. Size: 669 bytes.


**Uses:**
- [package-lock.json](#file-fa288d1472d2) — locked Node dependency versions
- [vite.config.js](#file-23e488f27597) — frontend build command

**Used by:**
- [resources/css/app.css](#file-fb832949a883) — Node package dependency
- [resources/js/app.js](#file-857628e89e6d) — Node package dependency
- [resources/js/passkeys.js](#file-2b3ec58aec9b) — Node package dependency
- [scripts/compile-leadership-profile.mjs](#file-b125b4555dea) — Node package dependency
- [vite.config.js](#file-23e488f27597) — Node package dependency

<a id="file-9503203c22f7"></a>
## phpstan.neon

Larastan/PHPStan level 7 source paths සහ temporary cache configuration.

Category: Project source/configuration. Size: 281 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-357499abc6fd"></a>
## phpunit.xml

Pest/PHPUnit discovery සහ in-memory SQLite test environment.

Category: Project source/configuration. Size: 1,311 bytes.


**Uses:**
- `tests` — test discovery

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-62bb227d0546"></a>
## pint.json

PHP code formatting configuration.

Category: Project source/configuration. Size: 28 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-c9fe70f275af"></a>
## public/.htaccess

Web-facing entry/configuration/asset file.

Category: Project source/configuration. Size: 740 bytes.


**Uses:**
- [public/index.php](#file-0e61f67641b4) — Apache rewrite target

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b94873aee2cf"></a>
## public/apple-touch-icon.png

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 1,662 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f269902c3789"></a>
## public/crest.png

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 129,251 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — public asset URL

<a id="file-2345d8f1a0b7"></a>
## public/favicon.ico

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 4,286 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-5f2454f9d281"></a>
## public/favicon.svg

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 3,545 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-0e61f67641b4"></a>
## public/index.php

Web-facing entry/configuration/asset file.

Category: Project source/configuration. Size: 543 bytes.


**Uses:**
- [bootstrap/app.php](#file-f4e0869689d9) — HTTP bootstrap
- `vendor/autoload.php` — Composer autoloader

**Used by:**
- [public/.htaccess](#file-c9fe70f275af) — Apache rewrite target
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — filesystem path reference

<a id="file-c3d77d14bbef"></a>
## public/logo.png

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 26,778 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — public asset URL

<a id="file-dac799070c2d"></a>
## public/robots.txt

Web-facing entry/configuration/asset file.

Category: Project source/configuration. Size: 24 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-fb832949a883"></a>
## resources/css/app.css

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 15,292 bytes.


**Uses:**
- [package.json](#file-7030d0b2f71b) — Node package dependency
- [resources/fonts/Exo2-Regular.woff2](#file-215eb8791792) — JS/CSS import or source
- [resources/fonts/NotoSerifSinhala-Regular.woff2](#file-8bcd55ec47b0) — JS/CSS import or source
- [resources/tailwind/page-content-safelist.html](#file-41b0d1e85485) — JS/CSS import or source
- `resources/views` — JS/CSS import or source
- `vendor/livewire/flux/dist/flux.css` — JS/CSS import or source

**Used by:**
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — literal project path
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — literal project path
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — literal project path
- [resources/views/public/maintenance.blade.php](#file-073e6bcc97a1) — literal project path
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — literal project path
- [vite.config.js](#file-23e488f27597) — literal project path

<a id="file-215eb8791792"></a>
## resources/fonts/Exo2-Regular.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 16,720 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/css/app.css](#file-fb832949a883) — JS/CSS import or source

<a id="file-8bcd55ec47b0"></a>
## resources/fonts/NotoSerifSinhala-Regular.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 42,228 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/css/app.css](#file-fb832949a883) — JS/CSS import or source

<a id="file-857628e89e6d"></a>
## resources/js/app.js

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 566 bytes.


**Uses:**
- [package.json](#file-7030d0b2f71b) — Node package dependency
- [resources/js/page-content-editor.js](#file-ae080de30f00) — JS/CSS import or source

**Used by:**
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — literal project path
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — literal project path
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — literal project path
- [resources/views/public/maintenance.blade.php](#file-073e6bcc97a1) — literal project path
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — literal project path
- [vite.config.js](#file-23e488f27597) — literal project path

<a id="file-ae080de30f00"></a>
## resources/js/page-content-editor.js

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 28,806 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/js/app.js](#file-857628e89e6d) — JS/CSS import or source

<a id="file-2b3ec58aec9b"></a>
## resources/js/passkeys.js

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 132 bytes.


**Uses:**
- [package.json](#file-7030d0b2f71b) — Node package dependency

**Used by:**
- [resources/views/components/passkey-registration.blade.php](#file-ebed209eb295) — literal project path
- [resources/views/components/passkey-verify.blade.php](#file-9579140f7b22) — literal project path
- [vite.config.js](#file-23e488f27597) — literal project path

<a id="file-41b0d1e85485"></a>
## resources/tailwind/page-content-safelist.html

Project resource/configuration. Detected references සහ parent folder responsibility බලන්න.

Category: Project source/configuration. Size: 43,413 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/css/app.css](#file-fb832949a883) — JS/CSS import or source

<a id="file-1093a76db08f"></a>
## resources/views/admin/dashboard.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 7,993 bytes.


**Uses:**
- [resources/views/components/admin/page-header.blade.php](#file-792e504b6206) — Blade component
- [resources/views/components/admin/stat-card.blade.php](#file-dbc226e2490c) — Blade component
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — renders/layout view
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-0b76d7cd09a9"></a>
## resources/views/admin/news/preview.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 8,526 bytes.


**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — renders/layout view

<a id="file-4833c5cd01ad"></a>
## resources/views/admin/pages/preview.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,215 bytes.

Config reads: `awcms.active_theme`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — fully qualified class reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/components/page/blocks.blade.php](#file-a53b4baa5c7e) — Blade component
- [resources/views/components/page/custom-styles.blade.php](#file-8e69b1288783) — Blade component

**Used by:**
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — renders/layout view
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-3576db031dcf"></a>
## resources/views/admin/partials/sidebar-navigation.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,586 bytes.


**Uses:**
- [resources/views/components/admin/nav-link.blade.php](#file-ee7ef1c9c47b) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — Blade include
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-204549bd15dd"></a>
## resources/views/components/admin/icon-action.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,959 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/admin/audit-logs/audit-log-index.blade.php](#file-3b0429b36634) — Blade component
- [resources/views/livewire/admin/contact-messages/contact-message-index.blade.php](#file-e518ccdf4bb1) — Blade component
- [resources/views/livewire/admin/documents/document-category-index.blade.php](#file-0dab354351c1) — Blade component
- [resources/views/livewire/admin/documents/document-edit.blade.php](#file-87dc67173ff8) — Blade component
- [resources/views/livewire/admin/documents/document-index.blade.php](#file-250954eab702) — Blade component
- [resources/views/livewire/admin/documents/document-uploads.blade.php](#file-107f8099ad3c) — Blade component
- [resources/views/livewire/admin/galleries/gallery-create.blade.php](#file-79a5e6e972ca) — Blade component
- [resources/views/livewire/admin/galleries/gallery-edit.blade.php](#file-26d9997db568) — Blade component
- [resources/views/livewire/admin/galleries/gallery-index.blade.php](#file-c0bd13e9ac3d) — Blade component
- [resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php](#file-a2efc400ea87) — Blade component
- [resources/views/livewire/admin/media/media-index.blade.php](#file-b9e7c6af277a) — Blade component
- [resources/views/livewire/admin/media/media-upload.blade.php](#file-109c570ea309) — Blade component
- [resources/views/livewire/admin/news/news-category-index.blade.php](#file-5a25b8e17834) — Blade component
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — Blade component
- [resources/views/livewire/admin/news/news-index.blade.php](#file-bc54552a078e) — Blade component
- [resources/views/livewire/admin/news/news-revision-history.blade.php](#file-f522e26b1eac) — Blade component
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — Blade component
- [resources/views/livewire/admin/pages/page-index.blade.php](#file-75131d5829a0) — Blade component
- [resources/views/livewire/admin/pages/page-revision-history.blade.php](#file-2ff2d274429c) — Blade component
- [resources/views/livewire/admin/redirects/redirect-index.blade.php](#file-252bb8a9c7c9) — Blade component
- [resources/views/livewire/admin/school-leaders/school-leader-index.blade.php](#file-5fc638c61e18) — Blade component
- [resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php](#file-b464536172c0) — Blade component
- [resources/views/livewire/admin/school-leaders/school-leadership-position-index.blade.php](#file-e58dbff41fa4) — Blade component
- [resources/views/livewire/admin/settings/site-settings-index.blade.php](#file-d5bf1003d076) — Blade component
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — Blade component
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [scripts/build-2026-10-21-hero-modal-deployment.py](#file-b58be82cd8da) — literal project path
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-ee7ef1c9c47b"></a>
## resources/views/components/admin/nav-link.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 928 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/admin/partials/sidebar-navigation.blade.php](#file-3576db031dcf) — Blade component

<a id="file-792e504b6206"></a>
## resources/views/components/admin/page-header.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 620 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — Blade component
- [resources/views/livewire/admin/audit-logs/audit-log-index.blade.php](#file-3b0429b36634) — Blade component
- [resources/views/livewire/admin/users/user-create.blade.php](#file-c31aaeb421c0) — Blade component
- [resources/views/livewire/admin/users/user-edit.blade.php](#file-44618f864c33) — Blade component
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — Blade component

<a id="file-d6e603ca68be"></a>
## resources/views/components/admin/school-leaders/⚡school-leadership-position-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 13,826 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dbc226e2490c"></a>
## resources/views/components/admin/stat-card.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 526 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — Blade component

<a id="file-c37aea7f5b45"></a>
## resources/views/components/admin/status-badge.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 691 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/admin/users/user-edit.blade.php](#file-44618f864c33) — Blade component
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — Blade component

<a id="file-e5e79d0412f2"></a>
## resources/views/components/admin/users/⚡user-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 150 bytes.

Symbol: `(anonymous)` extends `Livewire\Component`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-ac5bc7a85600"></a>
## resources/views/components/admin/users/⚡user-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 192 bytes.

Symbol: `(anonymous)` extends `Livewire\Component`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f5379bb0cdee"></a>
## resources/views/components/admin/users/⚡user-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 139 bytes.

Symbol: `(anonymous)` extends `Livewire\Component`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-139c30454dc5"></a>
## resources/views/components/app-logo-icon.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 714 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/components/app-logo.blade.php](#file-0493bb4f6295) — Blade component
- [resources/views/layouts/auth/card.blade.php](#file-e746e67ca790) — Blade component
- [resources/views/layouts/auth/simple.blade.php](#file-55bffcaa86b1) — Blade component
- [resources/views/layouts/auth/split.blade.php](#file-a27380f174bb) — Blade component

<a id="file-0493bb4f6295"></a>
## resources/views/components/app-logo.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 758 bytes.

Config reads: `app.name`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/components/app-logo-icon.blade.php](#file-139c30454dc5) — Blade component

**Used by:**
- [resources/views/layouts/app/header.blade.php](#file-9769b02f8b20) — Blade component
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — Blade component

<a id="file-738b0b4d46e9"></a>
## resources/views/components/auth-header.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 213 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/auth/confirm-password.blade.php](#file-812fc0679de9) — Blade component
- [resources/views/livewire/auth/forgot-password.blade.php](#file-be96505c8974) — Blade component
- [resources/views/livewire/auth/register.blade.php](#file-76e556d57fde) — Blade component
- [resources/views/livewire/auth/reset-password.blade.php](#file-7c27b12ba036) — Blade component
- [resources/views/livewire/auth/two-factor-challenge.blade.php](#file-c15830ca9cca) — Blade component

<a id="file-2fd7872ce44d"></a>
## resources/views/components/auth-session-status.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 167 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/auth/confirm-password.blade.php](#file-812fc0679de9) — Blade component
- [resources/views/livewire/auth/forgot-password.blade.php](#file-be96505c8974) — Blade component
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — Blade component
- [resources/views/livewire/auth/register.blade.php](#file-76e556d57fde) — Blade component
- [resources/views/livewire/auth/reset-password.blade.php](#file-7c27b12ba036) — Blade component

<a id="file-22f887ac4e63"></a>
## resources/views/components/desktop-user-menu.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,505 bytes.


**Uses:**
- [routes/settings.php](#file-ef358245ae87) — named route URL/reference

**Used by:**
- [resources/views/layouts/app/header.blade.php](#file-9769b02f8b20) — Blade component
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — Blade component

<a id="file-ca2d829ef8b8"></a>
## resources/views/components/forms/page-builder.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 59,205 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-1d863210fea9"></a>
## resources/views/components/forms/page-content-editor.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 12,835 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/admin/news/news-create.blade.php](#file-0c7497b1a13e) — Blade component
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — Blade component
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — Blade component
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — Blade component

<a id="file-e1a01d8b7ce9"></a>
## resources/views/components/forms/page-custom-css.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,716 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — Blade component
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — Blade component
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-f8ae6c46ba4a"></a>
## resources/views/components/forms/page-seo-fields.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 21,190 bytes.

Config reads: `app.name`, `app.url`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-919177412742"></a>
## resources/views/components/forms/page-visual-editor.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,064 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-1d8ea28a4792"></a>
## resources/views/components/forms/rich-text-editor.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,450 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-531cc956eecf"></a>
## resources/views/components/layouts/admin.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 7,498 bytes.

Config reads: `awcms.full_name`, `awcms.name`, `awcms.version`.

**Uses:**
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path
- [resources/views/admin/partials/sidebar-navigation.blade.php](#file-3576db031dcf) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/AuditLogs/AuditLogIndex.php](#file-536a5e50ec90) — renders/layout view
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — renders/layout view
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — renders/layout view
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — renders/layout view
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — renders/layout view
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — renders/layout view
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — renders/layout view
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — renders/layout view
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — renders/layout view
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — renders/layout view
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — renders/layout view
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — renders/layout view
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — renders/layout view
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — renders/layout view
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — renders/layout view
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — renders/layout view
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — renders/layout view
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — renders/layout view
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — renders/layout view
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — renders/layout view
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — renders/layout view
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — renders/layout view
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — renders/layout view
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — renders/layout view
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — renders/layout view
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — renders/layout view
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — renders/layout view
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — renders/layout view
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — renders/layout view
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — renders/layout view
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — renders/layout view
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — renders/layout view
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — renders/layout view
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — Blade component
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [scripts/build-2026-10-21-hero-modal-deployment.py](#file-b58be82cd8da) — literal project path

<a id="file-a53b4baa5c7e"></a>
## resources/views/components/page/blocks.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 13,996 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — Blade component
- [resources/views/pages/show.blade.php](#file-071059c97417) — Blade component
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — Blade component

<a id="file-8e69b1288783"></a>
## resources/views/components/page/custom-styles.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 333 bytes.


**Uses:**
- [app/Services/PageCss.php](#file-d33f6597c9ec) — fully qualified class reference

**Used by:**
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — Blade component
- [resources/views/pages/show.blade.php](#file-071059c97417) — Blade component
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — Blade component

<a id="file-ebed209eb295"></a>
## resources/views/components/passkey-registration.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,015 bytes.


**Uses:**
- [resources/js/passkeys.js](#file-2b3ec58aec9b) — literal project path

**Used by:**
- [resources/views/livewire/settings/security.blade.php](#file-12046faf6f8a) — Blade component

<a id="file-9579140f7b22"></a>
## resources/views/components/passkey-verify.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,552 bytes.


**Uses:**
- [resources/js/passkeys.js](#file-2b3ec58aec9b) — literal project path

**Used by:**
- [resources/views/livewire/auth/confirm-password.blade.php](#file-812fc0679de9) — Blade component
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — Blade component

<a id="file-470777eb9f0c"></a>
## resources/views/components/placeholder-pattern.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 386 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/dashboard.blade.php](#file-cb9b83605560) — Blade component

<a id="file-b319e2b177d3"></a>
## resources/views/components/settings/layout.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 860 bytes.


**Uses:**
- [routes/settings.php](#file-ef358245ae87) — named route URL/reference

**Used by:**
- [resources/views/livewire/settings/appearance.blade.php](#file-1edac84c451e) — Blade component
- [resources/views/livewire/settings/profile.blade.php](#file-70890f0fb0f2) — Blade component
- [resources/views/livewire/settings/security.blade.php](#file-12046faf6f8a) — Blade component

<a id="file-cb9b83605560"></a>
## resources/views/dashboard.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,275 bytes.


**Uses:**
- [resources/views/components/placeholder-pattern.blade.php](#file-470777eb9f0c) — Blade component
- [resources/views/layouts/app.blade.php](#file-4e52e2a63bef) — Blade component

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-7b9d15d0de0b"></a>
## resources/views/flux/icon/book-open-text.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,201 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-de9b91b7a721"></a>
## resources/views/flux/icon/chevrons-up-down.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 988 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dc4dea81b06f"></a>
## resources/views/flux/icon/folder-git-2.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,164 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-8b6f648df14f"></a>
## resources/views/flux/icon/layout-grid.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,143 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-a7e0e6cb02b9"></a>
## resources/views/flux/navlist/group.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,482 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-4e52e2a63bef"></a>
## resources/views/layouts/app.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 128 bytes.


**Uses:**
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — Blade component

**Used by:**
- [resources/views/dashboard.blade.php](#file-cb9b83605560) — Blade component

<a id="file-9769b02f8b20"></a>
## resources/views/layouts/app/header.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,723 bytes.


**Uses:**
- [resources/views/components/app-logo.blade.php](#file-0493bb4f6295) — Blade component
- [resources/views/components/desktop-user-menu.blade.php](#file-22f887ac4e63) — Blade component
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-80c048e547f7"></a>
## resources/views/layouts/app/sidebar.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,107 bytes.


**Uses:**
- [resources/views/components/app-logo.blade.php](#file-0493bb4f6295) — Blade component
- [resources/views/components/desktop-user-menu.blade.php](#file-22f887ac4e63) — Blade component
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include
- [routes/settings.php](#file-ef358245ae87) — named route URL/reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [resources/views/layouts/app.blade.php](#file-4e52e2a63bef) — Blade component

<a id="file-66f263c8003a"></a>
## resources/views/layouts/auth.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 91 bytes.


**Uses:**
- [resources/views/layouts/auth/simple.blade.php](#file-55bffcaa86b1) — Blade component

**Used by:**
- [resources/views/livewire/auth/confirm-password.blade.php](#file-812fc0679de9) — Blade component
- [resources/views/livewire/auth/forgot-password.blade.php](#file-be96505c8974) — Blade component
- [resources/views/livewire/auth/register.blade.php](#file-76e556d57fde) — Blade component
- [resources/views/livewire/auth/reset-password.blade.php](#file-7c27b12ba036) — Blade component
- [resources/views/livewire/auth/two-factor-challenge.blade.php](#file-c15830ca9cca) — Blade component
- [resources/views/livewire/auth/verify-email.blade.php](#file-cc7cf4cd43b6) — Blade component

<a id="file-e746e67ca790"></a>
## resources/views/layouts/auth/card.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,377 bytes.

Config reads: `app.name`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/components/app-logo-icon.blade.php](#file-139c30454dc5) — Blade component
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b31395193c55"></a>
## resources/views/layouts/auth/login.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,599 bytes.


**Uses:**
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include

**Used by:**
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — Blade component

<a id="file-55bffcaa86b1"></a>
## resources/views/layouts/auth/simple.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,192 bytes.

Config reads: `app.name`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/components/app-logo-icon.blade.php](#file-139c30454dc5) — Blade component
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

<a id="file-a27380f174bb"></a>
## resources/views/layouts/auth/split.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,400 bytes.

Config reads: `app.name`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/components/app-logo-icon.blade.php](#file-139c30454dc5) — Blade component
- [resources/views/partials/head.blade.php](#file-81d1ac73c0a4) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dbfeba3a222f"></a>
## resources/views/layouts/public.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 9,259 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Services/GalleryLocale.php](#file-937f20b58173) — fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [resources/views/pages/show.blade.php](#file-071059c97417) — Blade extends
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — Blade extends
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-3b0429b36634"></a>
## resources/views/livewire/admin/audit-logs/audit-log-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 11,780 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [resources/views/components/admin/page-header.blade.php](#file-792e504b6206) — Blade component

**Used by:**
- [app/Livewire/Admin/AuditLogs/AuditLogIndex.php](#file-536a5e50ec90) — renders/layout view

<a id="file-e518ccdf4bb1"></a>
## resources/views/livewire/admin/contact-messages/contact-message-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 3,723 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — renders/layout view

<a id="file-0dab354351c1"></a>
## resources/views/livewire/admin/documents/document-category-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 24,433 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — renders/layout view

<a id="file-cf8447dd5e55"></a>
## resources/views/livewire/admin/documents/document-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 22,747 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — renders/layout view

<a id="file-87dc67173ff8"></a>
## resources/views/livewire/admin/documents/document-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 55,700 bytes.


**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — renders/layout view

<a id="file-250954eab702"></a>
## resources/views/livewire/admin/documents/document-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 31,788 bytes.


**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — renders/layout view

<a id="file-107f8099ad3c"></a>
## resources/views/livewire/admin/documents/document-uploads.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 28,927 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-79a5e6e972ca"></a>
## resources/views/livewire/admin/galleries/gallery-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 5,126 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — renders/layout view

<a id="file-26d9997db568"></a>
## resources/views/livewire/admin/galleries/gallery-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 7,312 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — renders/layout view

<a id="file-c0bd13e9ac3d"></a>
## resources/views/livewire/admin/galleries/gallery-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 4,579 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — renders/layout view

<a id="file-a2efc400ea87"></a>
## resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 34,570 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — renders/layout view
- [scripts/build-2026-10-21-hero-modal-deployment.py](#file-b58be82cd8da) — literal project path

<a id="file-8306e12be497"></a>
## resources/views/livewire/admin/media/media-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 30,236 bytes.


**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — renders/layout view

<a id="file-b9e7c6af277a"></a>
## resources/views/livewire/admin/media/media-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 29,981 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-109c570ea309"></a>
## resources/views/livewire/admin/media/media-upload.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 29,746 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — renders/layout view

<a id="file-5a25b8e17834"></a>
## resources/views/livewire/admin/news/news-category-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 22,629 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — renders/layout view

<a id="file-0c7497b1a13e"></a>
## resources/views/livewire/admin/news/news-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 42,244 bytes.


**Uses:**
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — fully qualified class reference
- [resources/views/components/forms/page-content-editor.blade.php](#file-1d863210fea9) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — renders/layout view

<a id="file-44a2edd5644e"></a>
## resources/views/livewire/admin/news/news-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 44,628 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [resources/views/components/forms/page-content-editor.blade.php](#file-1d863210fea9) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — renders/layout view

<a id="file-bc54552a078e"></a>
## resources/views/livewire/admin/news/news-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 33,797 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — renders/layout view

<a id="file-f522e26b1eac"></a>
## resources/views/livewire/admin/news/news-revision-history.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 39,870 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — renders/layout view

<a id="file-09fe2888a3bc"></a>
## resources/views/livewire/admin/pages/page-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 11,979 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — fully qualified class reference
- [resources/views/components/forms/page-content-editor.blade.php](#file-1d863210fea9) — Blade component
- [resources/views/components/forms/page-custom-css.blade.php](#file-e1a01d8b7ce9) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — renders/layout view
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-8d2f8249dcd2"></a>
## resources/views/livewire/admin/pages/page-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 14,698 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [resources/views/components/forms/page-content-editor.blade.php](#file-1d863210fea9) — Blade component
- [resources/views/components/forms/page-custom-css.blade.php](#file-e1a01d8b7ce9) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — renders/layout view
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-75131d5829a0"></a>
## resources/views/livewire/admin/pages/page-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 21,893 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — renders/layout view

<a id="file-2ff2d274429c"></a>
## resources/views/livewire/admin/pages/page-revision-history.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 22,268 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — renders/layout view
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-252bb8a9c7c9"></a>
## resources/views/livewire/admin/redirects/redirect-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 3,556 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — renders/layout view

<a id="file-8529b03919da"></a>
## resources/views/livewire/admin/roles/role-permission-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 19,850 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — renders/layout view

<a id="file-5fc638c61e18"></a>
## resources/views/livewire/admin/school-leaders/school-leader-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 42,952 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b464536172c0"></a>
## resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 9,719 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — renders/layout view
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-e58dbff41fa4"></a>
## resources/views/livewire/admin/school-leaders/school-leadership-position-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 29,086 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-d5bf1003d076"></a>
## resources/views/livewire/admin/settings/site-settings-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 14,863 bytes.


**Uses:**
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component

**Used by:**
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — renders/layout view

<a id="file-a1e9a891a029"></a>
## resources/views/livewire/admin/theme-layouts/theme-layout-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 13,979 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — renders/layout view

<a id="file-8c27355c3e23"></a>
## resources/views/livewire/admin/users/audit-logs/audit-log-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 456 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-c31aaeb421c0"></a>
## resources/views/livewire/admin/users/user-create.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 9,126 bytes.


**Uses:**
- [resources/views/components/admin/page-header.blade.php](#file-792e504b6206) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — renders/layout view

<a id="file-44618f864c33"></a>
## resources/views/livewire/admin/users/user-edit.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 14,809 bytes.


**Uses:**
- [resources/views/components/admin/page-header.blade.php](#file-792e504b6206) — Blade component
- [resources/views/components/admin/status-badge.blade.php](#file-c37aea7f5b45) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — renders/layout view

<a id="file-6c1a999dd914"></a>
## resources/views/livewire/admin/users/user-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 19,323 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — fully qualified class reference
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — Blade component
- [resources/views/components/admin/page-header.blade.php](#file-792e504b6206) — Blade component
- [resources/views/components/admin/status-badge.blade.php](#file-c37aea7f5b45) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — renders/layout view

<a id="file-812fc0679de9"></a>
## resources/views/livewire/auth/confirm-password.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,288 bytes.


**Uses:**
- [resources/views/components/auth-header.blade.php](#file-738b0b4d46e9) — Blade component
- [resources/views/components/auth-session-status.blade.php](#file-2fd7872ce44d) — Blade component
- [resources/views/components/passkey-verify.blade.php](#file-9579140f7b22) — Blade component
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-be96505c8974"></a>
## resources/views/livewire/auth/forgot-password.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,212 bytes.


**Uses:**
- [resources/views/components/auth-header.blade.php](#file-738b0b4d46e9) — Blade component
- [resources/views/components/auth-session-status.blade.php](#file-2fd7872ce44d) — Blade component
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-6e2b25a4607e"></a>
## resources/views/livewire/auth/login.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 5,100 bytes.

Config reads: `awcms.name`.

**Uses:**
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [public/crest.png](#file-f269902c3789) — public asset URL
- [resources/views/components/auth-session-status.blade.php](#file-2fd7872ce44d) — Blade component
- [resources/views/components/passkey-verify.blade.php](#file-9579140f7b22) — Blade component
- [resources/views/layouts/auth/login.blade.php](#file-b31395193c55) — Blade component
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-76e556d57fde"></a>
## resources/views/livewire/auth/register.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,567 bytes.


**Uses:**
- [resources/views/components/auth-header.blade.php](#file-738b0b4d46e9) — Blade component
- [resources/views/components/auth-session-status.blade.php](#file-2fd7872ce44d) — Blade component
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-7c27b12ba036"></a>
## resources/views/livewire/auth/reset-password.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,026 bytes.


**Uses:**
- [resources/views/components/auth-header.blade.php](#file-738b0b4d46e9) — Blade component
- [resources/views/components/auth-session-status.blade.php](#file-2fd7872ce44d) — Blade component
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-c15830ca9cca"></a>
## resources/views/livewire/auth/two-factor-challenge.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,037 bytes.


**Uses:**
- [resources/views/components/auth-header.blade.php](#file-738b0b4d46e9) — Blade component
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-cc7cf4cd43b6"></a>
## resources/views/livewire/auth/verify-email.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,275 bytes.


**Uses:**
- [resources/views/layouts/auth.blade.php](#file-66f263c8003a) — Blade component

**Used by:**
- [app/Providers/FortifyServiceProvider.php](#file-685ad7ca161b) — renders/layout view

<a id="file-1edac84c451e"></a>
## resources/views/livewire/settings/appearance.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 670 bytes.


**Uses:**
- [resources/views/components/settings/layout.blade.php](#file-b319e2b177d3) — Blade component
- [resources/views/partials/settings-heading.blade.php](#file-1626408e2fcb) — Blade include

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f9c3b93233bf"></a>
## resources/views/livewire/settings/delete-user-form.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 1,602 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-70890f0fb0f2"></a>
## resources/views/livewire/settings/profile.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 1,461 bytes.


**Uses:**
- [app/Livewire/Settings/DeleteUserForm.php](#file-41247b44803c) — embedded Livewire component
- [resources/views/components/settings/layout.blade.php](#file-b319e2b177d3) — Blade component
- [resources/views/partials/settings-heading.blade.php](#file-1626408e2fcb) — Blade include

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-12046faf6f8a"></a>
## resources/views/livewire/settings/security.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 17,054 bytes.


**Uses:**
- [app/Livewire/Settings/TwoFactor/RecoveryCodes.php](#file-de88561c9579) — embedded Livewire component
- [resources/views/components/passkey-registration.blade.php](#file-ebed209eb295) — Blade component
- [resources/views/components/settings/layout.blade.php](#file-b319e2b177d3) — Blade component
- [resources/views/partials/settings-heading.blade.php](#file-1626408e2fcb) — Blade include

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-9187b0ad5fe7"></a>
## resources/views/livewire/settings/two-factor/recovery-codes.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ; browser events server component methods වෙත යවයි.

Category: Project source/configuration. Size: 3,425 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-ba500a33e57f"></a>
## resources/views/pages/settings/security.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 790 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-071059c97417"></a>
## resources/views/pages/show.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,656 bytes.


**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — fully qualified class reference
- [resources/views/components/page/blocks.blade.php](#file-a53b4baa5c7e) — Blade component
- [resources/views/components/page/custom-styles.blade.php](#file-8e69b1288783) — Blade component
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — Blade extends

**Used by:**
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — renders/layout view
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path

<a id="file-81d1ac73c0a4"></a>
## resources/views/partials/head.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 476 bytes.

Config reads: `app.name`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path

**Used by:**
- [resources/views/layouts/app/header.blade.php](#file-9769b02f8b20) — Blade include
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — Blade include
- [resources/views/layouts/auth/card.blade.php](#file-e746e67ca790) — Blade include
- [resources/views/layouts/auth/login.blade.php](#file-b31395193c55) — Blade include
- [resources/views/layouts/auth/simple.blade.php](#file-55bffcaa86b1) — Blade include
- [resources/views/layouts/auth/split.blade.php](#file-a27380f174bb) — Blade include

<a id="file-1626408e2fcb"></a>
## resources/views/partials/settings-heading.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 271 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/livewire/settings/appearance.blade.php](#file-1edac84c451e) — Blade include
- [resources/views/livewire/settings/profile.blade.php](#file-70890f0fb0f2) — Blade include
- [resources/views/livewire/settings/security.blade.php](#file-12046faf6f8a) — Blade include

<a id="file-e2f6ee5f6119"></a>
## resources/views/public/contact/index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,667 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — renders/layout view

<a id="file-0dd58f2966d6"></a>
## resources/views/public/documents/index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 8,023 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/documents/styles.blade.php](#file-9eaa0f4b3ee0) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-ec5c1df9b13b"></a>
## resources/views/public/documents/show.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 8,016 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/documents/styles.blade.php](#file-9eaa0f4b3ee0) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-9eaa0f4b3ee0"></a>
## resources/views/public/documents/styles.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 6,796 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/public/documents/index.blade.php](#file-0dd58f2966d6) — Blade include
- [resources/views/public/documents/show.blade.php](#file-ec5c1df9b13b) — Blade include

<a id="file-2f43765e807c"></a>
## resources/views/public/documents/upload-index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,030 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/documents/upload-styles.blade.php](#file-e30fb3eb8245) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — renders/layout view

<a id="file-b27df7a59ee5"></a>
## resources/views/public/documents/upload-show.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,942 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/documents/upload-styles.blade.php](#file-e30fb3eb8245) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — renders/layout view

<a id="file-e30fb3eb8245"></a>
## resources/views/public/documents/upload-styles.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,573 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/public/documents/upload-index.blade.php](#file-2f43765e807c) — Blade include
- [resources/views/public/documents/upload-show.blade.php](#file-b27df7a59ee5) — Blade include

<a id="file-ab2efd2f40d8"></a>
## resources/views/public/galleries/index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 6,970 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/galleries/styles.blade.php](#file-52d4023dc51a) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — renders/layout view
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-b951f59d092d"></a>
## resources/views/public/galleries/news.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,360 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [app/Services/GalleryLocale.php](#file-937f20b58173) — fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/galleries/styles.blade.php](#file-52d4023dc51a) — Blade include
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — renders/layout view

<a id="file-db6a508aa6d0"></a>
## resources/views/public/galleries/pagination.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,904 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-f26666e18a6a"></a>
## resources/views/public/galleries/show.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,045 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [app/Services/GalleryLocale.php](#file-937f20b58173) — fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/public/galleries/styles.blade.php](#file-52d4023dc51a) — Blade include

**Used by:**
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — renders/layout view

<a id="file-52d4023dc51a"></a>
## resources/views/public/galleries/styles.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 7,029 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — Blade include
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — Blade include
- [resources/views/public/galleries/show.blade.php](#file-f26666e18a6a) — Blade include
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-efe1f99ce875"></a>
## resources/views/public/history/past-chief-instructors.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 9,701 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — PHP class/type reference, fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — renders/layout view
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-83fd2c429c70"></a>
## resources/views/public/history/past-sgt-major.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 5,951 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — renders/layout view
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-6dd8814bb020"></a>
## resources/views/public/home.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 6,090 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — Blade extends
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — renders/layout view
- [scripts/build-2026-10-21-hero-link-deployment.py](#file-cff8ad197a2e) — literal project path

<a id="file-073e6bcc97a1"></a>
## resources/views/public/maintenance.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 1,004 bytes.


**Uses:**
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path

**Used by:**
- [app/Http/Middleware/EnsureSiteIsPublic.php](#file-8fd9e4844fce) — renders/layout view

<a id="file-bf06929a3544"></a>
## resources/views/public/news/index.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 7,094 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-015b5fcbf407"></a>
## resources/views/public/news/show.blade.php

Core Blade view/component; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 7,258 bytes.

Config reads: `app.name`, `awcms.active_theme`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-dd5f3b20dd4f"></a>
## routes/console.php

Route registration entry; exact URI/name/handler/middleware Routes tab එකේ ඇත.

Category: Project source/configuration. Size: 210 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [bootstrap/app.php](#file-f4e0869689d9) — console route registration

<a id="file-ef358245ae87"></a>
## routes/settings.php

Route registration entry; exact URI/name/handler/middleware Routes tab එකේ ඇත.

Category: Project source/configuration. Size: 903 bytes.

Registered URLs: `.well-known/passkey-endpoints` (well-known.passkeys).

**Uses:**
- [app/Livewire/Settings/Appearance.php](#file-082cda167128) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Settings/Profile.php](#file-8000c38dd596) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Settings/Security.php](#file-2ba603ddc070) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- `vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php` — registered route handler

**Used by:**
- [resources/views/components/desktop-user-menu.blade.php](#file-22f887ac4e63) — named route URL/reference
- [resources/views/components/settings/layout.blade.php](#file-b319e2b177d3) — named route URL/reference
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — named route URL/reference
- [routes/web.php](#file-6b9a783d9b43) — required route file
- [tests/Feature/Settings/SecurityTest.php](#file-4a00d81e65e8) — named route URL/reference

<a id="file-6b9a783d9b43"></a>
## routes/web.php

Route registration entry; exact URI/name/handler/middleware Routes tab එකේ ඇත.

Category: Project source/configuration. Size: 27,334 bytes.

Registered URLs: `dashboard` (dashboard).

**Uses:**
- [app/Http/Controllers/Admin/DashboardController.php](#file-b42815b416ad) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/Admin/NewsPreviewController.php](#file-451877cfc6da) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicGalleryController.php](#file-5a7c921b82e7) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicRedirectController.php](#file-4c0a934c620d) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Admin/AuditLogs/AuditLogIndex.php](#file-536a5e50ec90) — registered route handler
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — registered route handler
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — registered route handler
- [app/Livewire/Admin/Documents/DocumentUploads.php](#file-63c6dcb10c0e) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — registered route handler
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — registered route handler
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — registered route handler
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — registered route handler
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — registered route handler
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — registered route handler
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — registered route handler
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — registered route handler
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — registered route handler
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — registered route handler
- [app/Livewire/Admin/News/NewsRevisionHistory.php](#file-c9270ab1eb3b) — registered route handler
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — registered route handler
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — registered route handler
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — registered route handler
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — registered route handler
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — registered route handler
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — registered route handler
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference, registered route handler
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — registered route handler
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — registered route handler
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — registered route handler
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — registered route handler
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — registered route handler
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — registered route handler
- [routes/settings.php](#file-ef358245ae87) — required route file

**Used by:**
- [app/Http/Controllers/PastChiefInstructorController.php](#file-9d37b14e3dd9) — named route URL/reference
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — named route URL/reference
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — named route URL/reference
- [app/Http/Controllers/PublicContactController.php](#file-c20f494d48ea) — named route URL/reference
- [app/Http/Controllers/PublicDocumentController.php](#file-56250b59a420) — named route URL/reference
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — named route URL/reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — named route URL/reference
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — named route URL/reference
- [app/Livewire/Settings/Profile.php](#file-8000c38dd596) — named route URL/reference
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — named route URL/reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — named route URL/reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — named route URL/reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — named route URL/reference
- [app/Support/PageSeo.php](#file-c916378ce234) — named route URL/reference
- [bootstrap/app.php](#file-f4e0869689d9) — web route registration
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — named route URL/reference
- [resources/views/admin/news/preview.blade.php](#file-0b76d7cd09a9) — named route URL/reference
- [resources/views/admin/partials/sidebar-navigation.blade.php](#file-3576db031dcf) — named route URL/reference
- [resources/views/components/admin/school-leaders/⚡school-leadership-position-index.blade.php](#file-d6e603ca68be) — named route URL/reference
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — named route URL/reference
- [resources/views/layouts/app/header.blade.php](#file-9769b02f8b20) — named route URL/reference
- [resources/views/layouts/app/sidebar.blade.php](#file-80c048e547f7) — named route URL/reference
- [resources/views/layouts/auth/card.blade.php](#file-e746e67ca790) — named route URL/reference
- [resources/views/layouts/auth/simple.blade.php](#file-55bffcaa86b1) — named route URL/reference
- [resources/views/layouts/auth/split.blade.php](#file-a27380f174bb) — named route URL/reference
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — named route URL/reference
- [resources/views/livewire/admin/documents/document-category-index.blade.php](#file-0dab354351c1) — named route URL/reference
- [resources/views/livewire/admin/documents/document-create.blade.php](#file-cf8447dd5e55) — named route URL/reference
- [resources/views/livewire/admin/documents/document-edit.blade.php](#file-87dc67173ff8) — named route URL/reference
- [resources/views/livewire/admin/documents/document-index.blade.php](#file-250954eab702) — named route URL/reference
- [resources/views/livewire/admin/documents/document-uploads.blade.php](#file-107f8099ad3c) — named route URL/reference
- [resources/views/livewire/admin/galleries/gallery-create.blade.php](#file-79a5e6e972ca) — named route URL/reference
- [resources/views/livewire/admin/galleries/gallery-edit.blade.php](#file-26d9997db568) — named route URL/reference
- [resources/views/livewire/admin/galleries/gallery-index.blade.php](#file-c0bd13e9ac3d) — named route URL/reference
- [resources/views/livewire/admin/media/media-edit.blade.php](#file-8306e12be497) — named route URL/reference
- [resources/views/livewire/admin/media/media-index.blade.php](#file-b9e7c6af277a) — named route URL/reference
- [resources/views/livewire/admin/news/news-create.blade.php](#file-0c7497b1a13e) — named route URL/reference
- [resources/views/livewire/admin/news/news-edit.blade.php](#file-44a2edd5644e) — named route URL/reference
- [resources/views/livewire/admin/news/news-index.blade.php](#file-bc54552a078e) — named route URL/reference
- [resources/views/livewire/admin/news/news-revision-history.blade.php](#file-f522e26b1eac) — named route URL/reference
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — named route URL/reference
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — named route URL/reference
- [resources/views/livewire/admin/pages/page-index.blade.php](#file-75131d5829a0) — named route URL/reference
- [resources/views/livewire/admin/pages/page-revision-history.blade.php](#file-2ff2d274429c) — named route URL/reference
- [resources/views/livewire/admin/school-leaders/school-leader-index.blade.php](#file-5fc638c61e18) — named route URL/reference
- [resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php](#file-b464536172c0) — named route URL/reference
- [resources/views/livewire/admin/school-leaders/school-leadership-position-index.blade.php](#file-e58dbff41fa4) — named route URL/reference
- [resources/views/livewire/admin/users/user-create.blade.php](#file-c31aaeb421c0) — named route URL/reference
- [resources/views/livewire/admin/users/user-edit.blade.php](#file-44618f864c33) — named route URL/reference
- [resources/views/livewire/admin/users/user-index.blade.php](#file-6c1a999dd914) — named route URL/reference
- [resources/views/livewire/auth/login.blade.php](#file-6e2b25a4607e) — named route URL/reference
- [resources/views/public/contact/index.blade.php](#file-e2f6ee5f6119) — named route URL/reference
- [resources/views/public/documents/index.blade.php](#file-0dd58f2966d6) — named route URL/reference
- [resources/views/public/documents/show.blade.php](#file-ec5c1df9b13b) — named route URL/reference
- [resources/views/public/documents/upload-index.blade.php](#file-2f43765e807c) — named route URL/reference
- [resources/views/public/documents/upload-show.blade.php](#file-b27df7a59ee5) — named route URL/reference
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — named route URL/reference
- [resources/views/public/galleries/news.blade.php](#file-b951f59d092d) — named route URL/reference
- [resources/views/public/history/past-chief-instructors.blade.php](#file-efe1f99ce875) — named route URL/reference
- [resources/views/public/history/past-sgt-major.blade.php](#file-83fd2c429c70) — named route URL/reference
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — named route URL/reference
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — named route URL/reference
- [resources/views/public/news/show.blade.php](#file-015b5fcbf407) — named route URL/reference
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path
- [tests/Feature/Admin/AdminLayoutTest.php](#file-61561546aa52) — named route URL/reference
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — named route URL/reference
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — named route URL/reference
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — named route URL/reference
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — named route URL/reference
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — named route URL/reference
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — named route URL/reference
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — named route URL/reference
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — named route URL/reference
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — named route URL/reference
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — named route URL/reference
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — named route URL/reference
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — named route URL/reference
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — named route URL/reference
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — named route URL/reference
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — named route URL/reference
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — named route URL/reference
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — named route URL/reference
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — named route URL/reference
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — named route URL/reference
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — named route URL/reference
- [tests/Feature/Admin/UserManagementIndexTest.php](#file-e113722c846b) — named route URL/reference
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — named route URL/reference
- [tests/Feature/Auth/AuthenticationTest.php](#file-ad5adf97ee88) — named route URL/reference
- [tests/Feature/Auth/EmailVerificationTest.php](#file-a975c7792e9d) — named route URL/reference
- [tests/Feature/Auth/RegistrationTest.php](#file-cf1819f2d9b4) — named route URL/reference
- [tests/Feature/DashboardTest.php](#file-7f7cd4403a59) — named route URL/reference
- [tests/Feature/ExampleTest.php](#file-163dc4f47194) — named route URL/reference
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — named route URL/reference
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — named route URL/reference
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — named route URL/reference
- [tests/Feature/NewsMultipleImagesTest.php](#file-90cd0d72347c) — named route URL/reference
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — named route URL/reference
- [tests/Feature/PageCustomCssRenderingTest.php](#file-a233a15c12f7) — named route URL/reference
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — named route URL/reference
- [tests/Feature/PastSergeantMajorTest.php](#file-6ae4dc4b1756) — named route URL/reference
- [tests/Feature/PublicActiveThemeTest.php](#file-78c165114ece) — named route URL/reference
- [tests/Feature/PublicContactTest.php](#file-b191f82c0ffd) — named route URL/reference
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — named route URL/reference
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — named route URL/reference
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — named route URL/reference
- [tests/Feature/PublicNewsLanguageTest.php](#file-0576177ff099) — named route URL/reference
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — named route URL/reference
- [tests/Feature/PublicPageLanguageTest.php](#file-aeee5dd5126b) — named route URL/reference
- [tests/Feature/PublicPageRenderingTest.php](#file-e2f984c6764a) — named route URL/reference
- [tests/Feature/PublicPageSeoTest.php](#file-43c114ebc463) — named route URL/reference
- [tests/Feature/PublicSchoolLeadershipTest.php](#file-7bf1009b45e8) — named route URL/reference
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — named route URL/reference
- [themes/school-of-signals/views/commandant.blade.php](#file-0c1ce9f7fd0d) — named route URL/reference
- [themes/school-of-signals/views/history/past-commandants.blade.php](#file-16d30421a085) — named route URL/reference
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — named route URL/reference
- [themes/school-of-signals/views/leader-profile.blade.php](#file-6843e7ac23ba) — named route URL/reference
- [themes/school-of-signals/views/news/index.blade.php](#file-8fdadeebde29) — named route URL/reference
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — named route URL/reference
- [themes/school-of-signals/views/partials/cms-contact.blade.php](#file-17b45718646c) — named route URL/reference
- [themes/school-of-signals/views/school-leadership.blade.php](#file-81978c1aae5b) — named route URL/reference

<a id="file-d995a9a02eed"></a>
## scripts/build-2026-10-20-deployment.py

Dated incremental deployment payload/hash/archive builder; website request handler එකක් නොවේ.

Category: Project source/configuration. Size: 7,909 bytes.


**Uses:**
- [app/Http/Controllers/PublicHomeController.php](#file-fbf276278997) — literal project path
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — literal project path
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — literal project path
- [app/Models/SchoolLeader.php](#file-7f28db275867) — literal project path
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — literal project path
- [database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php](#file-3934d68405bd) — literal project path
- `public/build` — literal project path
- [resources/views/admin/dashboard.blade.php](#file-1093a76db08f) — literal project path
- [resources/views/admin/partials/sidebar-navigation.blade.php](#file-3576db031dcf) — literal project path
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — literal project path
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — literal project path
- [resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php](#file-b464536172c0) — literal project path
- [resources/views/public/history/past-chief-instructors.blade.php](#file-efe1f99ce875) — literal project path
- [scripts/compile-leadership-profile.mjs](#file-b125b4555dea) — literal project path
- `themes/school-of-signals/assets` — literal project path
- [themes/school-of-signals/views/commandant.blade.php](#file-0c1ce9f7fd0d) — literal project path
- [themes/school-of-signals/views/history/past-commandants.blade.php](#file-16d30421a085) — literal project path
- [themes/school-of-signals/views/leader-profile.blade.php](#file-6843e7ac23ba) — literal project path
- [themes/school-of-signals/views/partials/commandant-feature.blade.php](#file-e3a71403d4f8) — literal project path
- [themes/school-of-signals/views/partials/leadership-profile-content.blade.php](#file-41441cce0e05) — literal project path
- [themes/school-of-signals/views/school-leadership.blade.php](#file-81978c1aae5b) — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-875dd493e5fe"></a>
## scripts/build-2026-10-21-deployment.py

Dated incremental deployment payload/hash/archive builder; website request handler එකක් නොවේ.

Category: Project source/configuration. Size: 3,859 bytes.


**Uses:**
- [app/Http/Controllers/PastSergeantMajorController.php](#file-962b3174f155) — literal project path
- [resources/views/public/galleries/index.blade.php](#file-ab2efd2f40d8) — literal project path
- [resources/views/public/galleries/styles.blade.php](#file-52d4023dc51a) — literal project path
- [resources/views/public/history/past-sgt-major.blade.php](#file-83fd2c429c70) — literal project path
- [routes/web.php](#file-6b9a783d9b43) — literal project path
- [themes/school-of-signals/lang/en/home.php](#file-ed8819e01b29) — literal project path
- [themes/school-of-signals/lang/si/home.php](#file-956065fd9ae9) — literal project path
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — literal project path
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-cff8ad197a2e"></a>
## scripts/build-2026-10-21-hero-link-deployment.py

Dated incremental deployment payload/hash/archive builder; website request handler එකක් නොවේ.

Category: Project source/configuration. Size: 4,153 bytes.


**Uses:**
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — literal project path
- `public/themes/school-of-signals/assets/css/theme.css` — literal project path
- [resources/views/public/home.blade.php](#file-6dd8814bb020) — literal project path
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — literal project path
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b58be82cd8da"></a>
## scripts/build-2026-10-21-hero-modal-deployment.py

Dated incremental deployment payload/hash/archive builder; website request handler එකක් නොවේ.

Category: Project source/configuration. Size: 4,263 bytes.


**Uses:**
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — literal project path
- `public/build` — literal project path
- `public/build/manifest.json` — literal project path
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — literal project path
- [resources/views/components/layouts/admin.blade.php](#file-531cc956eecf) — literal project path
- [resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php](#file-a2efc400ea87) — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-46855021424b"></a>
## scripts/build-2026-10-21-page-css-deployment.py

Dated incremental deployment payload/hash/archive builder; website request handler එකක් නොවේ.

Category: Project source/configuration. Size: 5,111 bytes.


**Uses:**
- [app/Http/Controllers/Admin/PagePreviewController.php](#file-66ad49bdd5c6) — literal project path
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — literal project path
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — literal project path
- [app/Models/Page.php](#file-b56bbabec185) — literal project path
- [app/Rules/ValidPageCss.php](#file-d416e9fb861c) — literal project path
- [app/Services/PageCss.php](#file-d33f6597c9ec) — literal project path
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — literal project path
- [database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php](#file-94773c7c17bf) — literal project path
- `public/build` — literal project path
- `public/build/manifest.json` — literal project path
- [resources/views/admin/pages/preview.blade.php](#file-4833c5cd01ad) — literal project path
- [resources/views/components/admin/icon-action.blade.php](#file-204549bd15dd) — literal project path
- [resources/views/components/forms/page-custom-css.blade.php](#file-e1a01d8b7ce9) — literal project path
- [resources/views/components/page/custom-styles.blade.php](#file-8e69b1288783) — literal project path
- [resources/views/layouts/public.blade.php](#file-dbfeba3a222f) — literal project path
- [resources/views/livewire/admin/pages/page-create.blade.php](#file-09fe2888a3bc) — literal project path
- [resources/views/livewire/admin/pages/page-edit.blade.php](#file-8d2f8249dcd2) — literal project path
- [resources/views/livewire/admin/pages/page-revision-history.blade.php](#file-2ff2d274429c) — literal project path
- [resources/views/pages/show.blade.php](#file-071059c97417) — literal project path
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — literal project path

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-b125b4555dea"></a>
## scripts/compile-leadership-profile.mjs

Runtime Node/Tailwind compiler. LeadershipProfileContent PHP Process එක stdin JSON යවයි; stdout CSS ආපසු ලැබේ.

Category: Project source/configuration. Size: 736 bytes.


**Uses:**
- [package.json](#file-7030d0b2f71b) — Node package dependency

**Used by:**
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — filesystem path reference, literal project path
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-d1364c3b48d4"></a>
## tests/Feature/Admin/AdminAccessTest.php

Automated regression coverage: public registration is disabled; guests are redirected from the admin dashboard

Category: Project source/configuration. Size: 1,004 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- public registration is disabled (line 10)
- guests are redirected from the admin dashboard (line 15)
- users without admin permission are forbidden (line 20)
- super administrators can access the admin dashboard (line 30)

<a id="file-61561546aa52"></a>
## tests/Feature/Admin/AdminLayoutTest.php

Automated regression coverage: super administrator sees the secure admin layout; content editor only sees permitted navigation items

Category: Project source/configuration. Size: 1,220 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- super administrator sees the secure admin layout (line 10)
- content editor only sees permitted navigation items (line 28)

<a id="file-3d1b5be9719e"></a>
## tests/Feature/Admin/ArchiveRetirementTest.php

Automated regression coverage: legacy archive actions reject even a super administrator without modifying records; archive permission cleanup preserves unrelated grants and can run twice

Category: Project source/configuration. Size: 2,816 bytes.


**Uses:**
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RetireArchivePermissionsSeeder.php](#file-b35802b438dd) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- legacy archive actions reject even a super administrator without modifying records (line 25)
- archive permission cleanup preserves unrelated grants and can run twice (line 49)

<a id="file-15860e68fa09"></a>
## tests/Feature/Admin/AuditLogViewerTest.php

Automated regression coverage: auditor can access audit log viewer; user without audit permission is forbidden

Category: Project source/configuration. Size: 2,270 bytes.


**Uses:**
- [app/Livewire/Admin/AuditLogs/AuditLogIndex.php](#file-536a5e50ec90) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- auditor can access audit log viewer (line 13)
- user without audit permission is forbidden (line 23)
- audit records can be searched (line 32)
- audit records can be filtered by event (line 57)

<a id="file-ff3731f0ea22"></a>
## tests/Feature/Admin/AuditLoggingTest.php

Automated regression coverage: successful login creates an audit log; logout creates an audit log

Category: Project source/configuration. Size: 2,378 bytes.


**Uses:**
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/AuditLogger.php](#file-f6d357e44fba) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- successful login creates an audit log (line 9)
- logout creates an audit log (line 27)
- failed login event creates an audit log (line 41)
- sensitive values are redacted (line 62)

<a id="file-cbb07bdddbcb"></a>
## tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php

Automated regression coverage: site administrator can create a document category; duplicate document category names receive unique slugs

Category: Project source/configuration. Size: 11,974 bytes.


**Uses:**
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentCategoryService.php](#file-e2617061d806) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can create a document category (line 19)
- duplicate document category names receive unique slugs (line 90)
- publisher can update a document category (line 141)
- updating a document category keeps its slug when the name is unchanged (line 210)
- document category can be deactivated and activated (line 253)
- content editor cannot manage document categories (line 303)
- media operator cannot manage document categories (line 336)
- document category name is required after sanitization (line 369)
- document category plain text values are sanitized (line 393)
- deleted document category cannot be updated (line 429)
- deleted document category cannot be activated or deactivated (line 467)
- soft deleted document category slug is not reused (line 505)

<a id="file-419e4044300e"></a>
## tests/Feature/Admin/Documents/DocumentCategoryUiTest.php

Automated regression coverage: site administrator can open document category management; publisher can open document category management

Category: Project source/configuration. Size: 15,227 bytes.


**Uses:**
- [app/Livewire/Admin/Documents/DocumentCategoryIndex.php](#file-7aaafb6cc76d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentCategory.php](#file-43cbc2f55250) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can open document category management (line 18)
- publisher can open document category management (line 46)
- content editor cannot open document category management (line 68)
- media operator cannot open document category management (line 88)
- site administrator can create a document category through livewire (line 108)
- document category form validates required category name (line 184)
- administrator can open an existing document category for editing (line 222)
- publisher can update a document category through livewire (line 287)
- administrator can deactivate and reactivate a document category (line 385)
- document category search filters the visible category list (line 455)
- document category search also matches slug and description (line 518)
- clear search resets document category search field (line 581)
- closing category form resets category form state (line 611)

<a id="file-4474786fd2c7"></a>
## tests/Feature/Admin/Documents/DocumentLivewireTest.php

Automated regression coverage: redirects guests away from the document admin route; allows an auditor to view the document index

Category: Project source/configuration. Size: 19,886 bytes.


**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentCreate.php](#file-c47199c40bce) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentEdit.php](#file-2f6ee329ac54) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Documents/DocumentIndex.php](#file-be4cff5447cb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- redirects guests away from the document admin route (line 62)
- allows an auditor to view the document index (line 77)
- allows a content editor to create a document (line 95)
- validates the required document title (line 162)
- prevents an auditor from opening document edit (line 195)
- allows a media operator to add a pdf version to a draft document (line 227)
- prevents a content editor from publishing a document (line 296)
- prevents a media operator from publishing a document (line 343)
- allows a publisher to publish a document but rejects archiving (line 390)
- allows a publisher to replace the live pdf while preserving the document slug (line 464)
- prevents a media operator from replacing the live pdf (line 554)
- allows a media operator to restore a previous version on a draft document (line 626)
- allows a publisher to restore a previous live pdf version (line 691)
- prevents a media operator from restoring a previous live pdf version (line 765)
- does not show restore controls for an archived document (line 836)

<a id="file-ddd73f6cbf01"></a>
## tests/Feature/Admin/Documents/DocumentServiceTest.php

Automated regression coverage: creates a document as draft with a generated slug; generates unique document slugs

Category: Project source/configuration. Size: 27,397 bytes.


**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/DocumentService.php](#file-036a8ff92b3e) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- creates a document as draft with a generated slug (line 48)
- generates unique document slugs (line 78)
- adds the first pdf version to a draft document (line 100)
- increments pdf version numbers sequentially (line 136)
- rejects non public pdf media (line 181)
- rejects non document media as a pdf version (line 208)
- does not publish a document without a pdf version (line 234)
- publishes a document with a valid public pdf version (line 257)
- revalidates current pdf media before publishing (line 294)
- locks document metadata editing after publication (line 332)
- allows an authorised publisher to replace the pdf while preserving the stable slug (line 367)
- prevents a non publisher from replacing the live pdf (line 414)
- archives a published document (line 464)
- prevents adding new versions to archived documents (line 505)
- does not delete a published document directly (line 548)
- allows an archived document to be soft deleted (line 590)
- restores a previous pdf version on a draft document (line 648)
- allows an authorised publisher to restore a previous version on a published document (line 705)
- prevents a non publisher from restoring a previous live pdf version (line 754)
- prevents restoring a previous version on an archived document (line 808)
- prevents restoring a version that belongs to another document (line 861)
- revalidates historic pdf media before restoring it (line 916)
- prevents restoring a version whose pdf has been deleted (line 966)
- continues version numbering from the highest historic version after a restore (line 1012)

<a id="file-6ee1999556a7"></a>
## tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php

Automated regression coverage: rejects a cover image that is not attached to the gallery; allows an attached gallery image to be used as cover

Category: Project source/configuration. Size: 4,842 bytes.


**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- rejects a cover image that is not attached to the gallery (line 66)
- allows an attached gallery image to be used as cover (line 80)
- clears the cover when the cover image is removed (line 100)
- automatically uses the first ordered image as cover when publishing (line 127)
- preserves a manually selected attached cover when publishing (line 145)

<a id="file-ef63a914f6a6"></a>
## tests/Feature/Admin/Galleries/GalleryLivewireTest.php

Automated regression coverage: redirects guests away from the gallery admin route; allows an auditor to view the gallery index

Category: Project source/configuration. Size: 8,851 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryCreate.php](#file-e4f04c336ba1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryIndex.php](#file-67e7ffe3f965) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- redirects guests away from the gallery admin route (line 70)
- allows an auditor to view the gallery index (line 74)
- allows a content editor to create a gallery (line 80)
- validates the required gallery title (line 100)
- prevents an auditor from opening gallery edit (line 112)
- allows a media operator to add an image to a gallery (line 120)
- allows a media operator to update gallery image metadata (line 143)
- allows a media operator to reorder gallery images (line 169)
- allows a media operator to remove a gallery image (line 189)
- prevents a content editor from publishing a gallery (line 207)
- prevents a media operator from publishing a gallery (line 226)
- allows a publisher to publish and archive a gallery (line 245)

<a id="file-295658b83713"></a>
## tests/Feature/Admin/Galleries/GallerySecurityTest.php

Automated regression coverage: has the correct gallery permission matrix; enforces one media asset only once per gallery at database level

Category: Project source/configuration. Size: 9,299 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Galleries/GalleryEdit.php](#file-c088855056da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- has the correct gallery permission matrix (line 71)
- enforces one media asset only once per gallery at database level (line 102)
- enforces unique gallery slugs at database level (line 128)
- does not reuse a slug belonging to a soft deleted gallery (line 135)
- soft deletes a draft gallery without deleting its image records (line 148)
- deletes gallery image rows when a gallery is force deleted (line 166)
- deletes gallery image rows when the media asset is force deleted (line 181)
- cannot modify an image belonging to another gallery through gallery edit (line 197)
- does not publish a gallery when an attached media asset was soft deleted (line 217)
- does not publish a gallery after attached media becomes restricted (line 232)
- cannot archive a draft gallery (line 247)
- cannot publish an archived gallery again (line 259)

<a id="file-d9bfc5e25fe7"></a>
## tests/Feature/Admin/Galleries/GalleryServiceTest.php

Automated regression coverage: creates a gallery as draft with a generated slug; generates unique gallery slugs

Category: Project source/configuration. Size: 8,715 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- creates a gallery as draft with a generated slug (line 61)
- generates unique gallery slugs (line 77)
- adds a public image to a draft gallery (line 85)
- rejects non public gallery images (line 104)
- prevents duplicate images in the same gallery (line 115)
- reorders all images in a gallery (line 128)
- rejects an incomplete image reorder list (line 149)
- does not publish an empty gallery (line 161)
- publishes a gallery that contains a public image (line 170)
- revalidates media before publishing (line 183)
- locks normal editing after publication (line 196)
- archives a published gallery (line 208)
- does not delete a published gallery directly (line 220)

<a id="file-c29a742c88d8"></a>
## tests/Feature/Admin/HeroSlideAdminUiTest.php

Automated regression coverage: allows an authorized administrator to open the hero slider editor; forbids administrators without hero slide permissions

Category: Project source/configuration. Size: 15,049 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- allows an authorized administrator to open the hero slider editor (line 55)
- forbids administrators without hero slide permissions (line 67)
- creates english and sinhala hero slide translations (line 77)
- edits and deactivates an existing hero slide (line 160)
- changes slide order and deletes a slide (line 223)
- uploads a new public image and links it to the hero slide (line 262)
- starts with a closed modal and opens a clean create form (line 344)
- keeps the modal open on validation errors and closes after creating a slide (line 366)
- cancels edit and resets validation and uploads without changing saved data (line 385)
- opens and saves an existing slide through the modal (line 408)
- rejects opening create and edit modals without the corresponding permission (line 432)
- keeps the modal unavailable to read-only viewers (line 448)
- loads the modal with the Flux browser runtime (line 461)

<a id="file-c2d127affbdc"></a>
## tests/Feature/Admin/HeroSlideTranslationModelTest.php

Automated regression coverage: stores english and sinhala translations for a hero slide; falls back to english when the requested translation is unavailable

Category: Project source/configuration. Size: 3,248 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlideTranslation.php](#file-f30398e71d18) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- stores english and sinhala translations for a hero slide (line 10)
- falls back to english when the requested translation is unavailable (line 53)
- can disable english fallback (line 76)

<a id="file-d4bd54bc3f50"></a>
## tests/Feature/Admin/MediaAutomaticVariantTest.php

Automated regression coverage: image upload automatically generates image variants; document upload does not generate image variants

Category: Project source/configuration. Size: 7,181 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- image upload automatically generates image variants (line 29)
- document upload does not generate image variants (line 86)
- image exceeding configured dimensions is rejected before storage (line 118)
- image exceeding configured total pixels is rejected (line 160)
- changing public image to internal moves original and variants (line 196)
- changing internal image to public moves original and variants (line 274)

<a id="file-186e3a771026"></a>
## tests/Feature/Admin/MediaDeletionServiceTest.php

Automated regression coverage: media operator can move media to trash without deleting physical files; media operator can restore trashed media and physical files remain available

Category: Project source/configuration. Size: 10,096 bytes.


**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaVariant.php](#file-da95f7d80c02) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaDeletionService.php](#file-beb486d6c9dc) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can move media to trash without deleting physical files (line 156)
- media operator can restore trashed media and physical files remain available (line 221)
- permanent deletion removes media variants and physical files (line 297)
- active media cannot be permanently deleted directly (line 367)
- auditor cannot delete media (line 424)
- delete restore and permanent delete actions are audited (line 462)

<a id="file-f10adbb3868c"></a>
## tests/Feature/Admin/MediaEditTest.php

Automated regression coverage: media operator can open media details screen; public image details use medium variant preview

Category: Project source/configuration. Size: 9,235 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaMetadataService.php](#file-09f6b1bf60d9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can open media details screen (line 32)
- public image details use medium variant preview (line 57)
- auditor can view media details in read only mode (line 117)
- media operator can update image metadata (line 141)
- document metadata update clears image alternative text (line 196)
- changing public media to internal moves physical file to private disk (line 232)
- changing internal media to public moves physical file to public disk (line 301)
- media update writes audit log without full caption content (line 368)
- auditor cannot update media metadata (line 425)

<a id="file-5c9085acd019"></a>
## tests/Feature/Admin/MediaFoundationTest.php

Automated regression coverage: media asset automatically receives uuid and enum casts; media asset stores uploader relationship

Category: Project source/configuration. Size: 5,554 bytes.

Explicit permission literals: `media.delete`, `media.replace`, `media.update`, `media.upload`, `media.view`.

**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadSecurity.php](#file-1421bdb7a1e5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media asset automatically receives uuid and enum casts (line 17)
- media asset stores uploader relationship (line 48)
- media assets support soft deletion (line 62)
- image upload security only accepts configured image mime types (line 90)
- document uploads only allow pdf in initial media version (line 135)
- direct video uploads are disabled initially (line 157)
- dangerous executable and web extensions are forbidden (line 169)
- media operator receives full media permissions (line 210)
- content editor cannot delete or replace media (line 243)

<a id="file-d44d7ca47b7c"></a>
## tests/Feature/Admin/MediaImageVariantTest.php

Automated regression coverage: uploaded image can generate thumbnail and medium variants; image variants preserve aspect ratio without upscaling small image

Category: Project source/configuration. Size: 5,778 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVariantPreset.php](#file-7502de646059) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaImageVariantService.php](#file-b85a526868fe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- uploaded image can generate thumbnail and medium variants (line 29)
- image variants preserve aspect ratio without upscaling small image (line 114)
- private image variants remain on private disk (line 167)
- regenerating variants replaces old physical variant files (line 211)
- documents cannot generate image variants (line 283)

<a id="file-689bc91f5112"></a>
## tests/Feature/Admin/MediaIndexDeletionUiTest.php

Automated regression coverage: media operator can move media to trash from media index; media operator can restore trashed media from media index

Category: Project source/configuration. Size: 7,269 bytes.


**Uses:**
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can move media to trash from media index (line 25)
- media operator can restore trashed media from media index (line 68)
- media operator can permanently delete trashed media from media index (line 124)
- active media view shows normal media actions (line 170)
- trash view shows restore and permanent delete actions (line 201)
- auditor does not see destructive media actions (line 238)
- auditor cannot call delete media action directly (line 269)
- active media cannot be force deleted through media index action (line 308)

<a id="file-f604286cfa46"></a>
## tests/Feature/Admin/MediaIndexTest.php

Automated regression coverage: media operator can open media library; auditor can view media library but cannot see upload action

Category: Project source/configuration. Size: 5,798 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaIndex.php](#file-cc0494b8c04b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can open media library (line 17)
- auditor can view media library but cannot see upload action (line 37)
- media library displays active assets (line 55)
- media library search filters media assets (line 73)
- media library filters by type (line 102)
- media library filters by visibility (line 135)
- trash view only displays soft deleted media (line 170)
- media display can switch between grid and list (line 201)
- media filters can be cleared (line 232)

<a id="file-c65f02eb5046"></a>
## tests/Feature/Admin/MediaReplacementServiceTest.php

Automated regression coverage: media operator can replace an image while preserving media identity and metadata; media operator can replace a pdf document without changing media identity

Category: Project source/configuration. Size: 13,904 bytes.


**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaReplacementService.php](#file-1ee54b331819) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can replace an image while preserving media identity and metadata (line 60)
- media operator can replace a pdf document without changing media identity (line 307)
- image media cannot be replaced with a pdf document (line 438)
- auditor cannot replace media files (line 506)
- trashed media cannot be replaced (line 572)
- external media cannot be replaced with an uploaded file (line 631)
- internal media replacement remains on the private storage disk (line 691)

<a id="file-2f28a15a4bd6"></a>
## tests/Feature/Admin/MediaReplacementUiTest.php

Automated regression coverage: media operator can see the replace file interface; media operator can replace an image through media edit

Category: Project source/configuration. Size: 8,646 bytes.


**Uses:**
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaEdit.php](#file-c25fa36484ed) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can see the replace file interface (line 29)
- media operator can replace an image through media edit (line 78)
- media edit rejects replacement with the wrong physical media type (line 272)
- auditor cannot see or execute media replacement (line 350)
- external media does not expose the replace file interface (line 436)

<a id="file-7f31096236ad"></a>
## tests/Feature/Admin/MediaUploadServiceTest.php

Automated regression coverage: media operator can securely upload public image; uploaded image receives sha256 checksum

Category: Project source/configuration. Size: 9,187 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can securely upload public image (line 29)
- uploaded image receives sha256 checksum (line 95)
- pdf document can be securely uploaded (line 133)
- internal media is stored on private disk (line 179)
- restricted media is stored on private disk (line 218)
- svg upload is rejected (line 251)
- file with mismatched extension and content is rejected (line 286)
- oversized media file is rejected (line 320)
- user without upload permission cannot upload media (line 357)
- media upload is recorded in audit log (line 389)

<a id="file-44589dae6f75"></a>
## tests/Feature/Admin/MediaUploadUiTest.php

Automated regression coverage: media operator can open media upload screen; auditor cannot access media upload screen

Category: Project source/configuration. Size: 7,220 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Media/MediaUpload.php](#file-25d5e88e3901) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- media operator can open media upload screen (line 27)
- auditor cannot access media upload screen (line 48)
- media upload screen requires a file (line 64)
- media operator can upload image through livewire screen (line 79)
- media operator can upload pdf through livewire screen (line 155)
- internal upload from screen uses private disk (line 210)
- unsupported media type cannot be submitted through ui (line 255)
- successful upload resets form defaults (line 283)

<a id="file-8c93247a4c08"></a>
## tests/Feature/Admin/MenuServiceTest.php

Automated regression coverage: site administrator can create a menu and custom link item; content editor cannot manage menus

Category: Project source/configuration. Size: 1,566 bytes.


**Uses:**
- [app/Models/Menu.php](#file-72f78bded920) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can create a menu and custom link item (line 13)
- content editor cannot manage menus (line 45)

<a id="file-49da0724cc08"></a>
## tests/Feature/Admin/MenuTranslationServiceTest.php

Automated regression coverage: administrator can save three language menu labels and urls; missing menu translation safely falls back to english

Category: Project source/configuration. Size: 2,433 bytes.


**Uses:**
- [app/Models/MenuItem.php](#file-55fd9fcfc0cd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MenuService.php](#file-3748da885f11) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- administrator can save three language menu labels and urls (line 12)
- missing menu translation safely falls back to english (line 49)

<a id="file-889047e3bfd5"></a>
## tests/Feature/Admin/News/NewsEditorModeTest.php

Automated regression coverage: visual news content keeps approved word like formatting; html tailwind news content keeps safe semantic markup and classes

Category: Project source/configuration. Size: 2,094 bytes.


**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- visual news content keeps approved word like formatting (line 16)
- html tailwind news content keeps safe semantic markup and classes (line 40)

<a id="file-a30056491650"></a>
## tests/Feature/Admin/News/NewsSimplifiedActionsTest.php

Automated regression coverage: site administrator can publish and unpublish news directly from the news list; draft news can be moved to trash and restored from the simplified list

Category: Project source/configuration. Size: 3,533 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsIndex.php](#file-e77d9eae07c9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsDeletionService.php](#file-23c43b2d93e6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can publish and unpublish news directly from the news list (line 18)
- draft news can be moved to trash and restored from the simplified list (line 59)
- published news must be unpublished before deletion (line 90)
- active news list exposes only the simplified page style actions (line 114)

<a id="file-c8e3fe8a6628"></a>
## tests/Feature/Admin/News/NewsTranslationManagementTest.php

Automated regression coverage: a news translation starts empty and must be entered manually; one news translation group cannot contain the same language twice

Category: Project source/configuration. Size: 4,255 bytes.


**Uses:**
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- a news translation starts empty and must be entered manually (line 18)
- one news translation group cannot contain the same language twice (line 101)
- the same news slug may be used once in each language (line 123)

<a id="file-416b3fa74f8d"></a>
## tests/Feature/Admin/NewsArticleServiceTest.php

Automated regression coverage: featured image must be selected from the article images; content editor can create a draft news article

Category: Project source/configuration. Size: 16,346 bytes.


**Uses:**
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/MediaUploadService.php](#file-efd55e66e6b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsImageService.php](#file-a5945259a50d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- featured image must be selected from the article images (line 34)
- content editor can create a draft news article (line 63)
- new news articles cannot be created directly as published (line 195)
- duplicate article titles receive unique slugs (line 231)
- news rich content is sanitized before storage (line 281)
- content editor can update a news article (line 330)
- content editor can explicitly change a draft article slug (line 450)
- auditor cannot create news articles (line 498)
- inactive category cannot be assigned to a news article (line 534)
- internal image cannot be used as a news featured image (line 564)
- blank news body is rejected (line 614)
- deleted news article cannot be updated (line 644)
- published news article cannot be edited through draft update service (line 690)
- updating a news article stores the previous state as a revision (line 753)

<a id="file-f0a5049c6b3e"></a>
## tests/Feature/Admin/NewsCategoryServiceTest.php

Automated regression coverage: site administrator can create a news category; duplicate news category names receive unique slugs

Category: Project source/configuration. Size: 7,584 bytes.


**Uses:**
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsCategoryService.php](#file-c836ba39f3a1) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can create a news category (line 16)
- duplicate news category names receive unique slugs (line 100)
- publisher can update a news category (line 149)
- updating a category keeps its slug when the name is unchanged (line 223)
- news category can be deactivated and activated (line 261)
- content editor cannot manage news categories (line 306)
- category name is required after sanitization (line 336)
- category plain text values are sanitized (line 357)
- deleted news category cannot be updated (line 394)

<a id="file-7df7d018446c"></a>
## tests/Feature/Admin/NewsCategoryUiTest.php

Automated regression coverage: site administrator can open news category management; publisher can open news category management

Category: Project source/configuration. Size: 9,191 bytes.


**Uses:**
- [app/Livewire/Admin/News/NewsCategoryIndex.php](#file-e17c1268834d) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can open news category management (line 15)
- publisher can open news category management (line 37)
- content editor cannot open news category management (line 56)
- site administrator can create a news category through livewire (line 73)
- news category form validates required category name (line 146)
- administrator can open an existing category for editing (line 181)
- publisher can update a news category through livewire (line 242)
- administrator can deactivate and reactivate a category (line 330)
- category search filters the visible category list (line 392)
- clear search resets category search field (line 434)

<a id="file-de5ca9a88bc8"></a>
## tests/Feature/Admin/NewsRevisionServiceTest.php

Automated regression coverage: news revision service creates a snapshot of current article state; revision numbers increase sequentially for the same news article

Category: Project source/configuration. Size: 4,016 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- news revision service creates a snapshot of current article state (line 13)
- revision numbers increase sequentially for the same news article (line 75)
- different news articles maintain independent revision numbers (line 114)
- deleted news article cannot create a revision (line 150)

<a id="file-d840e7f01131"></a>
## tests/Feature/Admin/NewsUiTest.php

Automated regression coverage: authorized administrator can open news index; authorized administrator can open news create page

Category: Project source/configuration. Size: 7,978 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsCreate.php](#file-5abc13eeaeb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/News/NewsEdit.php](#file-484456672e53) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsCategory.php](#file-f5d3b9e23d57) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- authorized administrator can open news index (line 48)
- authorized administrator can open news create page (line 65)
- administrator without news create permission is forbidden (line 83)
- authorized administrator can create a draft news article (line 99)
- news create requires title category and content (line 167)
- authorized administrator can update a draft news article (line 202)
- selected featured image remains after saving the draft (line 263)
- published article edit page is read only (line 293)
- administrator without news update permission cannot open edit page (line 334)

<a id="file-2f41b321ac01"></a>
## tests/Feature/Admin/NewsWorkflowServiceTest.php

Automated regression coverage: news workflow roles have the expected permissions; a content editor can submit a draft news article for review

Category: Project source/configuration. Size: 19,536 bytes.

Explicit permission literals: `news.approve`, `news.archive`, `news.publish`, `news.request-changes`, `news.submit`.

**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsWorkflowService.php](#file-498368288f8f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- news workflow roles have the expected permissions (line 26)
- a content editor can submit a draft news article for review (line 103)
- submitting a draft creates a revision snapshot (line 146)
- a publisher can request changes from a submitted article (line 200)
- a change request requires a review note (line 251)
- a content editor can resubmit an article after changes are requested (line 294)
- a publisher can approve a submitted article (line 357)
- a publisher can publish an approved article (line 406)
- publishing preserves a future planned publication time (line 449)
- a publisher can archive a published article (line 509)
- a news article can complete the full review workflow (line 561)
- a draft article cannot be approved directly (line 689)
- a submitted article cannot be published before approval (line 723)
- an approved article cannot be archived before publication (line 757)
- a content editor cannot approve a submitted article (line 797)
- a content editor cannot publish an approved article (line 831)

<a id="file-82dd53475672"></a>
## tests/Feature/Admin/PageBlockFoundationTest.php

Automated regression coverage: page block sanitizer normalizes supported blocks; page block rich text removes dangerous html

Category: Project source/configuration. Size: 6,352 bytes.


**Uses:**
- [app/Services/PageBlockSanitizer.php](#file-e7b4ea1426b4) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page block sanitizer normalizes supported blocks (line 6)
- page block rich text removes dangerous html (line 54)
- heading text is stored as plain text (line 83)
- invalid heading level uses safe default (line 106)
- javascript button url is rejected (line 129)
- button allows safe internal url (line 152)
- protocol relative urls are rejected (line 177)
- unsupported block type is rejected (line 199)
- two column block sanitizes both columns (line 221)
- spacer only accepts predefined sizes (line 260)

<a id="file-7081eaced100"></a>
## tests/Feature/Admin/PageBuilderBackendTest.php

Automated regression coverage: page create can add builder blocks; page builder blocks can be reordered

Category: Project source/configuration. Size: 6,589 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page create can add builder blocks (line 18)
- page builder blocks can be reordered (line 52)
- page builder block can be removed (line 94)
- page create stores sanitized builder blocks (line 119)
- unsupported page builder block cannot be added (line 175)
- page edit loads existing builder blocks (line 190)
- page edit stores block changes and creates revision (line 229)

<a id="file-5cb4a7f210e3"></a>
## tests/Feature/Admin/PageBuilderUiTest.php

Automated regression coverage: page create offers visual and html tailwind editors; page edit offers visual and html tailwind editors

Category: Project source/configuration. Size: 1,710 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page create offers visual and html tailwind editors (line 17)
- page edit offers visual and html tailwind editors (line 35)

<a id="file-480c5674afee"></a>
## tests/Feature/Admin/PageCreateTest.php

Automated regression coverage: content editor can access page creation form; user without page creation permission is forbidden

Category: Project source/configuration. Size: 3,793 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can access page creation form (line 17)
- user without page creation permission is forbidden (line 27)
- content editor can create a draft page (line 36)
- duplicate page slugs receive a numeric suffix (line 62)
- creating a page creates a safe audit record (line 85)
- page title is required (line 127)

<a id="file-2e3e81e7e226"></a>
## tests/Feature/Admin/PageCustomCssTest.php

Automated regression coverage: creates pages with custom CSS in each language; detects CSS-only edits and can restore and remove styles

Category: Project source/configuration. Size: 4,849 bytes.


**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- creates pages with custom CSS in each language (line 19)
- detects CSS-only edits and can restore and remove styles (line 38)
- copies styling into a new translation without copying the wording (line 63)
- shows a CSS validation error and leaves the existing draft unchanged (line 79)
- shows escaped CSS source in revision history (line 92)
- renders page CSS in saved previews (line 100)

<a id="file-a0b9b38d5ed9"></a>
## tests/Feature/Admin/PageDeletionTest.php

Automated regression coverage: site administrator can move a draft page to trash; content editor cannot delete a page

Category: Project source/configuration. Size: 4,275 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can move a draft page to trash (line 17)
- content editor cannot delete a page (line 40)
- published page cannot be directly deleted (line 57)
- trashed pages can be displayed using the trash filter (line 76)
- site administrator can restore a trashed page (line 98)
- page delete and restore actions create audit records (line 121)

<a id="file-ddcc07423d2a"></a>
## tests/Feature/Admin/PageEditTest.php

Automated regression coverage: content editor can access draft page edit form; user without update permission cannot edit a page

Category: Project source/configuration. Size: 4,531 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can access draft page edit form (line 17)
- user without update permission cannot edit a page (line 32)
- content editor can update a draft page (line 43)
- duplicate slug cannot be assigned during page editing (line 78)
- updating a page creates a safe change audit record (line 102)
- published pages cannot be directly edited (line 156)

<a id="file-400571fb9d80"></a>
## tests/Feature/Admin/PageEditorModeTest.php

Automated regression coverage: new pages default to the visual editor; developer can switch from visual to html and tailwind mode

Category: Project source/configuration. Size: 4,974 bytes.


**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- new pages default to the visual editor (line 18)
- developer can switch from visual to html and tailwind mode (line 51)
- editor mode can change while page content exists without duplicating content (line 85)
- existing html page can choose visual mode while preserving the same source (line 110)
- visual and html pages use different public content wrappers (line 143)

<a id="file-7fc9257c3083"></a>
## tests/Feature/Admin/PageFoundationTest.php

Automated regression coverage: page stores workflow ownership and blocks; page slugs must be unique

Category: Project source/configuration. Size: 2,715 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page stores workflow ownership and blocks (line 11)
- page slugs must be unique (line 43)
- published scope returns only currently published pages (line 55)
- page workflow allows only valid transitions (line 82)

<a id="file-b7607c93af4c"></a>
## tests/Feature/Admin/PageIndexTest.php

Automated regression coverage: content editor can access pages index; user without pages view permission is forbidden

Category: Project source/configuration. Size: 2,571 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can access pages index (line 14)
- user without pages view permission is forbidden (line 24)
- pages can be searched by title (line 33)
- pages can be filtered by workflow status (line 54)
- invalid page filters are normalised (line 79)

<a id="file-2d1c809d0d3c"></a>
## tests/Feature/Admin/PagePreviewTest.php

Automated regression coverage: content editor can preview a draft page; published page can be previewed by an authorised user

Category: Project source/configuration. Size: 3,844 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can preview a draft page (line 14)
- published page can be previewed by an authorised user (line 36)
- user without page view permission cannot access preview (line 58)
- trashed page cannot be previewed (line 73)
- admin preview renders only sanitized html (line 90)
- admin preview always uses noindex metadata (line 134)

<a id="file-d239b175d9cd"></a>
## tests/Feature/Admin/PageRevisionFoundationTest.php

Automated regression coverage: page revision snapshot can be created; identical page snapshots do not create duplicate revisions

Category: Project source/configuration. Size: 4,921 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page revision snapshot can be created (line 19)
- identical page snapshots do not create duplicate revisions (line 55)
- editing a page creates a new revision (line 91)
- workflow transition creates a new revision (line 141)
- revision stores sanitized page content (line 180)

<a id="file-963b4d81b749"></a>
## tests/Feature/Admin/PageRevisionHistoryTest.php

Automated regression coverage: content editor can view page revision history; user without revision permission cannot view history

Category: Project source/configuration. Size: 8,453 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageRevisionHistory.php](#file-c4c24d2889f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/PageRevision.php](#file-6bb9cc9d8d9b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can view page revision history (line 19)
- user without revision permission cannot view history (line 48)
- revision history displays multiple saved versions (line 64)
- revision can be selected for comparison (line 104)
- content editor can restore a revision when page is draft (line 148)
- restoring a revision creates a new revision (line 203)
- published page cannot restore a revision directly (line 265)
- revision restoration is written to audit log (line 304)

<a id="file-cee52032b398"></a>
## tests/Feature/Admin/PageSeoFormTest.php

Automated regression coverage: page create stores seo metadata; page edit updates seo metadata

Category: Project source/configuration. Size: 4,829 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageEdit.php](#file-bfa6a6753fc6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page create stores seo metadata (line 17)
- page edit updates seo metadata (line 81)
- seo text fields are converted to plain text (line 133)
- invalid canonical url is rejected (line 162)
- invalid open graph image url is rejected (line 179)

<a id="file-256dd99b18f3"></a>
## tests/Feature/Admin/PageSeoFoundationTest.php

Automated regression coverage: page seo values use configured metadata; page seo falls back to page information

Category: Project source/configuration. Size: 3,977 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageRevisionService.php](#file-5c0b5a534567) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/PageSeo.php](#file-c916378ce234) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- page seo values use configured metadata (line 8)
- page seo falls back to page information (line 59)
- revision snapshot contains page seo metadata (line 105)

<a id="file-144babc8a862"></a>
## tests/Feature/Admin/PageTranslationManagementTest.php

Automated regression coverage: a translation starts empty and must be entered manually; one translation group cannot contain the same language twice

Category: Project source/configuration. Size: 2,838 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageCreate.php](#file-55f854022c60) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- a translation starts empty and must be entered manually (line 17)
- one translation group cannot contain the same language twice (line 88)

<a id="file-36edcccee3fe"></a>
## tests/Feature/Admin/PageWorkflowTest.php

Automated regression coverage: content editor can submit a draft page; publisher can approve a submitted page

Category: Project source/configuration. Size: 4,309 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Pages/PageIndex.php](#file-e179a105470c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageWorkflowService.php](#file-3ef41ff28549) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- content editor can submit a draft page (line 19)
- publisher can approve a submitted page (line 40)
- publisher can publish an approved page (line 63)
- content editor cannot approve a submitted page (line 84)
- submitted page cannot be published directly (line 98)
- published page can be archived (line 117)
- workflow transitions create audit records (line 138)

<a id="file-332bb5013c6b"></a>
## tests/Feature/Admin/RedirectManagerTest.php

Automated regression coverage: site administrator can create a working redirect

Category: Project source/configuration. Size: 859 bytes.


**Uses:**
- [app/Models/Redirect.php](#file-1efb27655fd7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/RedirectService.php](#file-7ca08192da71) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can create a working redirect (line 12)

<a id="file-82b88f2fd69a"></a>
## tests/Feature/Admin/RolePermissionManagementTest.php

Automated regression coverage: super administrator can open role permission management; site administrator cannot open role permission management

Category: Project source/configuration. Size: 881 bytes.


**Uses:**
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- super administrator can open role permission management (line 12)
- site administrator cannot open role permission management (line 22)

<a id="file-c46d2e172d25"></a>
## tests/Feature/Admin/SchoolLeadershipDeleteTest.php

Automated regression coverage: deletes present and past appointments with an audit trail while preserving shared records; hides delete and rejects direct deletion for read-only users

Category: Project source/configuration. Size: 5,987 bytes.


**Uses:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- deletes present and past appointments with an audit trail while preserving shared records (line 48)
- hides delete and rejects direct deletion for read-only users (line 82)
- checks view authorization again before deleting an appointment (line 94)
- returns not found for an appointment that no longer exists (line 106)
- closes and clears the editor when its appointment is deleted (line 113)
- returns to a populated page after deleting the last appointment on a page (line 127)

<a id="file-d05ff34cc065"></a>
## tests/Feature/Admin/SchoolLeadershipModalTest.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 4,631 bytes.


**Uses:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-1961d571f891"></a>
## tests/Feature/Admin/SchoolLeadershipProfileTest.php

Automated regression coverage: saves bilingual HTML and CSS and compiles Tailwind classes added in the editor; opens the complete profile editor under the administration layout

Category: Project source/configuration. Size: 20,371 bytes.


**Uses:**
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php](#file-b9f46b401967) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php](#file-a53f8a5c6b37) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- saves bilingual HTML and CSS and compiles Tailwind classes added in the editor (line 51)
- opens the complete profile editor under the administration layout (line 75)
- requires meaningful content in both languages before enabling (line 90)
- previews unsaved edits without publishing them (line 101)
- rejects editing actions for viewers and users without leadership access (line 112)
- keeps separate content when switching appointment holders (line 122)
- offers active present and past appointments including custom types in the profile editor (line 133)
- offers profile creation and editing for past appointments (line 152)
- keeps an open profile editor usable when an appointment becomes past (line 174)
- strips executable HTML and rejects unsafe CSS (line 186)
- isolates profile styles in a sandboxed document and prevents style tag breakouts (line 200)
- publishes enabled profiles for original and custom types and hides disabled pages (line 206)
- creates and enables a profile after adding a new present appointment through the admin forms (line 236)
- keeps former profiles available by record while excluding inactive profiles (line 279)
- preserves a saved profile and its URL when a present appointment ends and is replaced (line 290)
- hides disabled past profiles while keeping their saved content editable (line 351)
- links enabled past profiles from the existing appointment history pages (line 366)

<a id="file-5493846d3c60"></a>
## tests/Feature/Admin/SchoolModulePermissionUiTest.php

Automated regression coverage: hero creator without media upload cannot see the upload input; delegated roles manage permission allows inspection but not role creation

Category: Project source/configuration. Size: 6,358 bytes.


**Uses:**
- [app/Livewire/Admin/HeroSlides/HeroSlideIndex.php](#file-4e671f1927da) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Roles/RolePermissionIndex.php](#file-e09106fdf445) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php](#file-5167e1c4a98c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- hero creator without media upload cannot see the upload input (line 58)
- delegated roles manage permission allows inspection but not role creation (line 71)
- delegated roles manage permission cannot change role grants (line 86)
- unknown permission values are rejected without removing existing role grants (line 101)
- module actions require the corresponding view permission (line 119)
- super administrator can save a valid module permission set (line 139)

<a id="file-53397044bc14"></a>
## tests/Feature/Admin/SiteManagementFoundationTest.php

Automated regression coverage: site administrator can open site management modules; content editor cannot open site management modules

Category: Project source/configuration. Size: 1,440 bytes.


**Uses:**
- [app/Livewire/Admin/ContactMessages/ContactMessageIndex.php](#file-3041eb541004) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Redirects/RedirectIndex.php](#file-5c69212d1a67) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Settings/SiteSettingsIndex.php](#file-484bcafc91d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- site administrator can open site management modules (line 18)
- content editor cannot open site management modules (line 28)

<a id="file-df50d49fcb12"></a>
## tests/Feature/Admin/ThemeLayoutAdminUiTest.php

Automated regression coverage: allows an authorized administrator to open the multilingual editor; forbids administrators without the dedicated layout permission

Category: Project source/configuration. Size: 5,215 bytes.


**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php](#file-371d04803d32) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- allows an authorized administrator to open the multilingual editor (line 42)
- forbids administrators without the dedicated layout permission (line 53)
- keeps language editor content independent when switching tabs (line 59)
- saves and publishes only the selected language layout (line 116)

<a id="file-e307f4b73342"></a>
## tests/Feature/Admin/ThemeLayoutModelTest.php

Automated regression coverage: stores a theme specific draft layout with enum casts

Category: Project source/configuration. Size: 813 bytes.


**Uses:**
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- stores a theme specific draft layout with enum casts (line 10)

<a id="file-dc469ecd3bdf"></a>
## tests/Feature/Admin/ThemeLayoutSanitizerTest.php

Automated regression coverage: preserves safe semantic html tailwind classes and approved placeholders; removes unsafe attributes and uri schemes from otherwise safe html

Category: Project source/configuration. Size: 3,174 bytes.

Config reads: `app.key`.

**Uses:**
- [app/Services/ThemeLayoutSanitizer.php](#file-bcf26409fe4a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- preserves safe semantic html tailwind classes and approved placeholders (line 6)
- removes unsafe attributes and uri schemes from otherwise safe html (line 17)
- rejects executable template syntax (line 28)
- rejects unknown placeholders (line 37)
- accepts local presentation css (line 43)
- rejects css capable of loading external resources or breaking the style element (line 51)
- preserves a safe local site url placeholder inside an href attribute (line 60)
- rejects site url placeholders outside an exact local href value (line 71)

<a id="file-f293f9cdc665"></a>
## tests/Feature/Admin/ThemeLayoutServiceTest.php

Automated regression coverage: saves a sanitized localized draft with a revision and audit entry; stores each header language independently

Category: Project source/configuration. Size: 5,765 bytes.


**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutService.php](#file-4b0918515100) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- saves a sanitized localized draft with a revision and audit entry (line 20)
- stores each header language independently (line 47)
- publishes the current localized draft without changing it during later draft edits (line 98)
- rejects users without the dedicated permission (line 139)
- rejects an unavailable theme (line 152)

<a id="file-c7a37ba659ca"></a>
## tests/Feature/Admin/UserActionAuditTest.php

Automated regression coverage: creating a user creates an audit record; changing a user role creates an audit record

Category: Project source/configuration. Size: 3,502 bytes.


**Uses:**
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/AuditLog.php](#file-f0b1464bc270) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- creating a user creates an audit record (line 15)
- changing a user role creates an audit record (line 44)
- disabling a user creates an audit record (line 64)
- password reset audit does not contain passwords (line 86)

<a id="file-29370412f779"></a>
## tests/Feature/Admin/UserManagementFormTest.php

Automated regression coverage: super administrator can create a user; site administrator cannot assign a protected role

Category: Project source/configuration. Size: 6,796 bytes.


**Uses:**
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- super administrator can create a user (line 15)
- site administrator cannot assign a protected role (line 51)
- super administrator can update user and role (line 76)
- super administrator cannot remove their own role (line 107)
- administrator can securely reset another user password (line 125)
- password reset requires administrator current password (line 185)
- administrator cannot use admin reset on own password (line 219)

<a id="file-e113722c846b"></a>
## tests/Feature/Admin/UserManagementIndexTest.php

Automated regression coverage: super administrator can visit the users list; users without users view permission are forbidden

Category: Project source/configuration. Size: 3,102 bytes.


**Uses:**
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- super administrator can visit the users list (line 12)
- users without users view permission are forbidden (line 25)
- users can be searched by name (line 37)
- super administrator can disable another user (line 61)
- administrator cannot disable their own account (line 82)
- last active super administrator cannot be disabled (line 98)

<a id="file-22dca52ec16f"></a>
## tests/Feature/Admin/UserPermissionBoundariesTest.php

Automated regression coverage: a role assigner cannot assign custom permissions beyond their own; protected administrator roles cannot be assigned by a non super administrator

Category: Project source/configuration. Size: 5,438 bytes.


**Uses:**
- [app/Livewire/Admin/Users/UserCreate.php](#file-c07bf0f22937) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserEdit.php](#file-162a921b1cfe) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Livewire/Admin/Users/UserIndex.php](#file-e81e9bfe9fb5) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Support/UserManagementRules.php](#file-ab07cd3dce6a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- a role assigner cannot assign custom permissions beyond their own (line 20)
- protected administrator roles cannot be assigned by a non super administrator (line 31)
- direct target permissions are included in account management checks (line 42)
- profile editing works without role assignment permission (line 51)
- profile editor cannot forge a role change (line 66)
- account manager cannot disable a stronger custom account (line 81)
- super administrator cannot disable their own account (line 96)
- create-only user is redirected to the user list instead of the forbidden edit page (line 107)

<a id="file-0a8c21330d85"></a>
## tests/Feature/Admin/UserSecurityTest.php

Automated regression coverage: active users can authenticate; inactive users cannot authenticate

Category: Project source/configuration. Size: 1,576 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- active users can authenticate (line 6)
- inactive users cannot authenticate (line 20)
- inactive authenticated users are logged out (line 34)
- successful login records date and IP address (line 48)

<a id="file-ad5adf97ee88"></a>
## tests/Feature/Auth/AuthenticationTest.php

Automated regression coverage: login screen can be rendered; users can authenticate using the login screen

Category: Project source/configuration. Size: 1,671 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- login screen can be rendered (line 6)
- users can authenticate using the login screen (line 12)
- users can not authenticate with invalid password (line 27)
- users with two factor enabled are redirected to two factor challenge (line 40)
- users can logout (line 59)

<a id="file-a975c7792e9d"></a>
## tests/Feature/Auth/EmailVerificationTest.php

Automated regression coverage: email verification screen can be rendered; email can be verified

Category: Project source/configuration. Size: 2,096 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- email verification screen can be rendered (line 13)
- email can be verified (line 21)
- email is not verified with invalid hash (line 40)
- already verified user visiting verification link is redirected without firing event again (line 54)

<a id="file-ead00e8fcd5b"></a>
## tests/Feature/Auth/PasswordConfirmationTest.php

Automated regression coverage: confirm password screen can be rendered

Category: Project source/configuration. Size: 235 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- confirm password screen can be rendered (line 5)

<a id="file-5a82152246b9"></a>
## tests/Feature/Auth/PasswordResetTest.php

Automated regression coverage: reset password link screen can be rendered; reset password link can be requested

Category: Project source/configuration. Size: 1,792 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- reset password link screen can be rendered (line 12)
- reset password link can be requested (line 18)
- reset password screen can be rendered (line 28)
- password can be reset with valid token (line 44)

<a id="file-cf1819f2d9b4"></a>
## tests/Feature/Auth/RegistrationTest.php

Automated regression coverage: registration screen can be rendered; new users can register

Category: Project source/configuration. Size: 668 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- registration screen can be rendered (line 9)
- new users can register (line 15)

<a id="file-a2c8f70c1411"></a>
## tests/Feature/Auth/TwoFactorChallengeTest.php

Automated regression coverage: two factor challenge redirects to login when not authenticated; two factor challenge can be rendered

Category: Project source/configuration. Size: 741 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- two factor challenge redirects to login when not authenticated (line 10)
- two factor challenge can be rendered (line 16)

<a id="file-80f503e1d766"></a>
## tests/Feature/ContentSanitizationTest.php

Automated regression coverage: allowed page formatting is preserved; dangerous html and event attributes are removed

Category: Project source/configuration. Size: 3,014 bytes.


**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ContentSanitizer.php](#file-32886f1db6f6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- allowed page formatting is preserved (line 6)
- dangerous html and event attributes are removed (line 29)
- javascript links are removed (line 52)
- page model sanitizes content before storage (line 70)
- empty rich text markup is stored as null (line 88)
- encoded allowed html is decoded and safely sanitized (line 99)

<a id="file-7f7cd4403a59"></a>
## tests/Feature/DashboardTest.php

Automated regression coverage: guests are redirected to the login page; authorized users are redirected to the admin dashboard

Category: Project source/configuration. Size: 782 bytes.


**Uses:**
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- guests are redirected to the login page (line 10)
- authorized users are redirected to the admin dashboard (line 15)

<a id="file-163dc4f47194"></a>
## tests/Feature/ExampleTest.php

Automated regression coverage: returns a successful response

Category: Project source/configuration. Size: 134 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- returns a successful response (line 3)

<a id="file-08f4751d3ba9"></a>
## tests/Feature/GallerySinhalaTest.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 5,686 bytes.

Symbol: `Tests\Feature\GallerySinhalaTest` extends `Tests\TestCase`.
Public methods (source line): `test_manual_album_uses_one_image_set_and_localized_titles():60`, `test_service_saves_preserves_and_clears_sinhala_title():72`, `test_news_feed_is_filtered_by_language_before_pagination():85`, `test_news_language_switcher_links_only_eligible_translations():98`, `test_localized_news_detail_rejects_wrong_language_and_private_media():111`, `test_sinhala_pagination_keeps_locale_in_links():124`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding
- [tests/TestCase.php](#file-dc1b14d8259c) — PHP class/type reference, PHP import (declaration)

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-700c42e01b1a"></a>
## tests/Feature/GalleryUnpublishTest.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 2,843 bytes.

Symbol: `Tests\Feature\GalleryUnpublishTest` extends `Tests\TestCase`.
Public methods (source line): `test_unpublish_keeps_images_and_allows_republishing():22`, `test_unpublish_requires_publication_permission():45`, `test_legacy_unpublished_record_can_return_to_draft():58`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/GalleryService.php](#file-2ececd7ff7b6) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding
- [tests/TestCase.php](#file-dc1b14d8259c) — PHP class/type reference, PHP import (declaration)

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-6c9808fbe20c"></a>
## tests/Feature/HeroSlidePublicTest.php

Automated regression coverage: renders working hero page links when the website is installed in a subdirectory; keeps external hero button destinations unchanged

Category: Project source/configuration. Size: 6,143 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/HeroSlide.php](#file-c36ed5aa1b49) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [public/index.php](#file-0e61f67641b4) — filesystem path reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders working hero page links when the website is installed in a subdirectory (line 25)
- keeps external hero button destinations unchanged (line 64)
- renders hero slide content in the requested homepage language (line 76)
- uses english fallback and excludes inactive hero slides (line 129)

<a id="file-53679044bf35"></a>
## tests/Feature/NewsGalleryIntegrationTest.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 7,809 bytes.

Symbol: `Tests\Feature\NewsGalleryIntegrationTest` extends `Tests\TestCase`.
Public methods (source line): `test_news_and_independent_albums_share_one_ordered_feed():51`, `test_unticking_hides_the_album_without_deleting_news_images():74`, `test_draft_scheduled_deleted_and_empty_articles_are_hidden():84`, `test_nonpublic_and_deleted_media_are_excluded_from_feed_and_detail():101`, `test_news_title_and_images_stay_live_without_duplicate_gallery_records():125`, `test_combined_feed_is_paginated_instead_of_loading_every_album():140`, `test_gallery_preference_is_saved_and_snapshotted_by_article_service():152`.

**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsArticleService.php](#file-8695936ef235) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/NewsRevisionService.php](#file-06d5383c2edd) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PublicGalleryFeed.php](#file-31231bd51b5f) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding
- [tests/TestCase.php](#file-dc1b14d8259c) — PHP class/type reference, PHP import (declaration)

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-90cd0d72347c"></a>
## tests/Feature/NewsMultipleImagesTest.php

Automated regression coverage: public news renders attached images after the body in a four column desktop grid

Category: Project source/configuration. Size: 1,364 bytes.


**Uses:**
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- public news renders attached images after the body in a four column desktop grid (line 7)

<a id="file-9eea39f2fb47"></a>
## tests/Feature/PageBlockRenderingTest.php

Automated regression coverage: published page renders supported page builder blocks; page builder rich text is sanitized before public rendering

Category: Project source/configuration. Size: 8,398 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/seeders/RolesAndPermissionsSeeder.php](#file-78d32794773e) — PHP class/type reference, PHP import (declaration)
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- published page renders supported page builder blocks (line 8)
- page builder rich text is sanitized before public rendering (line 159)
- invalid unsafe blocks are ignored during public rendering (line 209)
- admin preview renders draft page builder blocks (line 281)
- page without builder blocks still renders main content (line 345)

<a id="file-a233a15c12f7"></a>
## tests/Feature/PageCustomCssRenderingTest.php

Automated regression coverage: renders scoped styles for the correct language and editor; omits invalid CSS from a manually modified database record

Category: Project source/configuration. Size: 1,629 bytes.


**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders scoped styles for the correct language and editor (line 6)
- omits invalid CSS from a manually modified database record (line 27)

<a id="file-1912a673eb68"></a>
## tests/Feature/PageHtmlContentTest.php

Automated regression coverage: preserves safe tailwind classes in page html; removes active content while keeping safe page html

Category: Project source/configuration. Size: 3,586 bytes.


**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/PageHtmlSanitizer.php](#file-33281f363390) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- preserves safe tailwind classes in page html (line 8)
- removes active content while keeping safe page html (line 26)
- page model stores sanitized tailwind html (line 48)
- published page renders sanitized tailwind html (line 64)
- visual editor preserves only its safe inline formatting styles (line 86)
- visual page model keeps safe visual styles after saving (line 106)

<a id="file-7a26d9e04789"></a>
## tests/Feature/PastChiefInstructorTest.php

Automated regression coverage: renders past chief instructors in both public languages; allows authorized administrators to create and delete past chief instructors

Category: Project source/configuration. Size: 3,204 bytes.


**Uses:**
- [app/Models/PastChiefInstructor.php](#file-fca0ee8b2525) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders past chief instructors in both public languages (line 30)
- allows authorized administrators to create and delete past chief instructors (line 63)

<a id="file-e999cb7856f9"></a>
## tests/Feature/PastCommandantTest.php

Automated regression coverage: renders the present commandant above past commandants in both public languages; allows authorized administrators to create and delete past commandants

Category: Project source/configuration. Size: 2,865 bytes.


**Uses:**
- [app/Models/PastCommandant.php](#file-2227ba16f07a) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders the present commandant above past commandants in both public languages (line 31)
- allows authorized administrators to create and delete past commandants (line 63)

<a id="file-6ae4dc4b1756"></a>
## tests/Feature/PastSergeantMajorTest.php

Automated regression coverage: renders sergeant major history chronologically with the current holder last; selects this appointment and legacy records while hiding inactive and unrelated leaders

Category: Project source/configuration. Size: 5,971 bytes.


**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders sergeant major history chronologically with the current holder last (line 33)
- selects this appointment and legacy records while hiding inactive and unrelated leaders (line 54)
- hides records belonging to an inactive appointment (line 67)
- shows an empty state and links the new page from the fallback navigation (line 76)
- links to enabled profiles in the selected language and keeps disabled profiles hidden (line 87)

<a id="file-78c165114ece"></a>
## tests/Feature/PublicActiveThemeTest.php

Automated regression coverage: uses the default public home when no active theme is configured; uses an installed active theme home view

Category: Project source/configuration. Size: 792 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- uses the default public home when no active theme is configured (line 7)
- uses an installed active theme home view (line 15)
- falls back safely when the configured theme is unavailable (line 23)

<a id="file-b191f82c0ffd"></a>
## tests/Feature/PublicContactTest.php

Automated regression coverage: guest can open contact page; guest can submit a contact message

Category: Project source/configuration. Size: 937 bytes.


**Uses:**
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- guest can open contact page (line 5)
- guest can submit a contact message (line 11)

<a id="file-e7408cc14eab"></a>
## tests/Feature/PublicDocumentTest.php

Automated regression coverage: guests can access the public document index; public document index only shows currently published documents

Category: Project source/configuration. Size: 20,939 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Enums/DocumentStatus.php](#file-1e2dbe3f43db) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaSource.php](#file-09b190a08482) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaType.php](#file-f394e04b3bee) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/MediaVisibility.php](#file-e9d006104665) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Document.php](#file-a5395ee5355b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/DocumentVersion.php](#file-dc28558361e9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- guests can access the public document index (line 116)
- public document index only shows currently published documents (line 128)
- guests can view a currently published document (line 180)
- draft documents cannot be viewed publicly (line 210)
- archived documents cannot be viewed publicly (line 222)
- future scheduled documents cannot be viewed publicly (line 235)
- published documents without a publication date cannot be viewed publicly (line 252)
- soft deleted published documents are hidden publicly (line 264)
- documents without a valid current version are hidden publicly (line 287)
- a private current pdf hides the document even when an older public version exists (line 306)
- a soft deleted current pdf hides the document publicly (line 351)
- guests can view the current public pdf inline (line 374)
- guests can download the current public pdf (line 391)
- view and download return not found when the physical pdf file is missing (line 408)
- replacing the current pdf keeps the public document urls stable (line 432)
- public document detail uses its title instead of legacy seo metadata (line 518)
- public document pages support Sinhala content and English fallback (line 549)
- public document index shows newer document dates first (line 627)

<a id="file-bf5fd8c3cddd"></a>
## tests/Feature/PublicGalleryTest.php

Automated regression coverage: guests can access the public gallery index; public gallery index only shows currently published galleries

Category: Project source/configuration. Size: 19,654 bytes.


**Uses:**
- [app/Enums/GalleryStatus.php](#file-7d3027533936) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Gallery.php](#file-87d2aaf50cbb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/GalleryImage.php](#file-cc816d7f9cf9) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- guests can access the public gallery index (line 76)
- public gallery index only shows currently published galleries (line 101)
- soft deleted published galleries are hidden from the public index (line 192)
- guests can view a currently published gallery (line 220)
- draft galleries cannot be viewed publicly (line 270)
- archived galleries cannot be viewed publicly (line 293)
- future scheduled published galleries cannot be viewed publicly (line 324)
- published galleries without a publication date cannot be viewed publicly (line 357)
- soft deleted published galleries cannot be viewed publicly (line 386)
- public gallery index renders the cover image and detail link (line 413)
- public gallery detail renders images in configured order (line 468)
- gallery image alt text follows the required fallback priority (line 549)
- public gallery detail renders custom seo metadata (line 635)
- public gallery detail falls back to gallery title and description for seo (line 702)
- public gallery index shows the most recently published galleries first (line 749)

<a id="file-bb2e2c731a20"></a>
## tests/Feature/PublicInterfaceLanguageTest.php

Automated regression coverage: localizes the public footer header contact section and navigation links; localizes news listings empty states and article controls

Category: Project source/configuration. Size: 9,375 bytes.


**Uses:**
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ContactMessage.php](#file-60b85836b768) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayoutRevision.php](#file-0becd2558476) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [database/migrations/2026_10_20_120000_localize_school_footer_content.php](#file-e96e95daa7de) — filesystem path reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- localizes the public footer header contact section and navigation links (line 29)
- localizes news listings empty states and article controls (line 46)
- localizes pagination on sinhala news pages (line 61)
- keeps contact forms validation and submission in sinhala (line 69)
- localizes the default public layout too (line 86)
- repairs the stored sinhala footer without changing english or custom content (line 93)
- localizes managed navigation placeholders using the requested locale (line 141)

<a id="file-0576177ff099"></a>
## tests/Feature/PublicNewsLanguageTest.php

Automated regression coverage: english sinhala and tamil news are rendered from manually entered versions; an unpublished news translation is not offered as an active language link

Category: Project source/configuration. Size: 4,519 bytes.


**Uses:**
- [app/Enums/NewsLocale.php](#file-61eca7d6e2d7) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- english sinhala and tamil news are rendered from manually entered versions (line 6)
- an unpublished news translation is not offered as an active language link (line 87)
- news index shows only the requested language (line 128)

<a id="file-83e4e9019901"></a>
## tests/Feature/PublicNewsTest.php

Automated regression coverage: guests can access the public news index; homepage shows the four newest published news articles for the active locale

Category: Project source/configuration. Size: 19,513 bytes.


**Uses:**
- [app/Enums/NewsStatus.php](#file-5c17bf9c3011) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/MediaAsset.php](#file-4712fd861864) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/NewsImage.php](#file-865f4356df79) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- guests can access the public news index (line 17)
- homepage shows the four newest published news articles for the active locale (line 33)
- homepage renders the selected featured image for published news (line 90)
- public news index only shows currently published articles (line 112)
- soft deleted published articles are hidden from the public index (line 259)
- guests can read a currently published news article (line 296)
- news detail renders the featured image before the body and all article images after it (line 340)
- draft articles cannot be viewed publicly (line 389)
- submitted articles cannot be viewed publicly (line 414)
- changes requested articles cannot be viewed publicly (line 439)
- approved articles cannot be viewed publicly before publication (line 464)
- archived articles cannot be viewed publicly (line 489)
- future scheduled published articles cannot be viewed publicly (line 520)
- published articles without a publication date cannot be viewed publicly (line 555)
- soft deleted published articles cannot be viewed publicly (line 586)
- public news detail renders article seo metadata (line 619)
- public news detail falls back to normal title and summary for seo (line 668)
- featured published news is prioritised on the public news index (line 717)

<a id="file-aeee5dd5126b"></a>
## tests/Feature/PublicPageLanguageTest.php

Automated regression coverage: english sinhala and tamil pages are rendered from manually entered versions; an unpublished translation is not offered as an active public language link

Category: Project source/configuration. Size: 3,411 bytes.


**Uses:**
- [app/Enums/PageLocale.php](#file-289fe689e2ea) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- english sinhala and tamil pages are rendered from manually entered versions (line 6)
- an unpublished translation is not offered as an active public language link (line 82)

<a id="file-e2f984c6764a"></a>
## tests/Feature/PublicPageRenderingTest.php

Automated regression coverage: published page can be viewed using its public slug; draft page is not publicly accessible

Category: Project source/configuration. Size: 2,849 bytes.


**Uses:**
- [app/Enums/PageStatus.php](#file-93d1c151c8ef) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- published page can be viewed using its public slug (line 6)
- draft page is not publicly accessible (line 25)
- submitted page is not publicly accessible (line 36)
- future scheduled publication is not publicly accessible (line 48)
- soft deleted published page is not publicly accessible (line 61)
- sanitized page html is rendered publicly (line 74)

<a id="file-43c114ebc463"></a>
## tests/Feature/PublicPageSeoTest.php

Automated regression coverage: published page renders configured seo metadata; page without custom seo metadata uses automatic fallbacks

Category: Project source/configuration. Size: 4,315 bytes.


**Uses:**
- [app/Models/Page.php](#file-b56bbabec185) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- published page renders configured seo metadata (line 5)
- page without custom seo metadata uses automatic fallbacks (line 99)
- page can instruct search engines not to index it (line 158)
- seo metadata is escaped safely (line 180)

<a id="file-7bf1009b45e8"></a>
## tests/Feature/PublicSchoolLeadershipTest.php

Automated regression coverage: features the current commandant and links to their profile from the homepage; shows active current school appointments including newly added types

Category: Project source/configuration. Size: 5,853 bytes.


**Uses:**
- [app/Models/SchoolLeader.php](#file-7f28db275867) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SchoolLeadershipPosition.php](#file-9c3415918b4c) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- features the current commandant and links to their profile from the homepage (line 32)
- shows active current school appointments including newly added types (line 50)
- opens the current commandant profile and preserves the selected language (line 81)
- keeps the leadership link available when the commandant is hidden on the homepage (line 105)
- does not expose inactive appointments or unsupported profile languages (line 119)

<a id="file-5abd44a4a7b4"></a>
## tests/Feature/Settings/ProfileUpdateTest.php

Automated regression coverage: profile page is displayed; profile information can be updated

Category: Project source/configuration. Size: 1,980 bytes.


**Uses:**
- [app/Livewire/Settings/Profile.php](#file-8000c38dd596) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- profile page is displayed (line 7)
- profile information can be updated (line 13)
- email verification status is unchanged when email address is unchanged (line 32)
- user can delete their account (line 47)
- settings.delete-user-form (line 52)
- correct password must be provided to delete account (line 64)
- settings.delete-user-form (line 69)

<a id="file-4a00d81e65e8"></a>
## tests/Feature/Settings/SecurityTest.php

Automated regression coverage: security settings page can be rendered; security settings page requires password confirmation when enabled

Category: Project source/configuration. Size: 3,392 bytes.


**Uses:**
- [app/Livewire/Settings/Security.php](#file-2ba603ddc070) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/User.php](#file-a90f2e50cb06) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/settings.php](#file-ef358245ae87) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- security settings page can be rendered (line 21)
- security settings page requires password confirmation when enabled (line 36)
- security settings page renders without two factor when feature is disabled (line 45)
- two factor authentication disabled when confirmation abandoned between requests (line 60)
- password can be updated (line 82)
- correct password must be provided to update password (line 100)

<a id="file-fbcf4d4be19b"></a>
## tests/Feature/ThemeLayoutPublicIntegrationTest.php

Automated regression coverage: keeps the original theme header and footer without published layouts; ignores draft layouts on the public website

Category: Project source/configuration. Size: 3,602 bytes.


**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- keeps the original theme header and footer without published layouts (line 24)
- ignores draft layouts on the public website (line 32)
- renders published managed header footer and sanitized css (line 59)

<a id="file-e0d014980ddd"></a>
## tests/Feature/ThemeLayoutRendererLocaleTest.php

Automated regression coverage: renders the published layout for the requested language; falls back to the published english layout when sinhala is unavailable

Category: Project source/configuration. Size: 6,553 bytes.


**Uses:**
- [app/Enums/ThemeLayoutLocale.php](#file-ce4e9dedc3a3) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Enums/ThemeLayoutStatus.php](#file-21966b2dd730) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Models/ThemeLayout.php](#file-839afe994d82) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- renders the published layout for the requested language (line 12)
- falls back to the published english layout when sinhala is unavailable (line 54)
- ignores a draft translation and uses the published english fallback (line 85)
- returns null when neither the requested nor english layout is published (line 125)
- renders the secure mobile menu toggle placeholder (line 151)

<a id="file-c6948e53b7c2"></a>
## tests/Feature/ThemeManagerTest.php

Automated regression coverage: theme manager discovers the school of signals manifest; theme manager rejects an unsafe theme slug

Category: Project source/configuration. Size: 596 bytes.


**Uses:**
- [app/Services/ThemeManager.php](#file-57c809432778) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- theme manager discovers the school of signals manifest (line 5)
- theme manager rejects an unsafe theme slug (line 15)

<a id="file-d1928a9edd00"></a>
## tests/Feature/ThemeViewResolverTest.php

Automated regression coverage: resolves installed active theme views; returns null when no active theme is configured

Category: Project source/configuration. Size: 988 bytes.


**Uses:**
- [app/Services/ThemeViewResolver.php](#file-5049505de5bb) — PHP class/type reference, PHP import (declaration), fully qualified class reference
- [tests/Pest.php](#file-faf20a22ebaf) — Pest Feature test binding
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — active-theme view candidate
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — active-theme view candidate

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- resolves installed active theme views (line 5)
- returns null when no active theme is configured (line 14)
- rejects missing and unsafe theme views (line 20)

<a id="file-faf20a22ebaf"></a>
## tests/Pest.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 1,618 bytes.


**Uses:**
- [tests/TestCase.php](#file-dc1b14d8259c) — PHP class/type reference, PHP import (declaration), Pest base test class

**Used by:**
- [tests/Feature/Admin/AdminAccessTest.php](#file-d1364c3b48d4) — Pest Feature test binding
- [tests/Feature/Admin/AdminLayoutTest.php](#file-61561546aa52) — Pest Feature test binding
- [tests/Feature/Admin/ArchiveRetirementTest.php](#file-3d1b5be9719e) — Pest Feature test binding
- [tests/Feature/Admin/AuditLogViewerTest.php](#file-15860e68fa09) — Pest Feature test binding
- [tests/Feature/Admin/AuditLoggingTest.php](#file-ff3731f0ea22) — Pest Feature test binding
- [tests/Feature/Admin/Documents/DocumentCategoryServiceTest.php](#file-cbb07bdddbcb) — Pest Feature test binding
- [tests/Feature/Admin/Documents/DocumentCategoryUiTest.php](#file-419e4044300e) — Pest Feature test binding
- [tests/Feature/Admin/Documents/DocumentLivewireTest.php](#file-4474786fd2c7) — Pest Feature test binding
- [tests/Feature/Admin/Documents/DocumentServiceTest.php](#file-ddd73f6cbf01) — Pest Feature test binding
- [tests/Feature/Admin/Galleries/GalleryCoverInvariantTest.php](#file-6ee1999556a7) — Pest Feature test binding
- [tests/Feature/Admin/Galleries/GalleryLivewireTest.php](#file-ef63a914f6a6) — Pest Feature test binding
- [tests/Feature/Admin/Galleries/GallerySecurityTest.php](#file-295658b83713) — Pest Feature test binding
- [tests/Feature/Admin/Galleries/GalleryServiceTest.php](#file-d9bfc5e25fe7) — Pest Feature test binding
- [tests/Feature/Admin/HeroSlideAdminUiTest.php](#file-c29a742c88d8) — Pest Feature test binding
- [tests/Feature/Admin/HeroSlideTranslationModelTest.php](#file-c2d127affbdc) — Pest Feature test binding
- [tests/Feature/Admin/MediaAutomaticVariantTest.php](#file-d4bd54bc3f50) — Pest Feature test binding
- [tests/Feature/Admin/MediaDeletionServiceTest.php](#file-186e3a771026) — Pest Feature test binding
- [tests/Feature/Admin/MediaEditTest.php](#file-f10adbb3868c) — Pest Feature test binding
- [tests/Feature/Admin/MediaFoundationTest.php](#file-5c9085acd019) — Pest Feature test binding
- [tests/Feature/Admin/MediaImageVariantTest.php](#file-d44d7ca47b7c) — Pest Feature test binding
- [tests/Feature/Admin/MediaIndexDeletionUiTest.php](#file-689bc91f5112) — Pest Feature test binding
- [tests/Feature/Admin/MediaIndexTest.php](#file-f604286cfa46) — Pest Feature test binding
- [tests/Feature/Admin/MediaReplacementServiceTest.php](#file-c65f02eb5046) — Pest Feature test binding
- [tests/Feature/Admin/MediaReplacementUiTest.php](#file-2f28a15a4bd6) — Pest Feature test binding
- [tests/Feature/Admin/MediaUploadServiceTest.php](#file-7f31096236ad) — Pest Feature test binding
- [tests/Feature/Admin/MediaUploadUiTest.php](#file-44589dae6f75) — Pest Feature test binding
- [tests/Feature/Admin/MenuServiceTest.php](#file-8c93247a4c08) — Pest Feature test binding
- [tests/Feature/Admin/MenuTranslationServiceTest.php](#file-49da0724cc08) — Pest Feature test binding
- [tests/Feature/Admin/News/NewsEditorModeTest.php](#file-889047e3bfd5) — Pest Feature test binding
- [tests/Feature/Admin/News/NewsSimplifiedActionsTest.php](#file-a30056491650) — Pest Feature test binding
- [tests/Feature/Admin/News/NewsTranslationManagementTest.php](#file-c8e3fe8a6628) — Pest Feature test binding
- [tests/Feature/Admin/NewsArticleServiceTest.php](#file-416b3fa74f8d) — Pest Feature test binding
- [tests/Feature/Admin/NewsCategoryServiceTest.php](#file-f0a5049c6b3e) — Pest Feature test binding
- [tests/Feature/Admin/NewsCategoryUiTest.php](#file-7df7d018446c) — Pest Feature test binding
- [tests/Feature/Admin/NewsRevisionServiceTest.php](#file-de5ca9a88bc8) — Pest Feature test binding
- [tests/Feature/Admin/NewsUiTest.php](#file-d840e7f01131) — Pest Feature test binding
- [tests/Feature/Admin/NewsWorkflowServiceTest.php](#file-2f41b321ac01) — Pest Feature test binding
- [tests/Feature/Admin/PageBlockFoundationTest.php](#file-82dd53475672) — Pest Feature test binding
- [tests/Feature/Admin/PageBuilderBackendTest.php](#file-7081eaced100) — Pest Feature test binding
- [tests/Feature/Admin/PageBuilderUiTest.php](#file-5cb4a7f210e3) — Pest Feature test binding
- [tests/Feature/Admin/PageCreateTest.php](#file-480c5674afee) — Pest Feature test binding
- [tests/Feature/Admin/PageCustomCssTest.php](#file-2e3e81e7e226) — Pest Feature test binding
- [tests/Feature/Admin/PageDeletionTest.php](#file-a0b9b38d5ed9) — Pest Feature test binding
- [tests/Feature/Admin/PageEditTest.php](#file-ddcc07423d2a) — Pest Feature test binding
- [tests/Feature/Admin/PageEditorModeTest.php](#file-400571fb9d80) — Pest Feature test binding
- [tests/Feature/Admin/PageFoundationTest.php](#file-7fc9257c3083) — Pest Feature test binding
- [tests/Feature/Admin/PageIndexTest.php](#file-b7607c93af4c) — Pest Feature test binding
- [tests/Feature/Admin/PagePreviewTest.php](#file-2d1c809d0d3c) — Pest Feature test binding
- [tests/Feature/Admin/PageRevisionFoundationTest.php](#file-d239b175d9cd) — Pest Feature test binding
- [tests/Feature/Admin/PageRevisionHistoryTest.php](#file-963b4d81b749) — Pest Feature test binding
- [tests/Feature/Admin/PageSeoFormTest.php](#file-cee52032b398) — Pest Feature test binding
- [tests/Feature/Admin/PageSeoFoundationTest.php](#file-256dd99b18f3) — Pest Feature test binding
- [tests/Feature/Admin/PageTranslationManagementTest.php](#file-144babc8a862) — Pest Feature test binding
- [tests/Feature/Admin/PageWorkflowTest.php](#file-36edcccee3fe) — Pest Feature test binding
- [tests/Feature/Admin/RedirectManagerTest.php](#file-332bb5013c6b) — Pest Feature test binding
- [tests/Feature/Admin/RolePermissionManagementTest.php](#file-82b88f2fd69a) — Pest Feature test binding
- [tests/Feature/Admin/SchoolLeadershipDeleteTest.php](#file-c46d2e172d25) — Pest Feature test binding
- [tests/Feature/Admin/SchoolLeadershipModalTest.php](#file-d05ff34cc065) — Pest Feature test binding
- [tests/Feature/Admin/SchoolLeadershipProfileTest.php](#file-1961d571f891) — Pest Feature test binding
- [tests/Feature/Admin/SchoolModulePermissionUiTest.php](#file-5493846d3c60) — Pest Feature test binding
- [tests/Feature/Admin/SiteManagementFoundationTest.php](#file-53397044bc14) — Pest Feature test binding
- [tests/Feature/Admin/ThemeLayoutAdminUiTest.php](#file-df50d49fcb12) — Pest Feature test binding
- [tests/Feature/Admin/ThemeLayoutModelTest.php](#file-e307f4b73342) — Pest Feature test binding
- [tests/Feature/Admin/ThemeLayoutSanitizerTest.php](#file-dc469ecd3bdf) — Pest Feature test binding
- [tests/Feature/Admin/ThemeLayoutServiceTest.php](#file-f293f9cdc665) — Pest Feature test binding
- [tests/Feature/Admin/UserActionAuditTest.php](#file-c7a37ba659ca) — Pest Feature test binding
- [tests/Feature/Admin/UserManagementFormTest.php](#file-29370412f779) — Pest Feature test binding
- [tests/Feature/Admin/UserManagementIndexTest.php](#file-e113722c846b) — Pest Feature test binding
- [tests/Feature/Admin/UserPermissionBoundariesTest.php](#file-22dca52ec16f) — Pest Feature test binding
- [tests/Feature/Admin/UserSecurityTest.php](#file-0a8c21330d85) — Pest Feature test binding
- [tests/Feature/Auth/AuthenticationTest.php](#file-ad5adf97ee88) — Pest Feature test binding
- [tests/Feature/Auth/EmailVerificationTest.php](#file-a975c7792e9d) — Pest Feature test binding
- [tests/Feature/Auth/PasswordConfirmationTest.php](#file-ead00e8fcd5b) — Pest Feature test binding
- [tests/Feature/Auth/PasswordResetTest.php](#file-5a82152246b9) — Pest Feature test binding
- [tests/Feature/Auth/RegistrationTest.php](#file-cf1819f2d9b4) — Pest Feature test binding
- [tests/Feature/Auth/TwoFactorChallengeTest.php](#file-a2c8f70c1411) — Pest Feature test binding
- [tests/Feature/ContentSanitizationTest.php](#file-80f503e1d766) — Pest Feature test binding
- [tests/Feature/DashboardTest.php](#file-7f7cd4403a59) — Pest Feature test binding
- [tests/Feature/ExampleTest.php](#file-163dc4f47194) — Pest Feature test binding
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — Pest Feature test binding
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — Pest Feature test binding
- [tests/Feature/HeroSlidePublicTest.php](#file-6c9808fbe20c) — Pest Feature test binding
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — Pest Feature test binding
- [tests/Feature/NewsMultipleImagesTest.php](#file-90cd0d72347c) — Pest Feature test binding
- [tests/Feature/PageBlockRenderingTest.php](#file-9eea39f2fb47) — Pest Feature test binding
- [tests/Feature/PageCustomCssRenderingTest.php](#file-a233a15c12f7) — Pest Feature test binding
- [tests/Feature/PageHtmlContentTest.php](#file-1912a673eb68) — Pest Feature test binding
- [tests/Feature/PastChiefInstructorTest.php](#file-7a26d9e04789) — Pest Feature test binding
- [tests/Feature/PastCommandantTest.php](#file-e999cb7856f9) — Pest Feature test binding
- [tests/Feature/PastSergeantMajorTest.php](#file-6ae4dc4b1756) — Pest Feature test binding
- [tests/Feature/PublicActiveThemeTest.php](#file-78c165114ece) — Pest Feature test binding
- [tests/Feature/PublicContactTest.php](#file-b191f82c0ffd) — Pest Feature test binding
- [tests/Feature/PublicDocumentTest.php](#file-e7408cc14eab) — Pest Feature test binding
- [tests/Feature/PublicGalleryTest.php](#file-bf5fd8c3cddd) — Pest Feature test binding
- [tests/Feature/PublicInterfaceLanguageTest.php](#file-bb2e2c731a20) — Pest Feature test binding
- [tests/Feature/PublicNewsLanguageTest.php](#file-0576177ff099) — Pest Feature test binding
- [tests/Feature/PublicNewsTest.php](#file-83e4e9019901) — Pest Feature test binding
- [tests/Feature/PublicPageLanguageTest.php](#file-aeee5dd5126b) — Pest Feature test binding
- [tests/Feature/PublicPageRenderingTest.php](#file-e2f984c6764a) — Pest Feature test binding
- [tests/Feature/PublicPageSeoTest.php](#file-43c114ebc463) — Pest Feature test binding
- [tests/Feature/PublicSchoolLeadershipTest.php](#file-7bf1009b45e8) — Pest Feature test binding
- [tests/Feature/Settings/ProfileUpdateTest.php](#file-5abd44a4a7b4) — Pest Feature test binding
- [tests/Feature/Settings/SecurityTest.php](#file-4a00d81e65e8) — Pest Feature test binding
- [tests/Feature/ThemeLayoutPublicIntegrationTest.php](#file-fbcf4d4be19b) — Pest Feature test binding
- [tests/Feature/ThemeLayoutRendererLocaleTest.php](#file-e0d014980ddd) — Pest Feature test binding
- [tests/Feature/ThemeManagerTest.php](#file-c6948e53b7c2) — Pest Feature test binding
- [tests/Feature/ThemeViewResolverTest.php](#file-d1928a9edd00) — Pest Feature test binding

<a id="file-dc1b14d8259c"></a>
## tests/TestCase.php

Automated regression coverage; shared test setup/base class.

Category: Project source/configuration. Size: 545 bytes.

Symbol: `Tests\TestCase` extends `Illuminate\Foundation\Testing\TestCase`.

**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [tests/Feature/GallerySinhalaTest.php](#file-08f4751d3ba9) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/GalleryUnpublishTest.php](#file-700c42e01b1a) — PHP class/type reference, PHP import (declaration)
- [tests/Feature/NewsGalleryIntegrationTest.php](#file-53679044bf35) — PHP class/type reference, PHP import (declaration)
- [tests/Pest.php](#file-faf20a22ebaf) — PHP class/type reference, PHP import (declaration), Pest base test class

<a id="file-eb919dafbbe8"></a>
## tests/Unit/ExampleTest.php

Automated regression coverage: that true is true

Category: Project source/configuration. Size: 81 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- that true is true (line 3)

<a id="file-4412c57413d4"></a>
## tests/Unit/PageCssTest.php

Automated regression coverage: scopes course selectors and preserves responsive rules and declaration values; handles commas and punctuation inside selectors functions comments and strings

Category: Project source/configuration. Size: 3,891 bytes.


**Uses:**
- [app/Services/PageCss.php](#file-d33f6597c9ec) — PHP class/type reference, PHP import (declaration), fully qualified class reference

**Used by:**
No static references detected; this is not proof of non-use.

Declared tests:
- scopes course selectors and preserves responsive rules and declaration values (line 5)
- handles commas and punctuation inside selectors functions comments and strings (line 32)
- rejects invalid or active CSS and fails closed when rendering (line 53)
- rejects oversized CSS but accepts empty optional CSS (line 78)

<a id="file-ad82ea274b07"></a>
## themes/school-of-signals/assets/css/navbar.css

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 12,259 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/css/navbar.css` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-f5b1d37cb881"></a>
## themes/school-of-signals/assets/css/theme.css

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 117,054 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/css/theme.css` — published asset copy
- [themes/school-of-signals/assets/fonts/Exo2-Regular.woff2](#file-340a7247a290) — JS/CSS import or source
- [themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2](#file-5436c94b6541) — JS/CSS import or source

**Used by:**
- [app/Services/LeadershipProfileContent.php](#file-5192f65a3932) — filesystem path reference, literal project path
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [scripts/build-2026-10-21-hero-link-deployment.py](#file-cff8ad197a2e) — literal project path
- [themes/school-of-signals/theme.json](#file-f2438d9aace3) — theme manifest asset
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — literal project path

<a id="file-340a7247a290"></a>
## themes/school-of-signals/assets/fonts/Exo2-Regular.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 16,720 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/Exo2-Regular.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — JS/CSS import or source

<a id="file-5436c94b6541"></a>
## themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 42,228 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — JS/CSS import or source

<a id="file-f8bb44ba029b"></a>
## themes/school-of-signals/assets/fonts/fontawesome-7/css/all.min.css

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 90,169 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/fontawesome-7/css/all.min.css` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-56f9e0e187a4"></a>
## themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-brands-400.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 114,412 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-brands-400.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-748071dc7688"></a>
## themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-regular-400.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 19,480 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-regular-400.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-4db58b2ad5bc"></a>
## themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-solid-900.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 119,156 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-solid-900.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-a3b93e13dc8d"></a>
## themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-v4compatibility.woff2

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 4,204 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-v4compatibility.woff2` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source

<a id="file-14a8c2987a0e"></a>
## themes/school-of-signals/assets/images/image-placeholder.svg

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 1,321 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/images/image-placeholder.svg` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — literal project path

<a id="file-5704d9c17561"></a>
## themes/school-of-signals/assets/images/logo.png

Font/image/icon asset; direct file references හා public/source consumers බලන්න.

Category: Project source/configuration. Size: 26,778 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/images/logo.png` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [themes/school-of-signals/views/partials/commandant-feature.blade.php](#file-e3a71403d4f8) — literal project path

<a id="file-224a28a1e76c"></a>
## themes/school-of-signals/assets/js/theme.js

Frontend styling/behavior source; imports, Blade consumers සහ build/published copies බලන්න.

Category: Project source/configuration. Size: 19,638 bytes.


**Uses:**
- `public/themes/school-of-signals/assets/js/theme.js` — published asset copy

**Used by:**
- [app/Services/ThemeAssetPublisher.php](#file-c218aac66c8b) — theme asset source
- [themes/school-of-signals/theme.json](#file-f2438d9aace3) — theme manifest asset
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — literal project path

<a id="file-ed8819e01b29"></a>
## themes/school-of-signals/lang/en/home.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 5,117 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Providers/ThemeServiceProvider.php](#file-63fabea05421) — translation resource discovery
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-956065fd9ae9"></a>
## themes/school-of-signals/lang/si/home.php

Localization resource; selected locale/namespace අනුව translation keys ලබාදෙයි.

Category: Project source/configuration. Size: 8,702 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [app/Providers/ThemeServiceProvider.php](#file-63fabea05421) — translation resource discovery
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path

<a id="file-f2438d9aace3"></a>
## themes/school-of-signals/theme.json

Theme manifest: slug, supported modules, default views සහ public asset source paths.

Category: Project source/configuration. Size: 567 bytes.


**Uses:**
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — theme manifest asset
- [themes/school-of-signals/assets/js/theme.js](#file-224a28a1e76c) — theme manifest asset
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — theme manifest view
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — theme manifest view

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-0c1ce9f7fd0d"></a>
## themes/school-of-signals/views/commandant.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,578 bytes.


**Uses:**
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends
- [themes/school-of-signals/views/partials/commandant-feature.blade.php](#file-e3a71403d4f8) — Blade include
- [themes/school-of-signals/views/partials/leadership-profile-content.blade.php](#file-41441cce0e05) — Blade include

**Used by:**
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-16d30421a085"></a>
## themes/school-of-signals/views/history/past-commandants.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 6,229 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
- [app/Http/Controllers/PastCommandantController.php](#file-188a3669d996) — active-theme view candidate
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-4578f3263b06"></a>
## themes/school-of-signals/views/home.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 24,256 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- `public/themes/school-of-signals/assets/images/image-placeholder.svg` — public asset URL
- `public/themes/school-of-signals/assets/images/slsc_about.png` — public asset URL
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/assets/images/image-placeholder.svg](#file-14a8c2987a0e) — literal project path
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends
- [themes/school-of-signals/views/partials/commandant-feature.blade.php](#file-e3a71403d4f8) — Blade include

**Used by:**
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path
- [scripts/build-2026-10-21-hero-link-deployment.py](#file-cff8ad197a2e) — literal project path
- [tests/Feature/ThemeViewResolverTest.php](#file-d1928a9edd00) — active-theme view candidate
- [themes/school-of-signals/theme.json](#file-f2438d9aace3) — theme manifest view

<a id="file-bd2672812d58"></a>
## themes/school-of-signals/views/layout.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 20,879 bytes.

Config reads: `app.name`, `awcms.theme_locales.school-of-signals`.

**Uses:**
- [app/Enums/ThemeLayoutRegion.php](#file-623139e20943) — fully qualified class reference
- [app/Services/GalleryLocale.php](#file-937f20b58173) — fully qualified class reference
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [app/Services/ThemeLayoutRenderer.php](#file-371dbeaa302b) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [public/logo.png](#file-c3d77d14bbef) — public asset URL
- `public/themes/school-of-signals/assets` — public asset URL
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- `themes/school-of-signals/assets` — literal project path
- [themes/school-of-signals/assets/css/theme.css](#file-f5b1d37cb881) — literal project path
- [themes/school-of-signals/assets/js/theme.js](#file-224a28a1e76c) — literal project path

**Used by:**
- [resources/views/admin/news/preview.blade.php](#file-0b76d7cd09a9) — Blade extends
- [resources/views/public/history/past-chief-instructors.blade.php](#file-efe1f99ce875) — Blade extends
- [resources/views/public/history/past-sgt-major.blade.php](#file-83fd2c429c70) — Blade extends
- [resources/views/public/news/index.blade.php](#file-bf06929a3544) — Blade extends
- [scripts/build-2026-10-21-deployment.py](#file-875dd493e5fe) — literal project path
- [themes/school-of-signals/theme.json](#file-f2438d9aace3) — theme manifest view
- [themes/school-of-signals/views/commandant.blade.php](#file-0c1ce9f7fd0d) — Blade extends
- [themes/school-of-signals/views/history/past-commandants.blade.php](#file-16d30421a085) — Blade extends
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — Blade extends
- [themes/school-of-signals/views/leader-profile.blade.php](#file-6843e7ac23ba) — Blade extends
- [themes/school-of-signals/views/news/index.blade.php](#file-8fdadeebde29) — Blade extends
- [themes/school-of-signals/views/news/show.blade.php](#file-6be5b6aa2625) — Blade extends
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — Blade extends
- [themes/school-of-signals/views/school-leadership.blade.php](#file-81978c1aae5b) — Blade extends

<a id="file-6843e7ac23ba"></a>
## themes/school-of-signals/views/leader-profile.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 2,837 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends
- [themes/school-of-signals/views/partials/leadership-profile-content.blade.php](#file-41441cce0e05) — Blade include

**Used by:**
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-8fdadeebde29"></a>
## themes/school-of-signals/views/news/index.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 6,225 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-6be5b6aa2625"></a>
## themes/school-of-signals/views/news/show.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 18,994 bytes.

Config reads: `app.name`, `awcms.theme_locales.school-of-signals`.

**Uses:**
- [app/Enums/NewsEditorMode.php](#file-bf209172286b) — fully qualified class reference
- [app/Http/Controllers/PublicNewsController.php](#file-21bb7cdf2247) — fully qualified class reference
- [app/Models/News.php](#file-9584cdf2eac3) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
No static references detected; this is not proof of non-use.

<a id="file-afa015b754b4"></a>
## themes/school-of-signals/views/pages/show.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 4,889 bytes.

Config reads: `awcms.theme_locales.school-of-signals`.

**Uses:**
- [app/Enums/PageEditorMode.php](#file-394b30936172) — fully qualified class reference
- [config/awcms.php](#file-2eb8ad7c121f) — configuration read
- [resources/views/components/page/blocks.blade.php](#file-a53b4baa5c7e) — Blade component
- [resources/views/components/page/custom-styles.blade.php](#file-8e69b1288783) — Blade component
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends
- [themes/school-of-signals/views/partials/cms-contact.blade.php](#file-17b45718646c) — Blade PHP view reference

**Used by:**
- [app/Http/Controllers/PublicPageController.php](#file-8c7b58255701) — active-theme view candidate
- [scripts/build-2026-10-21-page-css-deployment.py](#file-46855021424b) — literal project path
- [tests/Feature/ThemeViewResolverTest.php](#file-d1928a9edd00) — active-theme view candidate

<a id="file-17b45718646c"></a>
## themes/school-of-signals/views/partials/cms-contact.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 8,742 bytes.

Config reads: `app.name`.

**Uses:**
- [app/Models/SiteSetting.php](#file-bb56abed8eb0) — fully qualified class reference
- [config/app.php](#file-734f7be25e80) — configuration read
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference

**Used by:**
- [themes/school-of-signals/views/pages/show.blade.php](#file-afa015b754b4) — Blade PHP view reference

<a id="file-e3a71403d4f8"></a>
## themes/school-of-signals/views/partials/commandant-feature.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 3,673 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- `public/themes/school-of-signals/assets/images/logo.png` — public asset URL
- [themes/school-of-signals/assets/images/logo.png](#file-5704d9c17561) — literal project path

**Used by:**
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [themes/school-of-signals/views/commandant.blade.php](#file-0c1ce9f7fd0d) — Blade include
- [themes/school-of-signals/views/home.blade.php](#file-4578f3263b06) — Blade include

<a id="file-41441cce0e05"></a>
## themes/school-of-signals/views/partials/leadership-profile-content.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 286 bytes.


**Uses:**
No static references detected; this is not proof of non-use.

**Used by:**
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path
- [themes/school-of-signals/views/commandant.blade.php](#file-0c1ce9f7fd0d) — Blade include
- [themes/school-of-signals/views/leader-profile.blade.php](#file-6843e7ac23ba) — Blade include

<a id="file-81978c1aae5b"></a>
## themes/school-of-signals/views/school-leadership.blade.php

Theme Blade view; layout/includes/data producers සහ route links පහත දැක්වේ.

Category: Project source/configuration. Size: 5,738 bytes.


**Uses:**
- [app/Services/MediaUrlService.php](#file-145ef1119a77) — fully qualified class reference
- [routes/web.php](#file-6b9a783d9b43) — named route URL/reference
- [themes/school-of-signals/views/layout.blade.php](#file-bd2672812d58) — Blade extends

**Used by:**
- [app/Http/Controllers/PublicSchoolLeadershipController.php](#file-49482468b2fa) — renders/layout view
- [scripts/build-2026-10-20-deployment.py](#file-d995a9a02eed) — literal project path

<a id="file-23e488f27597"></a>
## vite.config.js

CSS/app/passkeys entry points, Tailwind plugin සහ font build configuration.

Category: Project source/configuration. Size: 747 bytes.


**Uses:**
- [package.json](#file-7030d0b2f71b) — Node package dependency
- `public/build` — compiled frontend output
- [resources/css/app.css](#file-fb832949a883) — literal project path
- [resources/js/app.js](#file-857628e89e6d) — literal project path
- [resources/js/passkeys.js](#file-2b3ec58aec9b) — literal project path

**Used by:**
- [package.json](#file-7030d0b2f71b) — frontend build command


# Appendix E — PHP package lock

| Package | Version | Scope | Purpose |
| --- | --- | --- | --- |
| bacon/bacon-qr-code | v3.1.1 | runtime | BaconQrCode is a QR code generator for PHP. |
| brick/math | 0.18.0 | runtime | Arbitrary-precision arithmetic library |
| carbonphp/carbon-doctrine-types | 3.2.0 | runtime | Types to use Carbon in Doctrine |
| dasprid/enum | 1.0.7 | runtime | PHP 7.1 enum implementation |
| dflydev/dot-access-data | v3.0.3 | runtime | Given a deep data structure, access data by dot notation. |
| doctrine/deprecations | 1.1.6 | runtime | A small layer on top of trigger_error(E_USER_DEPRECATED) or PSR-3 logging with options to disable all deprecations or selectively for packages. |
| doctrine/inflector | 2.1.0 | runtime | PHP Doctrine Inflector is a small library that can perform string manipulations with regard to upper/lowercase and singular/plural forms of words. |
| doctrine/lexer | 3.0.1 | runtime | PHP Doctrine Lexer parser library that can be used in Top-Down, Recursive Descent Parsers. |
| dragonmantank/cron-expression | v3.6.0 | runtime | CRON for PHP: Calculate the next or previous run date and determine if a CRON expression is due |
| egulias/email-validator | 4.0.4 | runtime | A library for validating emails against several RFCs |
| ezyang/htmlpurifier | v4.19.0 | runtime | Standards compliant HTML filter written in PHP |
| fruitcake/php-cors | v1.4.0 | runtime | Cross-origin resource sharing library for the Symfony HttpFoundation |
| graham-campbell/result-type | v1.1.4 | runtime | An Implementation Of The Result Type |
| guzzlehttp/guzzle | 7.15.2 | runtime | Guzzle is a PHP HTTP client library |
| guzzlehttp/promises | 2.5.1 | runtime | Guzzle promises library |
| guzzlehttp/psr7 | 2.13.0 | runtime | PSR-7 message implementation that also provides common utility methods |
| guzzlehttp/uri-template | v1.0.10 | runtime | A polyfill class for uri_template of PHP |
| intervention/gif | 5.0.1 | runtime | PHP GIF Encoder/Decoder |
| intervention/image | 4.0.0 | runtime | PHP Image Processing |
| laravel/chisel | v0.1.1 | runtime | Toolkit for building scripts that remove unwanted code, files, and dependencies. |
| laravel/fortify | v1.37.3 | runtime | Backend controllers and scaffolding for Laravel authentication. |
| laravel/framework | v13.23.0 | runtime | The Laravel Framework. |
| laravel/passkeys | v0.2.1 | runtime | Passwordless authentication using WebAuthn/passkeys for Laravel |
| laravel/prompts | v0.3.21 | runtime | Add beautiful and user-friendly forms to your command-line applications. |
| laravel/serializable-closure | v2.0.15 | runtime | Laravel Serializable Closure provides an easy and secure way to serialize closures in PHP. |
| laravel/tinker | v3.0.2 | runtime | Powerful REPL for the Laravel framework. |
| league/commonmark | 2.8.3 | runtime | Highly-extensible PHP Markdown parser which fully supports the CommonMark spec and GitHub-Flavored Markdown (GFM) |
| league/config | v1.2.0 | runtime | Define configuration arrays with strict schemas and access values with dot notation |
| league/flysystem | 3.35.2 | runtime | File storage abstraction for PHP |
| league/flysystem-local | 3.31.0 | runtime | Local filesystem adapter for Flysystem. |
| league/mime-type-detection | 1.17.0 | runtime | Mime-type detection for Flysystem |
| league/uri | 7.8.1 | runtime | URI manipulation library |
| league/uri-interfaces | 7.8.1 | runtime | Common tools for parsing and resolving RFC3987/RFC3986 URI |
| livewire/blaze | v1.0.13 | runtime | A tool for optimizing Blade component performance by folding them into parent templates |
| livewire/flux | v2.15.0 | runtime | The official UI component library for Livewire. |
| livewire/livewire | v4.3.3 | runtime | A front-end framework for Laravel. |
| monolog/monolog | 3.10.0 | runtime | Sends your logs to files, sockets, inboxes, databases and various web services |
| nesbot/carbon | 3.13.1 | runtime | An API extension for DateTime that supports 281 different languages. |
| nette/schema | v1.3.5 | runtime | 📐 Nette Schema: validating data structures against a given Schema. |
| nette/utils | v4.1.5 | runtime | 🛠  Nette Utils: lightweight utilities for string & array manipulation, image handling, safe JSON encoding/decoding, validation, slug or strong password generating etc. |
| nikic/php-parser | v5.8.0 | runtime | A PHP parser written in PHP |
| nunomaduro/termwind | v2.4.0 | runtime | It's like Tailwind CSS, but for the console. |
| paragonie/constant_time_encoding | v3.1.3 | runtime | Constant-time Implementations of RFC 4648 Encoding (Base-64, Base-32, Base-16) |
| phpdocumentor/reflection-common | 2.2.0 | runtime | Common reflection classes used by phpdocumentor to reflect the code structure |
| phpdocumentor/reflection-docblock | 6.0.3 | runtime | With this component, a library can provide support for annotations via DocBlocks or otherwise retrieve information that is embedded in a DocBlock. |
| phpdocumentor/type-resolver | 2.0.0 | runtime | A PSR-5 based resolver of Class names, Types and Structural Element Names |
| phpoption/phpoption | 1.9.5 | runtime | Option Type for PHP |
| phpstan/phpdoc-parser | 2.3.3 | runtime | PHPDoc parser with support for nullable, intersection and generic types |
| pragmarx/google2fa | v9.0.0 | runtime | A One Time Password Authentication package, compatible with Google Authenticator. |
| psr/clock | 1.0.0 | runtime | Common interface for reading the clock. |
| psr/container | 2.0.2 | runtime | Common Container Interface (PHP FIG PSR-11) |
| psr/event-dispatcher | 1.0.0 | runtime | Standard interfaces for event handling. |
| psr/http-client | 1.0.3 | runtime | Common interface for HTTP clients |
| psr/http-factory | 1.1.0 | runtime | PSR-17: Common interfaces for PSR-7 HTTP message factories |
| psr/http-message | 2.0 | runtime | Common interface for HTTP messages |
| psr/log | 3.0.2 | runtime | Common interface for logging libraries |
| psr/simple-cache | 3.0.0 | runtime | Common interfaces for simple caching |
| psy/psysh | v0.12.24 | runtime | An interactive shell for modern PHP. |
| ralouphie/getallheaders | 3.0.3 | runtime | A polyfill for getallheaders. |
| ramsey/collection | 2.1.1 | runtime | A PHP library for representing and manipulating collections. |
| ramsey/uuid | 4.9.3 | runtime | A PHP library for generating and working with universally unique identifiers (UUIDs). |
| spatie/laravel-package-tools | 1.93.1 | runtime | Tools for creating Laravel packages |
| spatie/laravel-permission | 8.3.0 | runtime | Permission handling for Laravel 12 and up |
| spomky-labs/cbor-php | 3.3.0 | runtime | CBOR Encoder/Decoder for PHP |
| spomky-labs/pki-framework | 1.5.0 | runtime | A PHP framework for managing Public Key Infrastructures. It comprises X.509 public key certificates, attribute certificates, certification requests and certification path validation. |
| symfony/clock | v8.1.0 | runtime | Decouples applications from the system clock |
| symfony/console | v8.1.1 | runtime | Eases the creation of beautiful and testable command line interfaces |
| symfony/css-selector | v8.1.0 | runtime | Converts CSS selectors to XPath expressions |
| symfony/deprecation-contracts | v3.7.1 | runtime | A generic function and convention to trigger deprecation notices |
| symfony/error-handler | v8.1.0 | runtime | Provides tools to manage errors and ease debugging PHP code |
| symfony/event-dispatcher | v8.1.1 | runtime | Provides tools that allow your application components to communicate with each other by dispatching events and listening to them |
| symfony/event-dispatcher-contracts | v3.7.1 | runtime | Generic abstractions related to dispatching event |
| symfony/finder | v8.1.1 | runtime | Finds files and directories via an intuitive fluent interface |
| symfony/http-foundation | v8.1.1 | runtime | Defines an object-oriented layer for the HTTP specification |
| symfony/http-kernel | v8.1.1 | runtime | Provides a structured process for converting a Request into a Response |
| symfony/mailer | v8.1.1 | runtime | Helps sending emails |
| symfony/mime | v8.1.0 | runtime | Allows manipulating MIME messages |
| symfony/polyfill-ctype | v1.37.0 | runtime | Symfony polyfill for ctype functions |
| symfony/polyfill-intl-grapheme | v1.38.1 | runtime | Symfony polyfill for intl's grapheme_* functions |
| symfony/polyfill-intl-idn | v1.38.1 | runtime | Symfony polyfill for intl's idn_to_ascii and idn_to_utf8 functions |
| symfony/polyfill-intl-normalizer | v1.38.0 | runtime | Symfony polyfill for intl's Normalizer class and related functions |
| symfony/polyfill-mbstring | v1.38.2 | runtime | Symfony polyfill for the Mbstring extension |
| symfony/polyfill-php80 | v1.37.0 | runtime | Symfony polyfill backporting some PHP 8.0+ features to lower PHP versions |
| symfony/polyfill-php84 | v1.38.1 | runtime | Symfony polyfill backporting some PHP 8.4+ features to lower PHP versions |
| symfony/polyfill-php85 | v1.38.1 | runtime | Symfony polyfill backporting some PHP 8.5+ features to lower PHP versions |
| symfony/polyfill-php86 | v1.38.0 | runtime | Symfony polyfill backporting some PHP 8.6+ features to lower PHP versions |
| symfony/polyfill-uuid | v1.37.0 | runtime | Symfony polyfill for uuid functions |
| symfony/process | v8.1.0 | runtime | Executes commands in sub-processes |
| symfony/property-access | v8.1.0 | runtime | Provides functions to read and write from/to an object or array using a simple string notation |
| symfony/property-info | v8.1.0 | runtime | Extracts information about PHP class' properties using metadata of popular sources |
| symfony/routing | v8.1.0 | runtime | Maps an HTTP request to a set of configuration variables |
| symfony/serializer | v8.1.1 | runtime | Handles serializing and deserializing data structures, including object graphs, into array structures or other formats like XML and JSON. |
| symfony/service-contracts | v3.7.1 | runtime | Generic abstractions related to writing services |
| symfony/string | v8.1.0 | runtime | Provides an object-oriented API to strings and deals with bytes, UTF-8 code points and grapheme clusters in a unified way |
| symfony/translation | v8.1.1 | runtime | Provides tools to internationalize your application |
| symfony/translation-contracts | v3.7.1 | runtime | Generic abstractions related to translation |
| symfony/type-info | v8.1.0 | runtime | Extracts PHP types information. |
| symfony/uid | v8.1.0 | runtime | Provides an object-oriented API to generate and represent UIDs |
| symfony/var-dumper | v8.1.1 | runtime | Provides mechanisms for walking through any arbitrary PHP variable |
| tijsverkoyen/css-to-inline-styles | v2.4.0 | runtime | CssToInlineStyles is a class that enables you to convert HTML-pages/files into HTML-pages/files with inline styles. This is very useful when you're sending emails. |
| vlucas/phpdotenv | v5.6.4 | runtime | Loads environment variables from `.env` to `getenv()`, `$_ENV` and `$_SERVER` automagically. |
| voku/portable-ascii | 2.1.1 | runtime | Portable ASCII library - performance optimized (ascii) string functions for php. |
| web-auth/cose-lib | 4.6.0 | runtime | CBOR Object Signing and Encryption (COSE) For PHP |
| web-auth/webauthn-lib | 5.3.5 | runtime | FIDO2/Webauthn Support For PHP |
| webmozart/assert | 2.4.1 | runtime | Assertions to validate method input/output with nice error messages. |
| brianium/paratest | v7.20.0 | development | Parallel testing for PHP |
| composer/pcre | 3.4.0 | development | PCRE wrapping library that offers type-safe preg_* replacements. |
| composer/xdebug-handler | 3.0.5 | development | Restarts a process without Xdebug. |
| fakerphp/faker | v1.24.1 | development | Faker is a PHP library that generates fake data for you. |
| fidry/cpu-core-counter | 1.3.0 | development | Tiny utility to get the number of CPU cores. |
| filp/whoops | 2.18.4 | development | php error handling for cool kids |
| hamcrest/hamcrest-php | v2.1.1 | development | This is the PHP port of Hamcrest Matchers |
| iamcal/sql-parser | v0.7 | development | MySQL schema parser |
| jean85/pretty-package-versions | 2.1.1 | development | A library to get pretty versions strings of installed dependencies |
| larastan/larastan | v3.10.0 | development | Larastan - Discover bugs in your code without running it. A phpstan/phpstan extension for Laravel |
| laravel/agent-detector | v2.0.2 | development | Detect if code is running in an AI agent or automated development environment |
| laravel/boost | v2.4.13 | development | Laravel Boost accelerates AI-assisted development by providing the essential context and structure that AI needs to generate high-quality, Laravel-specific code. |
| laravel/mcp | v0.9.1 | development | Rapidly build MCP servers for your Laravel applications. |
| laravel/pail | v1.2.7 | development | Easily delve into your Laravel application's log files directly from the command line. |
| laravel/pao | v1.1.2 | development | Agent-optimized output for PHP testing tools |
| laravel/pint | v1.29.3 | development | An opinionated code formatter for PHP. |
| laravel/roster | v0.5.1 | development | Detect packages & approaches in use within a Laravel project |
| laravel/sail | v1.64.0 | development | Docker files for running a basic Laravel application. |
| mockery/mockery | 1.6.12 | development | Mockery is a simple yet flexible PHP mock object framework |
| myclabs/deep-copy | 1.13.4 | development | Create deep copies (clones) of your objects |
| nunomaduro/collision | v8.9.5 | development | Cli error handling for console/command-line PHP applications. |
| pestphp/pest | v4.7.5 | development | The elegant PHP Testing Framework. |
| pestphp/pest-plugin | v4.0.0 | development | The Pest plugin manager |
| pestphp/pest-plugin-arch | v4.0.2 | development | The Arch plugin for Pest PHP. |
| pestphp/pest-plugin-laravel | v4.1.0 | development | The Pest Laravel Plugin |
| pestphp/pest-plugin-mutate | v4.0.1 | development | Mutates your code to find untested cases |
| pestphp/pest-plugin-profanity | v4.2.1 | development | The Pest Profanity Plugin |
| phar-io/manifest | 2.0.4 | development | Component for reading phar.io manifest information from a PHP Archive (PHAR) |
| phar-io/version | 3.2.1 | development | Library for handling version information and constraints |
| phpstan/phpstan | 2.2.16 | development | PHPStan - PHP Static Analysis Tool |
| phpunit/php-code-coverage | 12.5.7 | development | Library that provides collection, processing, and rendering functionality for PHP code coverage information. |
| phpunit/php-file-iterator | 6.0.1 | development | FilterIterator implementation that filters files based on a list of suffixes. |
| phpunit/php-invoker | 6.0.0 | development | Invoke callables with a timeout |
| phpunit/php-text-template | 5.0.0 | development | Simple template engine. |
| phpunit/php-timer | 8.0.0 | development | Utility class for timing |
| phpunit/phpunit | 12.5.30 | development | The PHP Unit Testing framework. |
| sebastian/cli-parser | 4.2.1 | development | Library for parsing CLI options |
| sebastian/comparator | 7.1.8 | development | Provides the functionality to compare PHP values for equality |
| sebastian/complexity | 5.0.0 | development | Library for calculating the complexity of PHP code units |
| sebastian/diff | 7.0.0 | development | Diff implementation |
| sebastian/environment | 8.1.2 | development | Provides functionality to handle HHVM/PHP environments |
| sebastian/exporter | 7.0.3 | development | Provides the functionality to export PHP variables for visualization |
| sebastian/global-state | 8.0.3 | development | Snapshotting of global state |
| sebastian/lines-of-code | 4.0.1 | development | Library for counting the lines of code in PHP source code |
| sebastian/object-enumerator | 7.0.0 | development | Traverses array structures and object graphs to enumerate all referenced objects |
| sebastian/object-reflector | 5.0.0 | development | Allows reflection of object attributes, including inherited and non-public ones |
| sebastian/recursion-context | 7.0.1 | development | Provides functionality to recursively process PHP variables |
| sebastian/type | 6.0.4 | development | Collection of value objects that represent the types of the PHP type system |
| sebastian/version | 6.0.0 | development | Library that helps with managing the version number of Git-hosted PHP projects |
| staabm/side-effects-detector | 1.0.5 | development | A static analysis tool to detect side effects in PHP code |
| symfony/yaml | v8.1.1 | development | Loads and dumps YAML files |
| ta-tikoma/phpunit-architecture-test | 0.8.7 | development | Methods for testing application architecture |
| theseer/tokenizer | 2.0.1 | development | A small library for converting tokenized PHP source code into XML and potentially other formats |
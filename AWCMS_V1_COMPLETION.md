# AWCMS v1 completion checkpoint

This package extends the supplied project without replacing the existing Pages, News, Media, Gallery, Document, User or Audit implementations.

## Newly connected in this package

1. Menu Builder
   - Multiple menu locations
   - Nested parent/child items
   - Custom URL, named route, Page, News, Gallery and Document links
   - Edit/delete/status/order controls
   - Drag/drop-assisted reordering for same-level items

2. Site Settings
   - Site identity and tagline
   - Logo and favicon from Public Media Library images
   - Army Unit / Training School / SFHQ / Establishment theme family selector
   - Primary/accent colours
   - Address, phones, email and map URL
   - Commander/head name, appointment, image and public message
   - Footer copy and default SEO
   - Hero slides and social links
   - Public maintenance mode

3. Roles & Permissions UI
   - Custom role creation
   - Permission syncing
   - Super Administrator protected from permission reduction in the UI

4. Contact Messages
   - Public contact form with validation, rate limiting and honeypot
   - Admin inbox/read/resolved flow

5. Redirect Manager
   - 301/302/307/308 redirects
   - Active/inactive state
   - Hit counter and last-hit timestamp
   - Fallback redirect resolution

6. Public integration
   - Dynamic homepage
   - Dynamic primary navigation
   - Dynamic footer/social/contact identity
   - Site logo/favicon
   - Latest News/Galleries/Documents
   - Commander/head message

7. Dashboard
   - Live content counts
   - New contact-message count
   - Recent audit events
   - Quick actions

## Validation status in the generated package

- PHP syntax lint: run over 247 PHP application, migration, route and test files; no syntax errors detected.
- `composer.json`: JSON parsed successfully.
- Full Laravel/Pest runtime, Blade compilation, Pint and Larastan require the Composer `vendor/` directory. The supplied ZIP intentionally did not include `vendor/`, and this isolated generation environment cannot download Composer packages.

Run the commands in `README.md` on the user's WAMP machine before merging/deploying.


## Final-pass fixes

- Restored the public Page SEO metadata sections so title, description, canonical, robots, Open Graph and Twitter metadata supplied by `PublicPageController` render through the shared public layout.
- Reduced the indexed redirect source path to a MySQL-safe length and aligned Livewire/service validation with that limit.
- Hardened redirect destinations against protocol-relative/open-redirect input and self-redirect loops.
- Added redirect-table existence protection to fallback routing before migrations are installed.
- Added named-route existence validation and protocol-relative URL protection to Menu Builder links.

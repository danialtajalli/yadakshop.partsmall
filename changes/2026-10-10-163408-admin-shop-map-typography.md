# Admin shop typography investigation and local fix

## Request and Scope

The user reported changed font sizes in the admin shop section. The exact page
and affected text were not specified; clarification and a screenshot were
requested. Source changes are local to Partsmall. Production was inspected
read-only at /var/www/partsmall-releases/production-20261008.

## Verified Findings

- The shop create/edit form includes components.view-product, which loads
  resources/js/app.js and the Neshan map SDK stylesheet. Repair-shop and
  representation forms share this map view.
- The installed SDK stylesheet contains global .text-sm, .text-xl, .row and
  other helper selectors. These are not limited to the map container.
- A browser fixture using the real local Filament stylesheet reproduced the
  leak: outside-map .text-sm text changed from 16px to 14px and .text-xl text
  from 16px to 20px when the unmodified SDK CSS was loaded. This proves a CSS
  isolation defect, not yet that these are the exact elements reported by the
  user. Ordinary Filament sidebar text remained 14px.
- Production's login page references Filament asset version 5.7.6.0; the local
  composer.lock records filament/filament v5.6.7. Its served Filament CSS also
  differs from the local published asset. This is an observed difference, not a
  confirmed cause of the reported regression.
- Production app container creation time was 2026-10-10T12:35:58.954118816Z.

## Changes

- resources/js/map/scope-styles.js: PostCSS transform scopes SDK selectors to
  .mapboxgl-map while preserving map-root selectors, fonts, keyframes, nested
  rules and ancestor-dependent night styles. Non-SDK CSS is left unchanged.
- vite.config.js: enables the scoped transform in asset builds and development.
- tests/Frontend/MapStyles.test.mjs: adds three focused regression tests.
- Rebuilt local public/build assets using npm.cmd run build. These generated
  assets are excluded from SFTP; production rebuilds them in its image build.

## Verification

- node --test tests/Frontend/MapStyles.test.mjs: 3 passed.
- PHP 8.4.15 PHPUnit tests/Feature/Filament: 25 passed, 199 assertions, isolated
  in-memory SQLite database.
- npm.cmd run build: passed. Existing large-bundle warning remains.
- Playwright with headless Edge: built CSS checked at 1440x900 and 390x844.
  Outside-map text matched the Filament-only baseline. Map root text stayed
  12px and its watermark stayed 11px. Screenshots were saved outside the repo
  and the mobile fixture was visually inspected.
- git diff --check: passed.
- Initial npm invocation was blocked by PowerShell script policy; npm.cmd
  completed without changing that policy.

## Backup and Outcome

No server files, containers, databases, credentials or shared configurations
were modified. No deletion or database operation occurred; no new database
backup was required. Existing backups were untouched. SSH and public HTTP
requests were read-only. Source changes remain local and uncommitted.

The CSS isolation defect is fixed locally. Matching the user's specific visual
regression still requires the affected page or screenshot. Uploading the new
scope-styles.js file and vite.config.js, followed by a controlled production
app/web image rebuild and recreation, is necessary to deploy this fix. No
production rollout was performed by this agent.

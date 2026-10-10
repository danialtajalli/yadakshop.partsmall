# Shop edit typography deployment check

## Request and Verified Findings

The user reported small text at /admin/shops/415/edit on partsmall.ir.
Unauthenticated access redirects to /admin/login, so the authenticated shop
page was not visually inspected.

Read-only SSH inspection confirmed app and web containers still have creation
times 2026-10-10T12:35:58.954118816Z and 2026-10-10T12:36:01.952799389Z.
The running application's Vite manifest references assets/app-DwlOnSXD.css.
Public HTTP retrieval verified that this asset still contains global .text-sm
and .text-xl rules and not the scoped selectors from the previous local fix.

Both updated source files, vite.config.js and resources/js/map/scope-styles.js,
exist in /var/www/partsmall-releases/production-20261008 with Oct 10 16:30
timestamps. Source uploads therefore do not indicate the running images have
been updated. Matching the actual visual symptom still needs an authenticated
view or screenshot.

## Capacity Warning

Root disk was 91% used with approximately 3.5 GB available. This exceeds the
team checklist's critical threshold. No production build was attempted.
Space and a rollback path must be assessed before attempting an image build.
No cleanup or deletion is authorized by this report.

## Outcome

The user redirected the task to a local legacy SQL relationship audit before a
deployment decision. No server files, databases, containers or shared settings
were changed. No database backup was needed for these read-only checks; all
existing backups were preserved. No production typography deployment occurred.

# Admin SPA production release

The user requested matching SPA support for PartsMall and deployment/push of both
PartsMall and TelegramBot. The user clarified that their existing `->spa()` change
was in TelegramBot. No Cheragh web application changes are included.

## Published changes

- PartsMall `release`: `49d12e6cb06c383365590d4034d2e31b828a73b7`.
  Enabled Filament SPA mode, with full navigation for the storefront home and
  public profile links. Added scoped TinyEditor cleanup before SPA departure;
  without this cleanup the editor disappeared on a repeat visit.
- TelegramBot `main`: `5be32ff68ddaccdf8d49a43d0fab9e1743ad4f3f`.
  Committed the user's existing admin `->spa()` addition.
- Both remote branch tips were verified after pushing. Neither target branch
  required PR approval under the policies checked for this release.
- Publishing TelegramBot also published its two existing, already deployed
  receipt-pipeline/release-manifest commits. This deployment changes only its
  admin provider; the outstanding receipt integration has not been declared done.

## Targets and deployment

Verified SSH/sudo target: `partsmall-server`, `130.185.122.197`.
TelegramBot runs natively at `/var/www/cheragh-bot`;
PartsMall runs in Docker at `https://partsmall.ir`.

Production source files matched the selected parent commit hashes before changes.
Built `partsmall-app:admin-spa-20261024-092517` from the exact existing patched
PHP 8.4.27 application image, overlaying only:

- `app/Providers/Filament/AdminPanelProvider.php`
- `resources/views/vendor/filament-tinyeditor/tiny-editor.blade.php`
- A release manifest in the image.

The new image ID is
`sha256:cde52944371f2e21370431de4f149e61764375a6d283cc7470afc356dc4de7f3`.
The active deployment directory is
`/var/www/partsmall-releases/admin-spa-20261024-092517`.
Its protected `compose-app.json` changes only the application's image relative
to the prior normalized Compose configuration. Recreated only
`partsmall-prod-app-1`, refreshed Blade views, validated and reloaded the existing
web container's Nginx configuration to resolve its new PHP upstream.
The previous canonical release tree remains available for rollback.

Replaced TelegramBot's admin provider atomically, preserving ownership/mode,
validated PHP/Blade, cleared compiled views, validated PHP-FPM configuration and
gracefully reloaded `php8.4-fpm`. Other application files, including server-only
changes, were preserved. No migrations or application data updates were run.

## Backups and durable evidence

Protected backup: `/var/backups/partsmall/admin-spa-20261024-092517`.
It contains original files, container inspection/configuration snapshots, the
original/rollback effective Compose configuration, HTTP baselines, final manifest,
and executable `rollback.sh` for both scoped application updates.
The old app image is retained. Recovery Compose selects its immutable image ID.
Backup files and their restore paths were checked while preparing deployment;
rollback was not needed or executed against production.

Durable manifests:

- `/var/www/partsmall-releases/admin-spa-20261024-092517/manifest.json`
- `/var/www/cheragh-bot/storage/app/releases/admin-spa-20261024-092517/manifest.json`

The manifest records repository/branch/source revisions, exact file SHA-256
checksums, image ID, checks, deployment method and verification results. Source
revisions identify the scoped overlays; this is not a replacement of all live
application files with an entire local worktree.

The initial image build stopped before any live application changes because
BuildKit interpreted a raw image ID as a registry reference. Retried using a
verified local tag for the same image ID; the successful rollout used the
directory above. The unused preparation/backup directories ending `092449` were
retained. No unrelated files or Docker volumes were removed.

## Verification

- PartsMall Filament tests: 25 passed, 199 assertions.
- TelegramBot admin/navigation/chart tests: 29 passed, 147 assertions.
- Both Vite builds, PHP syntax checks, Blade cache/clear and diff checks passed.
- Browser tests used actual rendered authenticated admin pages with isolated
  SQLite data. Desktop (1440px) and mobile (390px) navigation, list/create forms,
  history back/forward and a preserved document sentinel passed without
  JavaScript errors. PartsMall's editor initialized on both the first and repeat
  visit; leaving the page removed the stale TinyMCE registration.
- Browser Livewire write requests were stubbed. No production form submissions
  or database writes were made as smoke tests.
- Production bootstraps report `hasSpaMode() === true` for both admin panels;
  both report PHP 8.4.27. Deployed source hashes match the published changes.
- PartsMall app is healthy. All other pre-existing container IDs were preserved.
- Both `/up` endpoints and admin login pages return 200. Both unauthenticated
  `/admin` requests redirect with 302 as expected.
- Shared-site before/after status checks match:

| Host | Before | After |
| --- | --- | --- |
| bot.cheraghbargh.ir | 200 | 200 |
| partsmall.ir | 200 | 200 |
| price.yadakgate.ir | 302 | 302 |
| robot.yadakgate.ir | 200 | 200 |
| agent.yadakgate.ir | 200 | 200 |
| partner.yadakgate.ir | 400 | 400 |
| ruby100.ltd | 200 | 200 |
| yadakgate.ir | 200 | 200 |

The partner site's 400 and PartsMall queue's unhealthy check predate this release.
The queue container was not recreated; its previously documented missing
health-check script remains outside this change.

## Outcome

Both admin SPA changes are published and verified live. No Mizito tasks were
linked to this direct request, so no task statuses were changed. Existing
unrelated local TelegramBot edits were left untouched. This report is a separate
documentation commit following the deployed application revisions above.

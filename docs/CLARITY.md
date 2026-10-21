# Microsoft Clarity

The application uses project ID `z02pt3ef4k` by default. The integration loads
one asynchronous Microsoft tag on public production pages. It has no consent
popup, accept/reject controls, privacy-settings link or consent local-storage
flow. It does not send an automatic consent grant or denial. Cookie and consent
behavior is controlled by the Clarity project settings and the Microsoft tag.
No dashboard settings were changed for the popup-removal task.

Admin paths, non-production environments and disabled/invalid project IDs remain
excluded. Contact forms, status messages and shop comments retain their masking
attributes. No Laravel package or database migration is needed.

## Application configuration

```dotenv
CLARITY_ENABLED=true
CLARITY_PROJECT_ID=z02pt3ef4k
```

Only alphanumeric project IDs are accepted. An explicitly empty ID, disabled
setting or non-production environment prevents the loader from rendering.
The shared storefront layout and standalone contact templates include it once.
The loader also avoids inserting a duplicate Microsoft tag if evaluated twice.
The obsolete popup stylesheet has been removed; the loader asset uses `?v=3`.

Sign in with the account that owns this Clarity project to verify the dashboard,
site association and cookie settings. Keep Balanced or Strict masking and review
a contact-page recording to confirm the application masking is effective.
Project ownership and dashboard configuration were not independently verified
by this local change. Do not install a duplicate snippet through a tag manager.

## Local change and historical deployment

The popup-removal change was implemented and tested locally on 2026-10-21.
It has not been deployed. The existing integration was recorded as deployed to
`https://partsmall.ir` on 2026-10-19, with its previous consent controls. Those
historical deployment observations were not rechecked during the local change.

The previously documented active app/web release is
`/var/www/partsmall-releases/clarity-ui-20261019-z02pt3ef4k`, using
`partsmall-app:clarity-ui-20261019-z02pt3ef4k` and
`partsmall-web:clarity-ui-20261019-z02pt3ef4k`. Its rollback archive/script is
`/var/backups/partsmall/clarity-ui-20261019-z02pt3ef4k/rollback.sh`.
The queue remains on its previously recorded release. No containers, services,
production data or shared proxy configuration were changed for popup removal.

Deploy PHP, Blade and public assets through the normal application rollout.
Both app and web images must contain the update. Refresh configuration caches
when changing environment values; recreate the app container to import changed
environment values rather than relying on a restart alone.

## Verification

```sh
node --test tests/Frontend/Clarity.test.mjs
php vendor/bin/phpunit tests/Feature/ClarityTest.php tests/Feature/ContactPageTest.php tests/Feature/ShopCommentTest.php
```

Check that public production pages render one loader and no popup or privacy
controls; the tag loads immediately without local-storage access or a synthetic
consent decision. Repeated loader execution must not add another tag. Verify
admin, disabled, invalid-ID and non-production exclusions and masking attributes.
Use an isolated browser with a stubbed Microsoft tag for local checks. Real
collection, cookies, recordings and dashboard settings require verification in
the target environment after an authorized rollout.

Set `CLARITY_ENABLED=false` and follow the normal configuration-cache and rollout
process to disable tracking for new page loads.

References: [installation](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-setup),
[Consent V2](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-consent-api-v2),
[consent management](https://learn.microsoft.com/en-us/clarity/setup-and-installation/consent-management),
[masking](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-masking).

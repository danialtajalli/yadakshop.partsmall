# Microsoft Clarity

The application uses the team-supplied Clarity Project ID `z02pt3ef4k` by default.
Tracking starts only in production after visitor consent. No Laravel package or
database migration is needed. The integration was deployed to
`https://partsmall.ir` on 2026-10-19. A live browser check verified the tag response
(`200`), analytics collection (`204`), consent controls and admin exclusion.
Project ownership and dashboard settings have not been independently verified.

## Current production release

The active application and web containers use the release directory
`/var/www/partsmall-releases/clarity-ui-20261019-z02pt3ef4k` and versioned images
`partsmall-app:clarity-ui-20261019-z02pt3ef4k` and
`partsmall-web:clarity-ui-20261019-z02pt3ef4k`. Its `compose.prod.yaml` selects these
images. Run future release commands from the active release directory, after
reviewing the intended changes. The queue remains on its prior image and release.

The protected rollback archive and script are under
`/var/backups/partsmall/clarity-ui-20261019-z02pt3ef4k/`; the rollback script is
`rollback.sh`. It selects the previous app/web image tags with a Compose override
and waits for health checks. The previous release directory is retained. The
production image tags without the Clarity suffix were preserved.

## Account setup

1. Sign in at [Microsoft Clarity](https://clarity.microsoft.com/) with the account
   that owns the existing project `z02pt3ef4k`. Confirm that it belongs to the
   production Partsmall website. Do not install another copy of the tracking
   snippet through a tag manager.
2. In the project's consent settings, enable Consent Mode / require cookie
   consent for all visitors. The application passes `consentv2`, grants analytics
   only after acceptance, and always denies advertising storage.
3. Review Settings > Masking. Keep Balanced or Strict masking; application
   attributes additionally mask contact forms, contact status messages and shop
   comments. Review the site's privacy notice for the actual use of Clarity and
   Microsoft's data processing before enabling collection.

There is no connected Clarity account tool available in this session. Account
verification and these dashboard settings must be completed by the project owner.

## Application configuration

The supplied project ID is the application default and is present in the
environment examples. An environment variable can override it:

```dotenv
CLARITY_ENABLED=true
CLARITY_PROJECT_ID=z02pt3ef4k
```

Only alphanumeric project IDs are accepted. An explicitly empty
`CLARITY_PROJECT_ID`, disabled setting, or any environment other than `production`
prevents analytics assets and consent controls from rendering. If the environment
variable is absent, the supplied project ID is used. Admin paths are excluded as
an additional guard; Filament uses its own layout without the integration.

Deploy the changed PHP, Blade, and public assets using the project's normal
deployment process. Refresh Laravel's configuration cache after changing the
environment. For Docker production, set these values in the existing production
environment file selected by `DOCKER_ENV_FILE`. The application services use
`env_file`, and the production entrypoint refreshes the configuration cache.
Recreate the app container to pick up environment changes; restarting an existing
container alone does not import changed environment values. Both application
and web images must include the updated source/public assets.

Deployment recreated only Partsmall's application and web containers and rebuilt
their configuration cache through the existing entrypoint. No database migration
or shared proxy configuration change was performed. Other containers retained
their original IDs and start times, and shared-site HTTP/TLS checks matched their
pre-deployment baseline.

## Visitor behavior

- The Persian consent banner appears only when an active production project is
  configured. The compact banner sits at the bottom center, briefly explains
  analytics and visit replay, links to Microsoft's privacy statement, and offers
  equally styled acceptance and rejection buttons.
- The Microsoft tag is fetched only after acceptance. Rejection does not fetch
  the tag, including cookieless tracking.
- Decisions are stored in browser local storage, scoped to the project ID, for
  180 days. Expired or malformed decisions require a new choice. If storage is
  unavailable, the current page honors the choice and the next page asks again.
- The privacy settings link in the footer reopens the controls; it does not float
  above the call button. Choosing an option preserves the current scroll position.
  Withdrawing consent sends
  denied consent and reloads the page to unload the tag. Withdrawal in another
  tab also reloads a page with active tracking.
- The shared storefront layout and standalone contact templates include the
  integration. Existing disabled contact-form GET routing is unchanged.

## Verification

Automated checks:

```sh
node --test tests/Frontend/Clarity.test.mjs
php vendor/bin/phpunit tests/Feature/ClarityTest.php tests/Feature/ContactPageTest.php tests/Feature/ShopCommentTest.php
```

The following were checked after deployment in an isolated headless browser;
repeat them when changing the project or consent implementation:

1. Before choosing, confirm there are no network requests to `clarity.ms` and no
   Clarity cookies. Reject and navigate to another page; confirm this continues.
2. Open privacy settings, accept, and confirm one `clarity.ms/tag/<project-id>`
   request. Check consent/cookie behavior against the project's dashboard settings.
3. Withdraw consent, confirm the reload, and confirm no new tag loads. Check
   that consent revocation cleared Clarity cookies.
4. Confirm `/admin/login` does not load these analytics assets.
5. Confirm sessions arrive in the Clarity dashboard and inspect a contact-page
   recording to verify masking. This dashboard review remains for a project
   member; the live browser check confirmed the form's masking attribute and a
   successful Microsoft collection response.

To disable tracking for new page loads, set `CLARITY_ENABLED=false`, refresh the
configuration cache, and follow the normal application rollout process.

References: [installation](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-setup),
[Consent V2](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-consent-api-v2),
[masking](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-masking).

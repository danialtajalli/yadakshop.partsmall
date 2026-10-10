# Production shop 42 / category 6 compared with legacy seed source

## Request and Scope

The user clarified that the comparison should use the server's current database,
not the stopped local database. Verify the seed source, confirm the production
shop 42 / category 6 relation, then check that the legacy source represents the
same relationship despite its different schema.

Targets were production containers partsmall-prod-app-1 and
partsmall-prod-mysql-1, release /var/www/partsmall-releases/production-20261008,
and the local project-root partsmall_db.sql. Both containers were running and
their Compose labels identified the same production project and release.

## Seed Source Verification

DatabaseSeeder calls PartsmallLegacySeeder first. PartsmallLegacySeeder reads
base_path('partsmall_db.sql') and passes its contents to LegacyDataImporter via
LegacyInsertParser. SHA-256 hashes of both seeder classes and the importer were
identical between the local checkout and the running app container.

The local root dump and the server release's root dump were byte-for-byte
identical, SHA-256:

`5dc2e372f1dedeb8ac1ce6b024d60b9a21ed1072e884c4f578b1aaffb268897a`

The dump is present in the server release directory, but not in the running app
container. This confirms the configured seed source and matching release copy;
it does not prove the historical seeding execution or allow rerunning the
initial seeder inside that image. No seeder was run.

## Production Read-Only Verification

A PHP helper was streamed over SSH into the existing production app container
without saving a server file. It used the application's effective MySQL
connection to database partsmall, established an explicitly read-only
transaction, performed SELECT queries and rolled the transaction back.

- shops.id = 42 exists.
- parts_categories.id = 6 exists.
- Exactly one parts_category_shop row has shop_id = 42 and
  parts_category_id = 6.
- The complete category-ID list for that shop was [6].

No credentials, names, contact details or other sensitive record contents were
printed. Shop slug/name and category name were compared using hashes only.

## Legacy Structure and Comparison

- Legacy shop.id is preserved as current shops.id.
- Legacy categorypart.id is preserved as current parts_categories.id.
- Legacy shop.cat is a comma-separated category-ID field, not a current pivot
  row. Legacy shop.part is a separate part relationship and was not confused
  with the category relationship.
- Legacy shop 42 occurs once, with cat = '6'.
- Legacy categorypart 6 occurs once.
- LegacyInsertParser parsed these records, and the importer's actual pure
  existingPartsCategoryIds method returned [6], with its category-ID lookup
  initialized from the dump. No import or database connection was made locally.
- The shop slug/name and category-name hashes also matched production, confirming
  the referenced IDs identify the same records rather than just equal numbers.

The importer therefore produces the requested shop_id = 42 /
parts_category_id = 6 pivot relationship from the existing dump.

## Outcome and Backups

All requested checks passed. The relationship already exists in production and
in the legacy seed source. No SQL dump correction is needed.

No server files, database data, services, images, shared configurations or local
application source were modified. No file was deleted. No migration, seeder or
destructive command was executed. No new backup was required for the read-only
comparison; all existing backups were preserved. This report and temporary
local verification helpers are the only files created for this request.

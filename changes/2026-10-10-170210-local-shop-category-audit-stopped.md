# Local seed-source and shop-category audit: stopped

## Request

Confirm that root partsmall_db.sql is the initial seeder's source. Continue only
if the current local database has shop 42 related to parts category 6, then
verify that the legacy file will seed that same relation. Understand the old
and new schemas before changing anything.

## Verified Source and Mapping

- DatabaseSeeder calls PartsmallLegacySeeder first.
- PartsmallLegacySeeder reads base_path('partsmall_db.sql') and passes the
  contents to LegacyInsertParser and LegacyDataImporter. The root file exists.
- Legacy shop.id is preserved as shops.id.
- Legacy categorypart.id is preserved as parts_categories.id.
- Legacy shop.cat contains comma-separated category IDs. The importer splits,
  deduplicates and filters them against imported categorypart IDs, then inserts
  shop_id / parts_category_id rows in parts_category_shop.
- Shop.partsCategories() uses that current pivot table. Legacy shop.part is a
  different relation, imported into part_shop, and is not the requested category
  relation.
- The initial importer truncates existing tables. It was NOT executed.

## Failed Local Database Gate

A temporary helper outside the project bootstrapped the configured local
application connection with PHP 8.4.15. It was designed to use an explicitly
read-only transaction and SELECT checks only. Connection establishment failed
before a transaction or any SQL query could run: SQLSTATE HY000, driver code
2002, connection refused. The failure was not an authentication or missing-row
result. Passwords and detailed connection error contents were not logged.

Read-only service inspection showed wampmysqld64 and wampmariadb64 are stopped.
Neither service was started or reconfigured.

## Outcome and Next Step

The seed-source gate passed. The current-local-data gate could not be verified.
Per the user's stop condition, no legacy shop 42 record was parsed or changed
after that failure. No seeder, migration, database update, SQL dump edit or server
operation was performed for this request. No new backup was necessary because
there were no database or dump changes. Existing files and backups were kept.

Start the database service used by the local project, then repeat the current
shop 42/category 6 read-only check. Only if that passes should the dump relation
be examined and, if needed, corrected after preserving a backup of the dump.

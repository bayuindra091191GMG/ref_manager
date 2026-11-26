# Contributing

Start with [Architecture](ARCHITECTURE.md) to find the owning module. Use [Operations](OPERATIONS.md) to provision an environment and [Security](SECURITY.md) to report vulnerabilities privately.

## Set up and test

Install PHP compatible with [`composer.json`](composer.json), Composer, and the required PHP extensions. Run `composer install` to install the locked dependencies and PHPUnit. Install Node.js and run `npm install` only when working on the Vite inputs or build configuration. The admin UI also uses checked-in assets from `public/`; changing those assets does not require a Vite build.

```sh
php artisan test --filter=AdminBookingCheckProofTest
php artisan test
```

Start with the relevant class or test filter. [`phpunit.xml`](phpunit.xml) defaults to in-memory SQLite, synchronous queues, and in-memory sessions and cache. [`tests/TestCase.php`](tests/TestCase.php) rejects cached Laravel configuration and databases other than SQLite `:memory:` or a database whose name ends in `_test` or `_testing`. Clear the config cache with `php artisan config:clear` in the development checkout if necessary. For behavior that depends on MySQL or MariaDB, use a disposable test database rather than the development or production database. The baseline application schema is not an SQLite migration.

## Make a change

- Follow the route to its controller, model, and Blade view or API response. Check any jobs, reports, or accounting that consume the same changes. Use the existing Laravel patterns in that module rather than introducing another layer for a small change.
- Preserve route names, response formats, permission checks, and branch boundaries unless the change explicitly requires a different contract. For schema changes, add a new migration instead of rewriting the baseline SQL. Review how the change affects existing rows and whether rollback is safe.
- Cover the user-visible behavior with a focused test when appropriate. For payments, inventory, and booking transitions, include a rejected or repeated operation if it could duplicate a charge, stock movement, or notification. Use synthetic records and fake external services in tests.
- If editing Blade or assets, open the affected screen and exercise the changed behavior, including validation and the relevant roles. Run `npm run build` if the change affects the Vite inputs or pipeline.

## Open a pull request

1. Describe the workflow and what changed. List the checks you ran and note anything you could not verify.
2. Call out migrations, config changes, worker restarts, and any compatibility impact for mobile clients or staff. Add redacted screenshots for visible changes.
3. Keep the diff focused. Do not include local settings, credentials, real customer data, or the ignored `PROJECT-GUIDE.md`.

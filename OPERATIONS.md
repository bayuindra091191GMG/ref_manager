# Operations

This runbook covers deployment and diagnosis. Keep workstation settings, local URLs, and sample account details in the ignored `PROJECT-GUIDE.md`. Provision deployment secrets through the environment or an approved secret store.

## Prepare an environment

1. Install a PHP runtime that satisfies [`composer.json`](composer.json), Composer, a MySQL or MariaDB server, and a compatible SQL client for loading the schema dump. Run `composer check-platform-reqs --lock --no-dev` on the deploy host and ensure the web and worker PHP runtimes also have the required extensions, including the PDO driver for MySQL.
2. Configure `APP_ENV`, `APP_URL`, `APP_KEY`, the database connection, and the session, cache, and queue stores. In production, set `APP_DEBUG=false`. Preserve `APP_KEY` across releases. The repository does not ship an `.env.example`; get environment-specific settings from the maintainer. Install the Composer dependencies before running Artisan.
3. Point the web server document root at `public/`. Keep `storage/` and `bootstrap/cache/` writable by the application account. Set up `php artisan storage:link` where public storage is required. Persist uploaded content and generated receipts across releases; see the disk definitions in [`config/filesystems.php`](config/filesystems.php). Review which files will be served publicly before exposing the deployment.
4. For a new, dedicated database, initialize the schema with `php artisan migrate` (`--force` is required for non-interactive production runs). Use `DB_CONNECTION=mysql` for the baseline, including when the server is MariaDB. The [schema dump](database/schema/mysql-schema.sql) has table definitions but no business data. Restore approved reference data and provision staff accounts and permissions separately; the default seeder is not an installation routine. The dump does not contain Laravel's cache tables, so provision them if using the default database cache or select another supported store.
5. For mobile authentication, provision stable Passport signing keys and the personal access client used for token issuance. `php artisan passport:keys` and `php artisan passport:client --personal` are setup commands, not steps to repeat on every release. Configure and verify payment and notification integrations separately before enabling live traffic.

## Release checklist

- Install locked PHP dependencies with `composer install --no-dev --optimize-autoloader --no-interaction`. Ship the tracked `public/` assets with the release. Run `npm run build` only for a release that uses the Vite outputs; it does not regenerate the bundled OneUI assets.
- Back up the database and persistent uploads before schema or data changes. Verify the target environment and review pending migrations with `php artisan migrate:status` on an initialized database before running `php artisan migrate --force` as part of an approved release.
- Refresh Laravel's config cache if the deployment uses it. Run `php artisan queue:restart` after deploying code used by workers and confirm the process supervisor starts workers again. If multiple hosts run workers, they need a shared cache for the restart signal.
- Run a supervised `php artisan queue:work` for the configured queue connection and invoke `php artisan schedule:run` once a minute from the scheduler host. Use `php artisan schedule:list` to check schedule registration, not as proof that the scheduler or workers are running.
- Check `/up` for application boot, then test staff login and one relevant booking or POS flow in a safe environment. If the release changes jobs, verify that a representative job is dispatched and processed. Do not send test payment events or notifications to real customers.

## Diagnose and recover

- **Reminders or room statuses are late:** check the scheduler, worker processes, queue backlog, and `php artisan queue:failed`. Before retrying a job, check whether it already sent a notification or changed transaction state.
- **Receipts or uploads fail:** check disk permissions, shared volumes, and the public storage link. Use a non-sensitive fixture to verify the intended file is reachable only where expected.
- **A release fails:** coordinate application, database, and worker rollback. The [baseline migration's](database/migrations/2026_08_24_000000_create_ref_manager_schema.php) `down()` drops all base tables in the selected database except `migrations`. Do not use it as a recovery method for a populated environment; restore a tested backup when required.

See [Architecture](ARCHITECTURE.md) for workflow ownership and [Security](SECURITY.md) for deployment safeguards.

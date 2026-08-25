<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->guardAgainstCachedConfiguration();

        parent::setUp();

        $this->guardAgainstUnsafeTestingDatabase();
    }

    private function guardAgainstCachedConfiguration(): void
    {
        $configCache = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($configCache)) {
            throw new RuntimeException(
                'Refusing to run tests while bootstrap/cache/config.php exists. Run php artisan config:clear first.'
            );
        }
    }

    private function guardAgainstUnsafeTestingDatabase(): void
    {
        if (! app()->environment('testing')) {
            throw new RuntimeException('Tests must run with APP_ENV=testing.');
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && $database === ':memory:') {
            return;
        }

        if (is_string($database) && preg_match('/(_test|_testing)$/', $database) === 1) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Refusing to run tests against unsafe database connection [%s] database [%s]. Use sqlite :memory: or a database ending in _test/_testing.',
                (string) $connection,
                (string) $database
            )
        );
    }
}

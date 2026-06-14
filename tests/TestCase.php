<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Safety net: tests must always run against the sqlite in-memory database
     * defined in phpunit.xml. If this resolves to anything else (e.g. mysql),
     * it usually means a stale bootstrap/cache/config.php is overriding
     * phpunit.xml's env values, which would cause RefreshDatabase to run
     * migrate:fresh against the real production database.
     *
     * Run "php artisan config:clear" if this exception is thrown.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');

        if ($connection !== 'sqlite') {
            throw new \RuntimeException(
                "Refusing to run tests: database connection is \"{$connection}\", expected \"sqlite\". "
                . 'This likely means a stale config cache (bootstrap/cache/config.php) is overriding '
                . 'phpunit.xml. Run "php artisan config:clear" before running tests.'
            );
        }
    }
}

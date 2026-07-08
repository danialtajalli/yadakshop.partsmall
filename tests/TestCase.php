<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertInMemorySqliteDatabase();
    }

    private function assertInMemorySqliteDatabase(): void
    {
        $connection = (string) config('database.default');
        $database = config('database.connections.'.$connection.'.database');

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(sprintf(
                'Refusing to run tests against database [%s:%s]. Tests must use sqlite :memory: only.',
                $connection,
                var_export($database, true),
            ));
        }
    }
}

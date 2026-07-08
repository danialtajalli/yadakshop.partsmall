<?php

namespace Tests\Unit;

use Tests\TestCase;

class TestingDatabaseGuardTest extends TestCase
{
    public function test_tests_run_against_in_memory_sqlite_only(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}

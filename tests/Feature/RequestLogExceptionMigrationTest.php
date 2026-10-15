<?php

namespace Tests\Feature;

use App\Models\RequestLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\MySqlConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class RequestLogExceptionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_generates_a_nullable_longtext_alter_for_mysql(): void
    {
        // Compile production SQL without connecting to a MySQL database.
        $connection = new MySqlConnection(fn () => throw new RuntimeException('Unexpected database connection.'));
        $schema = Schema::getFacadeRoot();
        Schema::swap($connection->getSchemaBuilder());

        try {
            $statements = $connection->pretend(fn () => $this->migration()->up());
        } finally {
            Schema::swap($schema);
        }

        $this->assertCount(1, $statements);
        $this->assertSame('alter table `request_logs` modify `exception` longtext null', $statements[0]['query']);
    }

    public function test_migration_preserves_existing_logs_and_accepts_large_exceptions(): void
    {
        $this->storeException('Existing exception');
        $this->storeException(null);
        $migration = $this->migration();

        $migration->down();
        $migration->up();

        $largeException = str_repeat('خطا ', 20000);
        $this->assertGreaterThan(65535, strlen($largeException));
        $this->storeException($largeException);

        $this->assertSame(
            ['Existing exception', null, $largeException],
            RequestLog::query()->orderBy('id')->pluck('exception')->all(),
        );
    }

    public function test_rollback_refuses_to_truncate_multibyte_exception_logs(): void
    {
        $largeException = str_repeat('خ', 33000);
        $this->assertLessThan(65535, mb_strlen($largeException));
        $this->assertGreaterThan(65535, strlen($largeException));
        $this->storeException($largeException);

        try {
            $this->migration()->down();
            $this->fail('Rollback should refuse to narrow a column containing oversized logs.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('existing logs exceed 65,535 bytes', $exception->getMessage());
        }

        $this->assertSame($largeException, RequestLog::query()->sole()->exception);
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_15_000001_widen_request_logs_exception_column.php');
    }

    private function storeException(?string $exception): void
    {
        RequestLog::query()->create([
            'log_type' => 'reportable_exception',
            'occurred_at' => now(),
            'method' => 'GET',
            'url' => 'https://example.test/request-log-error',
            'path' => 'request-log-error',
            'status_code' => 500,
            'status_family' => 500,
            'is_reportable_status' => true,
            'exception' => $exception,
        ]);
    }
}

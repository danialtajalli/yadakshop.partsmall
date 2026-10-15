<?php

namespace Tests\Feature;

use App\Exceptions\RequestLogWriteException;
use App\Jobs\StoreRequestLogJob;
use App\Models\RequestLog;
use App\Support\RequestLogPayload;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PDOException;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

class RequestLoggingSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_cap_preserves_utf8_and_other_request_fields(): void
    {
        $request = Request::create('https://example.test/error?source=test');
        $payload = RequestLogPayload::make(
            request: $request,
            logType: 'reportable_exception',
            statusCode: 500,
            exception: new RuntimeException(str_repeat('خطا ', 10000)),
        );

        $this->assertLessThanOrEqual(16384, strlen($payload['exception']));
        $this->assertTrue(mb_check_encoding($payload['exception'], 'UTF-8'));
        $this->assertStringStartsWith(RuntimeException::class.': ', $payload['exception']);
        $this->assertStringEndsWith("\n[truncated]", $payload['exception']);
        $this->assertSame(['source' => 'test'], $payload['query']);
        $this->assertSame(500, $payload['status_code']);
    }

    public function test_null_short_and_exact_boundary_exception_values_are_unchanged(): void
    {
        foreach ([[], ['exception' => null], ['exception' => 'short'], ['exception' => str_repeat('x', 16384)]] as $payload) {
            $this->assertSame($payload, RequestLogPayload::limitException($payload));
        }

        $limited = RequestLogPayload::limitException(['exception' => str_repeat('x', 16385)]);
        $this->assertSame(16384, strlen($limited['exception']));
        $this->assertStringEndsWith("\n[truncated]", $limited['exception']);
    }

    public function test_new_job_serializes_a_bounded_exception_and_stores_it(): void
    {
        $job = new StoreRequestLogJob($this->payload(str_repeat('x', 100000)));
        $restored = unserialize(serialize($job));

        $this->assertEquals($job->data, $restored->data);
        $this->assertSame(16384, strlen($restored->data['exception']));
        $this->assertLessThan(20000, strlen(serialize($job)));
        $restored->handle();
        $this->assertSame($job->data['exception'], RequestLog::query()->sole()->exception);
    }

    public function test_legacy_serialized_job_is_capped_before_insertion(): void
    {
        // Existing queue payloads bypass the newly added constructor limiter.
        $legacy = (new ReflectionClass(StoreRequestLogJob::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(StoreRequestLogJob::class, 'data'))->setValue(
            $legacy,
            $this->payload(str_repeat('خطا ', 20000)),
        );
        $job = unserialize(serialize($legacy));
        $this->assertGreaterThan(65535, strlen($job->data['exception']));

        $job->handle();

        $stored = RequestLog::query()->sole()->exception;
        $this->assertLessThanOrEqual(16384, strlen($stored));
        $this->assertTrue(mb_check_encoding($stored, 'UTF-8'));
        $this->assertStringEndsWith("\n[truncated]", $stored);
    }

    public function test_query_failure_wrapper_excludes_sql_bindings_and_previous_exception(): void
    {
        $cause = new QueryException(
            'mysql',
            'insert into request_logs (exception) values (?)',
            [str_repeat('PRIVATE_PAYLOAD', 10000)],
            new PDOException('Data too long for column exception', 1406),
        );
        $failure = new RequestLogWriteException($cause);

        $this->assertSame(QueryException::class, $failure->originalClass);
        $this->assertSame('1406', $failure->originalCode);
        $this->assertNull($failure->getPrevious());
        $this->assertStringContainsString('Data too long', $failure->getMessage());
        $this->assertStringNotContainsString('PRIVATE_PAYLOAD', (string) $failure);
        $this->assertStringNotContainsString('insert into', (string) $failure);

        $large = new RequestLogWriteException(new RuntimeException(str_repeat('خطا ', 10000)));
        $this->assertLessThanOrEqual(1050, strlen($large->getMessage()));
        $this->assertTrue(mb_check_encoding($large->getMessage(), 'UTF-8'));
    }

    public function test_schema_check_failure_is_wrapped_without_an_extra_payload_dump(): void
    {
        Log::spy();
        Schema::shouldReceive('hasTable')->with('request_logs')->once()
            ->andThrow(new RuntimeException('Schema database unavailable'));

        try {
            (new StoreRequestLogJob($this->payload()))->handle();
            $this->fail('Expected a bounded request-log write failure.');
        } catch (RequestLogWriteException $failure) {
            $this->assertNull($failure->getPrevious());
            $this->assertStringContainsString('Schema database unavailable', $failure->getMessage());
        }

        Log::shouldNotHaveReceived('error');
    }

    public function test_console_exceptions_do_not_queue_request_logs_but_remain_logged(): void
    {
        $this->setConsole(true);
        Queue::fake();
        Log::spy();

        report(new RuntimeException('Worker error remains visible'));

        Queue::assertNothingPushed();
        Log::shouldHaveReceived('error')->with('Worker error remains visible', Mockery::type('array'))->once();
    }

    public function test_write_failure_does_not_queue_another_job_even_in_http_execution(): void
    {
        $this->setConsole(false);
        Queue::fake();
        Log::spy();
        $failure = new RequestLogWriteException(new RuntimeException('Write failed'));

        report($failure);

        Queue::assertNothingPushed();
        Log::shouldHaveReceived('error')->with($failure->getMessage(), Mockery::type('array'))->once();
    }

    public function test_dispatch_guard_prevents_reentry_and_resets_after_dispatch_failure(): void
    {
        $this->setConsole(false);
        Log::spy();
        $this->app->instance('request', Request::create('https://example.test/error'));
        Bus::shouldReceive('dispatch')->twice()->andReturnUsing(function (StoreRequestLogJob $job): void {
            report(new RuntimeException('Queue backend failed during dispatch'));
            throw new RuntimeException('Cannot enqueue request log');
        });

        report(new RuntimeException('First HTTP error'));
        report(new RuntimeException('Second HTTP error'));

        Log::shouldHaveReceived('error')->with('Request log dispatch failed.', Mockery::type('array'))->twice();
        Log::shouldHaveReceived('error')->with('First HTTP error', Mockery::type('array'))->once();
        Log::shouldHaveReceived('error')->with('Second HTTP error', Mockery::type('array'))->once();
    }

    public function test_real_sync_queue_write_failure_does_not_recursively_dispatch(): void
    {
        $this->setConsole(false);
        Log::spy();
        config(['queue.default' => 'sync']);
        $this->app->instance('request', Request::create('https://example.test/error'));
        Schema::shouldReceive('hasTable')->with('request_logs')->once()
            ->andThrow(new RuntimeException('Request log database unavailable'));

        report(new RuntimeException('Original HTTP failure'));

        Log::shouldHaveReceived('error')->with('Original HTTP failure', Mockery::type('array'))->once();
        Log::shouldHaveReceived('error')->with('StoreRequestLogJob exhausted all attempts.', Mockery::type('array'))->once();
        Log::shouldHaveReceived('error')->with('Request log dispatch failed.', Mockery::type('array'))->once();
    }

    public function test_real_worker_retries_with_backoff_then_stops_without_creating_more_jobs(): void
    {
        $this->setConsole(true);
        Log::spy();
        $this->travelTo(now()->startOfSecond());
        $queue = Queue::connection('database');
        // Missing required fields deliberately cause a real database insert failure.
        $queue->push(new StoreRequestLogJob(['exception' => 'Cannot store this record']), '', 'request-log-safety');
        $worker = $this->app->make('queue.worker');
        $options = new WorkerOptions(sleep: 0, maxTries: 99);

        foreach ([10, 60] as $attempt => $delay) {
            $worker->runNextJob('database', 'request-log-safety', $options);
            $pending = DB::table('jobs')->sole();
            $this->assertSame($attempt + 1, $pending->attempts);
            $this->assertSame(now()->timestamp + $delay, $pending->available_at);
            $this->travel($delay)->seconds();
        }

        $worker->runNextJob('database', 'request-log-safety', $options);

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('request_logs', 0);
        Log::shouldHaveReceived('error')->with(
            'StoreRequestLogJob exhausted all attempts.',
            Mockery::on(fn (array $context): bool => $context['attempt'] === 3
                && $context['job_id'] !== null
                && $context['exception_class'] === QueryException::class
                && ! array_key_exists('exception', $context)
                && ! array_key_exists('request_log_payload', $context)),
        )->once();
    }

    public function test_final_failure_summary_is_bounded_and_excludes_original_payload(): void
    {
        Log::spy();
        $payload = $this->payload(str_repeat('PRIVATE_PAYLOAD', 10000));
        $payload['path'] = str_repeat('خطا', 1000);
        $failure = new RequestLogWriteException(new RuntimeException(str_repeat('failure ', 10000)));

        (new StoreRequestLogJob($payload))->failed($failure);

        Log::shouldHaveReceived('error')->with(
            'StoreRequestLogJob exhausted all attempts.',
            Mockery::on(fn (array $context): bool => strlen($context['path']) <= 256
                && mb_check_encoding($context['path'], 'UTF-8')
                && strlen($context['exception_message']) <= 1200
                && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'PRIVATE_PAYLOAD')
                && ! array_key_exists('exception', $context)
                && ! array_key_exists('request_log_payload', $context)),
        )->once();
    }

    private function setConsole(bool $console): void
    {
        (new ReflectionProperty(Application::class, 'isRunningInConsole'))->setValue($this->app, $console);
    }

    private function payload(?string $exception = 'Request failure'): array
    {
        return [
            'log_type' => 'reportable_exception',
            'occurred_at' => now(),
            'method' => 'GET',
            'url' => 'https://example.test/error',
            'path' => 'error',
            'status_code' => 500,
            'status_family' => 500,
            'is_reportable_status' => true,
            'exception' => $exception,
        ];
    }
}

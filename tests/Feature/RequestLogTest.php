<?php

namespace Tests\Feature;

use App\Jobs\StoreRequestLogJob;
use App\Models\RequestLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class RequestLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_incoming_request_middleware_queues_successful_request(): void
    {
        Queue::fake();

        Route::get('/request-log-ok', fn () => response('ok'))->name('request-log.ok');

        $this->get('/request-log-ok?source=test')->assertOk();

        Queue::assertPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['method'] === 'GET'
                && $job->data['log_type'] === 'incoming_request'
                && $job->data['path'] === 'request-log-ok'
                && $job->data['route_name'] === 'request-log.ok'
                && $job->data['status_code'] === 200
                && $job->data['status_family'] === 200
                && $job->data['is_reportable_status'] === false
                && $job->data['query'] === ['source' => 'test'];
        });
    }

    public function test_reportable_response_middleware_queues_300_and_400_status_families(): void
    {
        Queue::fake();

        Route::get('/request-log-redirect', fn () => redirect('/'))->name('request-log.redirect');
        Route::get('/request-log-not-found', fn () => abort(404))->name('request-log.not-found');

        $this->get('/request-log-redirect')->assertRedirect('/');
        $this->get('/request-log-not-found')->assertNotFound();

        Queue::assertPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['log_type'] === 'reportable_response'
                && $job->data['path'] === 'request-log-redirect'
                && $job->data['status_code'] === 302
                && $job->data['status_family'] === 300
                && $job->data['is_reportable_status'] === true;
        });

        Queue::assertPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['log_type'] === 'reportable_response'
                && $job->data['path'] === 'request-log-not-found'
                && $job->data['status_code'] === 404
                && $job->data['status_family'] === 400
                && $job->data['is_reportable_status'] === true;
        });

        Queue::assertNotPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['log_type'] === 'incoming_request'
                && in_array($job->data['path'], ['request-log-redirect', 'request-log-not-found'], true);
        });
    }

    public function test_global_exception_reporter_queues_500_status_family(): void
    {
        Queue::fake();

        Route::get('/request-log-error', fn () => throw new RuntimeException('Request log test exception'))->name('request-log.error');

        try {
            $this->get('/request-log-error');
        } catch (RuntimeException) {
            //
        }

        Queue::assertPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['log_type'] === 'reportable_exception'
                && $job->data['path'] === 'request-log-error'
                && $job->data['status_code'] === 500
                && $job->data['status_family'] === 500
                && $job->data['is_reportable_status'] === true
                && str_contains((string) $job->data['exception'], RuntimeException::class);
        });

        Queue::assertNotPushed(StoreRequestLogJob::class, function (StoreRequestLogJob $job): bool {
            return $job->data['log_type'] === 'incoming_request'
                && $job->data['path'] === 'request-log-error';
        });
    }

    public function test_store_request_log_job_writes_to_database(): void
    {
        $job = new StoreRequestLogJob([
            'occurred_at' => now(),
            'log_type' => 'reportable_exception',
            'method' => 'POST',
            'url' => 'https://example.test/forms/contact?source=test',
            'path' => 'forms/contact',
            'route_name' => 'forms.contact.store',
            'route_action' => 'App\Http\Controllers\ContactFormController@store',
            'status_code' => 500,
            'status_family' => 500,
            'is_reportable_status' => true,
            'duration_ms' => 123,
            'user_id' => null,
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'referer' => 'https://example.test/page/contact',
            'exception' => RuntimeException::class.': boom',
            'query' => ['source' => 'test'],
        ]);

        $job->handle();

        $this->assertDatabaseHas('request_logs', [
            'log_type' => 'reportable_exception',
            'method' => 'POST',
            'path' => 'forms/contact',
            'route_name' => 'forms.contact.store',
            'status_code' => 500,
            'status_family' => 500,
            'is_reportable_status' => true,
        ]);

        $this->assertSame(['source' => 'test'], RequestLog::query()->firstOrFail()->query);
    }

    public function test_store_request_log_job_logs_final_failure_context(): void
    {
        $payload = [
            'status_code' => 500,
            'status_family' => 500,
            'path' => 'request-log-error',
        ];
        $exception = new RuntimeException('database write failed');

        Log::shouldReceive('error')
            ->once()
            ->with('StoreRequestLogJob exhausted all attempts.', [
                'exception' => $exception,
                'exception_class' => RuntimeException::class,
                'exception_message' => 'database write failed',
                'request_log_payload' => $payload,
            ]);

        (new StoreRequestLogJob($payload))->failed($exception);
    }
}

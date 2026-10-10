<?php

namespace Tests\Unit;

use App\Jobs\QueueProbeJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class QueueProbeCommandTest extends TestCase
{
    public function test_probe_refuses_a_database_queue(): void
    {
        config(['queue.default' => 'database']);
        Queue::fake();

        $this->artisan('partsmall:queue-probe')->assertFailed();
        Queue::assertNothingPushed();
    }

    public function test_status_requires_a_valid_uuid(): void
    {
        $this->configureDedicatedQueue();
        Redis::shouldReceive('connection')->never();

        $this->artisan('partsmall:queue-probe', ['--status' => true])->assertFailed();
        $this->artisan('partsmall:queue-probe', ['--id' => 'invalid'])->assertFailed();
    }

    public function test_probe_dispatches_on_the_redis_connection(): void
    {
        $this->configureDedicatedQueue();
        Queue::fake();
        $id = '3a26c27a-66cf-4cea-a3d4-daa162579472';

        $this->artisan('partsmall:queue-probe', ['--id' => $id, '--fail-first' => true])->assertSuccessful();

        Queue::assertPushed(QueueProbeJob::class, fn (QueueProbeJob $job): bool => $job->connection === 'redis' && $job->probeId === $id && $job->failFirst
        );
    }

    public function test_status_reads_only_the_requested_probe(): void
    {
        $this->configureDedicatedQueue();
        Queue::fake();
        $id = '3a26c27a-66cf-4cea-a3d4-daa162579472';
        Redis::shouldReceive('connection')->once()->with('queue')->andReturnSelf();
        Redis::shouldReceive('get')->once()->with('partsmall:queue-probe:'.$id.':attempts')->andReturn('2');
        Redis::shouldReceive('get')->once()->with('partsmall:queue-probe:'.$id.':completed')->andReturn('1');

        $this->artisan('partsmall:queue-probe', ['--id' => $id, '--status' => true])
            ->expectsOutput(json_encode(['id' => $id, 'attempts' => 2, 'completed' => 1]))
            ->assertSuccessful();
        Queue::assertNothingPushed();
    }

    private function configureDedicatedQueue(): void
    {
        config(['queue.default' => 'redis', 'queue.connections.redis.connection' => 'queue']);
    }
}

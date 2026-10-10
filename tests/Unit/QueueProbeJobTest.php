<?php

namespace Tests\Unit;

use App\Jobs\QueueProbeJob;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class QueueProbeJobTest extends TestCase
{
    public function test_first_attempt_fails_before_marking_completion(): void
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('incr')->once()->with('partsmall:queue-probe:test:attempts');
        $connection->shouldReceive('expire')->once()->with('partsmall:queue-probe:test:attempts', 86400);
        $connection->shouldNotReceive('setnx');
        Redis::shouldReceive('connection')->once()->with('queue')->andReturn($connection);
        $job = new QueueProbeJob('test', true);
        $queuedJob = Mockery::mock(Job::class);
        $queuedJob->shouldReceive('attempts')->once()->andReturn(1);
        $job->setJob($queuedJob);

        $this->expectException(RuntimeException::class);
        $job->handle();
    }

    public function test_retry_uses_an_idempotent_completion_marker(): void
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('incr')->once()->with('partsmall:queue-probe:test:attempts');
        $connection->shouldReceive('expire')->once()->with('partsmall:queue-probe:test:attempts', 86400);
        $connection->shouldReceive('setnx')->once()->with('partsmall:queue-probe:test:completed', 1)->andReturn(0);
        $connection->shouldReceive('expire')->once()->with('partsmall:queue-probe:test:completed', 86400);
        Redis::shouldReceive('connection')->once()->with('queue')->andReturn($connection);
        $job = new QueueProbeJob('test', true);
        $queuedJob = Mockery::mock(Job::class);
        $queuedJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->setJob($queuedJob);

        $job->handle();
        $this->assertSame(2, $job->tries);
    }
}

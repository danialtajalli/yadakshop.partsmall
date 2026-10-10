<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

class QueueProbeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 2;

    public int $timeout = 30;

    public int $backoff = 2;

    public function __construct(
        public readonly string $probeId,
        public readonly bool $failFirst = false,
    ) {}

    public function handle(): void
    {
        $redis = Redis::connection('queue');
        $key = 'partsmall:queue-probe:'.$this->probeId;
        $redis->incr($key.':attempts');
        $redis->expire($key.':attempts', 86400);

        if ($this->failFirst && $this->attempts() === 1) {
            throw new RuntimeException('Expected first-attempt failure for the Partsmall queue probe.');
        }

        $redis->setnx($key.':completed', 1);
        $redis->expire($key.':completed', 86400);
    }
}

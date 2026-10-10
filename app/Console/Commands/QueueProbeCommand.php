<?php

namespace App\Console\Commands;

use App\Jobs\QueueProbeJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class QueueProbeCommand extends Command
{
    protected $signature = 'partsmall:queue-probe
                            {--id= : UUID for the probe}
                            {--fail-first : Fail the first attempt to exercise retry}
                            {--status : Read probe status without dispatching a job}';

    protected $description = 'Verify the Redis worker and retry path using an isolated, expiring probe';

    public function handle(): int
    {
        if (config('queue.default') !== 'redis' || config('queue.connections.redis.connection') !== 'queue') {
            $this->error('The dedicated Redis queue must be configured before running this probe.');

            return self::FAILURE;
        }

        $id = $this->option('id');
        if (($id !== null && ! Str::isUuid($id)) || ($this->option('status') && $id === null)) {
            $this->error('Supply a valid --id UUID when reading status.');

            return self::FAILURE;
        }
        $id ??= (string) Str::uuid();

        if ($this->option('status')) {
            $redis = Redis::connection('queue');
            $key = 'partsmall:queue-probe:'.$id;
            $this->line(json_encode([
                'id' => $id,
                'attempts' => (int) $redis->get($key.':attempts'),
                'completed' => (int) $redis->get($key.':completed'),
            ], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        QueueProbeJob::dispatch($id, (bool) $this->option('fail-first'))->onConnection('redis');
        $this->line(json_encode(['id' => $id, 'dispatched' => true], JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}

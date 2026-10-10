<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    DB::select('SELECT 1');
    if (config('queue.default') === 'redis') {
        Redis::connection(config('queue.connections.redis.connection'))->ping();
    }
    if (config('cache.default') === 'redis') {
        Redis::connection(config('cache.stores.redis.connection'))->ping();
    }
    foreach (glob('/proc/[0-9]*/cmdline') ?: [] as $file) {
        $arguments = explode("\0", (string) @file_get_contents($file));
        if (in_array('artisan', $arguments, true)
            && (in_array('queue:work', $arguments, true) || in_array('queue:listen', $arguments, true))) {
            exit(0);
        }
    }
} catch (Throwable) {
    exit(1);
}

exit(1);

<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$settings = [];
foreach (['app.env', 'cache.default', 'cache.serializable_classes', 'queue.default', 'queue.connections.redis.connection', 'queue.connections.redis.retry_after', 'queue.connections.database.retry_after', 'database.redis.client', 'database.redis.default.host', 'database.redis.cache.host', 'database.redis.queue.host', 'session.driver', 'logging.default'] as $key) {
    $settings[$key] = config($key);
}
echo json_encode(['effective_settings' => $settings], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\n";

foreach (['jobs', 'failed_jobs', 'cache', 'request_logs'] as $table) {
    if (! Schema::hasTable($table)) {
        continue;
    }
    $query = DB::table($table);
    $summary = ['table' => $table, 'rows' => $query->count()];
    if ($table === 'jobs') {
        $summary['queues'] = DB::table($table)
            ->selectRaw('queue, count(*) as total, sum(reserved_at is not null) as reserved, min(created_at) as oldest_created_at')
            ->groupBy('queue')->get()->toArray();
    }
    if ($table === 'request_logs') {
        $summary['oldest_created_at'] = $query->min('created_at');
        $summary['newest_created_at'] = $query->max('created_at');
    }
    echo json_encode($summary, JSON_THROW_ON_ERROR)."\n";
}

if (Schema::hasTable('cache')) {
    $rows = DB::table('cache')->where(function ($query): void {
        foreach (['%catalog:%', '%product:%', '%directory-listing:%', '%home:%', '%pages:%'] as $pattern) {
            $query->orWhere('key', 'like', $pattern);
        }
    })->limit(40)->get(['key', 'value', 'expiration']);
    foreach ($rows as $row) {
        $value = @unserialize($row->value, ['allowed_classes' => false]);
        echo json_encode(['cache_key' => $row->key, 'decoded_type' => get_debug_type($value), 'expiration' => $row->expiration], JSON_THROW_ON_ERROR)."\n";
    }
}

foreach (['app/Support/SafeCache.php', 'app/Services/ProductService.php', 'app/Services/VehicleCatalogService.php', 'config/cache.php'] as $file) {
    echo json_encode(['file' => $file, 'sha256' => hash_file('sha256', getcwd().'/'.$file)], JSON_THROW_ON_ERROR)."\n";
}

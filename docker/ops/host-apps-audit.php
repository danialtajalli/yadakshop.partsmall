<?php

declare(strict_types=1);

require '/var/www/partsmall/vendor/autoload.php';

foreach (glob('/var/www/*/.env') ?: [] as $file) {
    try {
        $values = Dotenv\Dotenv::parse(file_get_contents($file));
        $selected = [];
        foreach (['APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'CACHE_STORE', 'QUEUE_CONNECTION', 'REDIS_HOST', 'REDIS_PORT', 'REDIS_DB', 'REDIS_CACHE_DB', 'REDIS_PREFIX'] as $key) {
            $selected[$key] = $values[$key] ?? null;
        }
        echo json_encode(['path' => dirname($file), 'environment_settings' => $selected], JSON_THROW_ON_ERROR)."\n";
    } catch (Throwable) {
        echo json_encode(['path' => dirname($file), 'error' => 'Environment could not be parsed; contents withheld.'])."\n";
    }
}

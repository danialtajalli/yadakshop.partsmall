<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if ($argc !== 4) {
    fwrite(STDERR, "Usage: php docker/init-production-env.php SOURCE TARGET MYSQL_VERSION\n");
    exit(2);
}

[$script, $source, $target, $mysqlVersion] = $argv;

if (file_exists($target) || is_link($target)) {
    fwrite(STDOUT, "Target already exists; no changes made.\n");
    exit(0);
}

try {
    if (! preg_match('/\A8\.(?:0|4)(?:\.\d+)?\z/', $mysqlVersion)) {
        throw new RuntimeException('Select a reviewed MySQL 8.0 or 8.4 version.');
    }

    $contents = file_get_contents($source);
    if ($contents === false) {
        throw new RuntimeException('Cannot read source.');
    }

    $live = Dotenv::parse($contents);
    if (empty($live['APP_KEY']) || empty($live['DB_DATABASE'])
        || ($live['DB_CONNECTION'] ?? '') !== 'mysql'
        || parse_url($live['APP_URL'] ?? '', PHP_URL_SCHEME) !== 'https') {
        throw new RuntimeException('Source must have a live key, HTTPS URL, and MySQL database.');
    }

    $uploads = realpath(dirname($source).'/storage/app/public');
    if ($uploads === false || ! is_dir($uploads)) {
        throw new RuntimeException('Cannot locate source uploads.');
    }

    $overrides = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'WEB_PORT' => '8000',
        'MYSQL_ADMIN_PORT' => '3307',
        'MYSQL_VERSION' => $mysqlVersion,
        'DOCKER_SUBNET' => '172.30.50.0/24',
        'TRUSTED_PROXIES' => '172.30.50.0/24',
        'PRODUCTION_VOLUME_PREFIX' => 'partsmall-prod',
        'UPLOAD_SOURCE' => $uploads,
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'mysql',
        'DB_PORT' => '3306',
        'DB_URL' => '',
        'DB_SOCKET' => '',
        'DB_USERNAME' => 'partsmall',
        'DB_PASSWORD' => bin2hex(random_bytes(32)),
        'MYSQL_ROOT_PASSWORD' => bin2hex(random_bytes(32)),
        'QUEUE_CONNECTION' => 'redis',
        'DB_QUEUE_RETRY_AFTER' => '180',
        'CACHE_STORE' => 'redis',
        'REDIS_CLIENT' => 'phpredis',
        'REDIS_URL' => '',
        'REDIS_HOST' => 'redis-queue',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '0',
        'REDIS_USERNAME' => '',
        'REDIS_PASSWORD' => '',
        'REDIS_CACHE_URL' => '',
        'REDIS_CACHE_HOST' => 'redis-cache',
        'REDIS_CACHE_PORT' => '6379',
        'REDIS_CACHE_DB' => '0',
        'REDIS_CACHE_PASSWORD' => '',
        'REDIS_CACHE_CONNECTION' => 'cache',
        'REDIS_CACHE_LOCK_CONNECTION' => 'queue',
        'REDIS_QUEUE_URL' => '',
        'REDIS_QUEUE_HOST' => 'redis-queue',
        'REDIS_QUEUE_PORT' => '6379',
        'REDIS_QUEUE_DB' => '0',
        'REDIS_QUEUE_USERNAME' => '',
        'REDIS_QUEUE_PASSWORD' => '',
        'REDIS_QUEUE_CONNECTION' => 'queue',
        'REDIS_QUEUE_RETRY_AFTER' => '180',
        'REDIS_QUEUE_BLOCK_FOR' => '5',
        'REDIS_VERSION' => '7.4.11-alpine',
        'REDIS_CACHE_MAXMEMORY' => '256mb',
        'REDIS_QUEUE_MAXMEMORY' => '256mb',
        'MYSQL_BINLOG_EXPIRE_LOGS_SECONDS' => '2592000',
        'DOCKER_LOG_MAX_SIZE' => '20m',
        'DOCKER_LOG_MAX_FILES' => '5',
        'SESSION_DRIVER' => 'database',
        'SESSION_SECURE_COOKIE' => 'true',
        'SCOUT_DRIVER' => 'collection',
        'LOG_CHANNEL' => 'stderr',
    ];

    // Preserve the live text and quoting; only container-specific values change.
    $production = rtrim($contents, "\r\n")."\n\n# Docker production overrides (last assignment wins).\n";
    foreach ($overrides as $key => $value) {
        $production .= $key.'='.json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n";
    }

    $parsed = Dotenv::parse($production);
    foreach ($live as $key => $value) {
        if (! array_key_exists($key, $overrides) && ($parsed[$key] ?? null) !== $value) {
            throw new RuntimeException('A live value was not preserved.');
        }
    }
    foreach ($overrides as $key => $value) {
        if (($parsed[$key] ?? null) !== $value) {
            throw new RuntimeException('An override was not preserved.');
        }
    }

    $previousMask = umask(0077);
    try {
        $file = fopen($target, 'x');
        if ($file === false) {
            throw new RuntimeException('Cannot create target exclusively.');
        }

        try {
            // Restrict inherited ACLs before writing any credentials.
            if (! chmod($target, 0600) || fwrite($file, $production) !== strlen($production)) {
                throw new RuntimeException('Cannot securely write target.');
            }
        } finally {
            fclose($file);
        }
    } finally {
        umask($previousMask);
    }

    fwrite(STDOUT, "Created production environment; live settings preserved, Docker passwords generated.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Production environment preparation failed; details withheld to protect credentials.\n");
    exit(1);
}

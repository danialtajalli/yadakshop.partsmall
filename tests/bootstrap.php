<?php

declare(strict_types=1);

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

require __DIR__.'/../vendor/autoload.php';

$root = dirname(__DIR__);

if (is_file($root.'/.env')) {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

if (is_file($root.'/.env.testing')) {
    Dotenv\Dotenv::createImmutable($root, '.env.testing')->safeLoad();
}

/*
 * Force an isolated in-memory sqlite database before Laravel boots.
 * Without this, RefreshDatabase can migrate:fresh the local MySQL database
 * when .env or a cached config.php override phpunit.xml.
 */
foreach ([
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'DB_HOST' => '',
    'DB_PORT' => '',
    'DB_USERNAME' => '',
    'DB_PASSWORD' => '',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$cachedConfig = $root.'/bootstrap/cache/config.php';

if (is_file($cachedConfig)) {
    unlink($cachedConfig);
}

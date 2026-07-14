<?php

declare(strict_types=1);

/**
 * Backward-compatible entry point for legacy iframe URLs (/forms/contact.php).
 * Delegates to the Laravel contact form routes.
 */

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '/forms/contact'.($query !== '' ? '?'.$query : '');

if ($method === 'GET') {
    header('Location: '.$target, true, 302);
    exit;
}

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';

/** @var \Illuminate\Contracts\Http\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create(
    $target,
    $method,
    $_POST,
    $_COOKIE,
    $_FILES,
    $_SERVER,
);

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);

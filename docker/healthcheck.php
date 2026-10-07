<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    DB::select('SELECT 1');
    $socket = @fsockopen('127.0.0.1', 9000, $errorCode, $errorMessage, 2);

    if ($socket === false) {
        exit(1);
    }

    fclose($socket);
} catch (Throwable) {
    exit(1);
}

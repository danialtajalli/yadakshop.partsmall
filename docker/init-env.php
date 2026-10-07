<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root.'/.env.docker';

if (is_file($target)) {
    fwrite(STDOUT, ".env.docker already exists; no changes made.\n");
    exit(0);
}

$template = file_get_contents($root.'/.env.docker.example');

if ($template === false) {
    throw new RuntimeException('Cannot read .env.docker.example.');
}

$template = str_replace(
    ["APP_KEY=\n", "DB_PASSWORD=\n", "MYSQL_ROOT_PASSWORD=\n"],
    [
        'APP_KEY=base64:'.base64_encode(random_bytes(32))."\n",
        'DB_PASSWORD='.bin2hex(random_bytes(24))."\n",
        'MYSQL_ROOT_PASSWORD='.bin2hex(random_bytes(24))."\n",
    ],
    str_replace("\r\n", "\n", $template),
);

$file = fopen($target, 'x');

if ($file === false || fwrite($file, $template) !== strlen($template)) {
    throw new RuntimeException('Cannot create .env.docker.');
}

fclose($file);
chmod($target, 0600);
fwrite(STDOUT, "Created .env.docker with a new application key and database passwords.\n");

<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$parser = new LegacyInsertParser(file_get_contents(__DIR__.'/../partsmall_db.sql'));

$empty = [];
foreach ($parser->rows('part') as $row) {
    if (trim($row['name'] ?? '') === '') {
        $empty[] = $row;
    }
}

echo 'empty name parts: '.count($empty).PHP_EOL;
foreach ($empty as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

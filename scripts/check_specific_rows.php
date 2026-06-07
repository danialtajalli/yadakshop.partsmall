<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$parser = new LegacyInsertParser(file_get_contents(__DIR__.'/../partsmall_db.sql'));

foreach ([121, 131, 324, 320] as $id) {
    foreach ($parser->rows('shop') as $row) {
        if ((int) $row['id'] === $id) {
            echo 'shop '.$id.': '.json_encode(array_intersect_key($row, array_flip(['id', 'name', 'latin'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
    foreach ($parser->rows('part') as $row) {
        if ((int) $row['id'] === $id) {
            echo 'part '.$id.': '.json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
}

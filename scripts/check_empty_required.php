<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$parser = new LegacyInsertParser(file_get_contents(__DIR__.'/../partsmall_db.sql'));

$checks = [
    ['part', 'name'],
    ['part', 'latin'],
    ['shop', 'name'],
    ['shop', 'latin'],
    ['repair_shop', 'name'],
    ['repair_shop', 'latin'],
    ['car', 'name'],
    ['car', 'latin'],
    ['model', 'name'],
    ['model', 'latin'],
    ['company', 'name'],
    ['company', 'latin'],
    ['state', 'name'],
    ['state', 'slug'],
    ['city', 'name'],
    ['categorypart', 'name'],
    ['type_repair', 'name'],
    ['wage', 'name'],
];

foreach ($checks as [$table, $column]) {
    $empty = [];
    foreach ($parser->rows($table) as $row) {
        if (trim((string) ($row[$column] ?? '')) === '') {
            $empty[] = (int) $row['id'];
        }
    }
    if ($empty !== []) {
        echo "$table.$column empty: ".count($empty).' ids='.implode(',', array_slice($empty, 0, 20)).PHP_EOL;
    }
}

<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$sql = file_get_contents(__DIR__.'/../partsmall_db.sql');
$parser = new LegacyInsertParser($sql);

$cars = $parser->rows('car');
echo 'cars: '.count($cars).PHP_EOL;
if ($cars) {
    echo json_encode(array_intersect_key($cars[0], array_flip(['id', 'name', 'cat', 'latin', 'custom1'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
    echo json_encode(array_intersect_key($cars[1], array_flip(['id', 'name', 'cat', 'latin', 'custom1'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$parts = $parser->rows('part');
echo 'parts: '.count($parts).PHP_EOL;
if ($parts) {
    echo json_encode(array_intersect_key($parts[0], array_flip(['id', 'name', 'wage_id', 'type_repair_id', 'categorypart_id', 'latin'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$shops = $parser->rows('shop');
echo 'shops: '.count($shops).PHP_EOL;
if ($shops) {
    echo json_encode(array_intersect_key($shops[0], array_flip(['id', 'name', 'latin', 'cat', 'part', 'sort'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
}

echo 'repair_shops: '.count($parser->rows('repair_shop')).PHP_EOL;
echo 'companies: '.count($parser->rows('company')).PHP_EOL;
echo 'models: '.count($parser->rows('model')).PHP_EOL;

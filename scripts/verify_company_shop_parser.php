<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$parser = new LegacyInsertParser(file_get_contents(__DIR__.'/../partsmall_db.sql'));
$shops = $parser->rows('shop');
$links = 0;

foreach ($shops as $shop) {
    $cat = trim((string) ($shop['cat'] ?? ''));
    if ($cat !== '') {
        $links += count(array_filter(explode(',', $cat)));
    }
}

echo 'shops parsed: '.count($shops).PHP_EOL;
echo 'cat links (raw): '.$links.PHP_EOL;
echo 'sample shop 1 cat: '.($shops[0]['cat'] ?? 'n/a').PHP_EOL;

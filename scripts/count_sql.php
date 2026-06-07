<?php

require __DIR__.'/../vendor/autoload.php';

use App\Support\Legacy\LegacyInsertParser;

$parser = new LegacyInsertParser(file_get_contents(__DIR__.'/../partsmall_db.sql'));

foreach (['state','city','categorypart','type_repair','wage','part','repair_shop','shop','comments','page','users'] as $table) {
    echo $table.': '.count($parser->rows($table)).PHP_EOL;
}

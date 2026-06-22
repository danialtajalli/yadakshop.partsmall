<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'states', 'cities', 'companies', 'models', 'cars',
    'parts_categories', 'repair_categories', 'wages', 'parts',
    'repair_shops', 'shops', 'comments', 'pages', 'users',
    'phones', 'links', 'images',
    'car_model', 'part_shop', 'parts_category_shop',
    'part_repair_category', 'part_wage', 'repair_category_repair_shop',
];

foreach ($tables as $table) {
    echo $table.': '.Illuminate\Support\Facades\DB::table($table)->count().PHP_EOL;
}

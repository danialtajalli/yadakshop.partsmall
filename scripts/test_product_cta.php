<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/product/hyundai/santafe/new/arm', 'GET');
$response = $kernel->handle($request);

$html = $response->getContent();
echo $response->getStatusCode().PHP_EOL;
echo (str_contains($html, 'ثبت‌نام') ? 'OK signup ' : 'MISS signup ').PHP_EOL;
echo (str_contains($html, 'تلگرام') ? 'OK telegram ' : 'MISS telegram ').PHP_EOL;
echo (str_contains($html, 'layouts.partials') ? '' : (str_contains($html, 'sticky top-0') ? 'OK header ' : 'MISS header ')).PHP_EOL;

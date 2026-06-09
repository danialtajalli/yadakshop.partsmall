<?php

use App\Http\Controllers\PartSelectionController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('car/{company}/{car}/{model}', [PartSelectionController::class, 'show'])
    ->name('car.parts');

Route::get('product/{company}/{car}/{model}/{part}', [ProductController::class, 'show'])
    ->name('product.show');

<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartSelectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RepresentationController;
use App\Http\Controllers\RepairShopController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('shops', [ShopController::class, 'index'])->name('shops.index');
Route::get('profile/{shop_slug}', [ShopController::class, 'show'])->name('shop.profile');
Route::get('repair_shops', [RepairShopController::class, 'index'])->name('repair-shops.index');
Route::get('repair_profile/{repair_shop_slug}', [RepairShopController::class, 'show'])->name('repair-shop.profile');
Route::get('representations', [RepresentationController::class, 'index'])->name('representations.index');
Route::get('representation/{representation_slug}', [RepresentationController::class, 'show'])->name('representation.profile');

Route::get('part/{part}', [PartController::class, 'show'])->name('part.show');

Route::get('car/{company}/{car}/{model}', [PartSelectionController::class, 'show'])
    ->name('car.parts');

Route::get('product/{company}/{car}/{model}/{part}', [ProductController::class, 'show'])
    ->name('product.show');

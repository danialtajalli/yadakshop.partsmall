<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartSelectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RepresentationController;
use App\Http\Controllers\RepairShopController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\VehicleCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('search', SearchController::class)->name('search.index');

Route::get('company', [VehicleCatalogController::class, 'companies'])->name('companies.index');
Route::get('company/{company}', [VehicleCatalogController::class, 'cars'])->name('cars.index');

Route::get('car/{company}/{car}', [VehicleCatalogController::class, 'models'])->name('models.index');
Route::get('car/{company}/{car}/{model}', [PartSelectionController::class, 'show'])
    ->name('car.parts.vehicle');

Route::get('shops', [ShopController::class, 'index'])->name('shops.index');
Route::get('profile/{shop_slug}', [ShopController::class, 'show'])->name('shop.profile');
Route::get('repair_shops', [RepairShopController::class, 'index'])->name('repair-shops.index');
Route::get('repair_profile/{repair_shop_slug}', [RepairShopController::class, 'show'])->name('repair-shop.profile');
Route::get('representations', [RepresentationController::class, 'index'])->name('representations.index');
Route::get('representation/{representation_slug}', [RepresentationController::class, 'show'])->name('representation.profile');

Route::get('part', [PartSelectionController::class, 'show'])->name('car.parts');
Route::get('part/{part}', [PartController::class, 'show'])->name('part.show');

Route::get('product/{company}/{car}/{model}/{part}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('page/contact', [ContactController::class, 'show']);
Route::get('page/{slug}', [PageController::class, 'show'])
    ->name('page.show');

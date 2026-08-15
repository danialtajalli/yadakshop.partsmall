<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PartSelectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RepresentationController;
use App\Http\Controllers\RepairShopController;
use App\Http\Controllers\Scripts\ConsolidateModelCategoriesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShopCommentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\VehicleCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('search', SearchController::class)->name('search.index');

Route::get('company', [VehicleCatalogController::class, 'companies'])->name('companies.index');
Route::get('company/{company}', [VehicleCatalogController::class, 'cars'])->name('cars.index');

Route::get('car/{company}/{car}', [VehicleCatalogController::class, 'models'])->name('models.index');
Route::match(['get', 'post'], 'car/{company}/{car}/{model}', [PartSelectionController::class, 'show'])
    ->name('car.parts.vehicle');

Route::match(['get', 'post'], 'shops', [ShopController::class, 'index'])->name('shops.index');
Route::match(['get', 'post'], 'shops/{company:slug}', [ShopController::class, 'byCompany'])
    ->name('shops.company');
Route::get('profile/{shop_slug}', [ShopController::class, 'show'])->name('shop.profile');
Route::post('profile/{shop_slug}/comments', [ShopCommentController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('shop.comments.store');
Route::match(['get', 'post'], 'repair_shops', [RepairShopController::class, 'index'])->name('repair-shops.index');
Route::get('carservice/{id}/{slug}', [RepairShopController::class, 'show'])
    ->whereNumber('id')
    ->name('repair-shop.profile');
Route::match(['get', 'post'], 'representations', [RepresentationController::class, 'index'])->name('representations.index');
Route::get('representation/{representation_slug}', [RepresentationController::class, 'show'])->name('representation.profile');

Route::match(['get', 'post'], 'part', [PartSelectionController::class, 'show'])->name('car.parts');
Route::match(['get', 'post'], 'part/{part}', [PartController::class, 'show'])->name('part.show');

Route::get('product/{company}/{car}/{model}/{part}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('forms/contact', function () {return abort(404);})->name('forms.contact.create');
Route::post('forms/contact', [ContactFormController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('forms.contact.store');

Route::get('page/contact', [ContactController::class, 'show'])->name('page.contact');
Route::get('page/{slug}', [PageController::class, 'show'])
    ->name('page.show');

Route::get('scripts/consolidate-model-categories', ConsolidateModelCategoriesController::class)
    ->name('scripts.consolidate-model-categories');

<?php

use App\Http\Controllers\Api\ShopCommentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:6,1'])->group(function (): void {
    Route::post('shops/{shop_slug}/comments', [ShopCommentController::class, 'store'])
        ->name('api.shop.comments.store');
});

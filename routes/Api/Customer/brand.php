<?php

use App\Http\Controllers\Api\Customer\Brands\BrandController;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:api')->group(function () {
    Route::prefix('brands')->group(function () {
        Route::get('', [BrandController::class, 'index']);
        Route::get('featured', [BrandController::class, 'featuredBrands']);
        Route::get('{slug}', [BrandController::class, 'show']);
    });
});

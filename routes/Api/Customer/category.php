<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Category\CategoryStatusController;
use App\Http\Controllers\Api\Customer\Category\CategoryController;


Route::middleware('auth:api')->group(function () {

    Route::prefix('categories')->group(function () {
        Route::get('', [CategoryController::class, 'index']);
        Route::get('{slug}', [CategoryController::class, 'show']);
        Route::get('featured', [CategoryController::class, 'featuredCategories']);
    });

});

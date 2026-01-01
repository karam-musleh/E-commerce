<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\Admin\Brand\BrandController;
use App\Http\Controllers\Api\Admin\Brand\BrandStatusController;
use App\Http\Controllers\Api\Admin\Category\CategoryController;
use App\Http\Controllers\Api\Admin\Category\CategoryStatusController;



Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('v1/admin/categories')
    ->group(function () {
        Route::apiResource('', CategoryController::class)->parameter('categories', 'slug');
        Route::get('featured', [CategoryStatusController::class, 'featuredCategories']);
        Route::patch('{slug}/toggle-featured', [CategoryStatusController::class, 'toggleFeatured']);
        Route::patch('{slug}/status', [CategoryStatusController::class, 'updateStatus']);
    });



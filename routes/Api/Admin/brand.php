<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\Admin\Brand\BrandController;
use App\Http\Controllers\Api\Admin\Brand\BrandStatusController;

// Route::middleware(['auth:api', AdminMiddleware::class])
//     ->prefix('admin/brands')
//     ->group(function () {
//         // Route::apiResource('brands', BrandController::class)->parameter('brands', 'slug')->parameter('brands', 'slug');

//     });
Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('v1/admin/brands')
    ->group(function () {
        Route::get('featured', [BrandStatusController::class, 'featuredBrands']);
            Route::patch('{slug}/toggle-featured', [BrandStatusController::class, 'toggleFeatured']);
            Route::patch('{slug}/status', [BrandStatusController::class, 'updateStatus']);
    });
Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('v1/admin')
    ->group(function () {
        Route::apiResource('brands', BrandController::class)->parameter('brands', 'slug');

    });

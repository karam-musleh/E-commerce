<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\admin\Product\ReviewController;
use App\Http\Controllers\Api\Admin\Product\ProductController;
// use App\Http\Controllers\Api\Admin\Product\ReviewController;
use App\Http\Controllers\Api\admin\Product\FlashSaleController;
use App\Http\Controllers\Api\Admin\Product\DailyProductController;

Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('v1/admin')
    ->group(function () {
        Route::apiResource('products', ProductController::class)->parameter('products', 'slug');
        Route::apiResource('daily-deals', DailyProductController::class)->parameters(['daily-deals' => 'id']);
        Route::apiResource('flash-sales', FlashSaleController::class);
        // Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']);
        // Route::get('/reviews', [ReviewController::class, 'myReviews']);



        Route::get('reviews', [ReviewController::class, 'index']);

        // الموافقة على مراجعة معينة
        Route::post('reviews/{review}/approve', [ReviewController::class, 'approve']);

        // رفض مراجعة معينة
        Route::post('reviews/{review}/reject', [ReviewController::class, 'reject']);

        // حذف مراجعة معينة
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
    });
// Route::prefix('admin')->middleware(['auth:api', 'admin'])->group(function () {

//     // جلب كل الريفيوز مع الفلاتر (status, rating, product_id)
//     Route::get('reviews', [ReviewController::class, 'index']);

//     // الموافقة على مراجعة معينة
//     Route::post('reviews/{review}/approve', [ReviewController::class, 'approve']);

//     // رفض مراجعة معينة
//     Route::post('reviews/{review}/reject', [ReviewController::class, 'reject']);

//     // حذف مراجعة معينة
//     Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
// });

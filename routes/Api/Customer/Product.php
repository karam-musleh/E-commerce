
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\Customer\Product\ReviewController;
use App\Http\Controllers\Api\Customer\Product\ProductController;
use App\Http\Controllers\Api\Customer\Product\FlashSaleController;
use App\Http\Controllers\Api\Customer\Product\DailyProductController;
// use App\Http\Controllers\Api\Admin\Product\ReviewController;


Route::get('/products/{product:slug}/reviews', [ReviewController::class, 'index']);

// Routes تحتاج تسجيل دخول

Route::middleware(['auth:api'])
    ->prefix('v1/customer')->group(function () {
    // إضافة مراجعة
    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']);

    // تحديث مراجعة
    Route::put('/products/{product:slug}/reviews/{review}', [ReviewController::class, 'update']);

    // حذف مراجعة
    Route::delete('/products/{product:slug}/reviews/{review}', [ReviewController::class, 'destroy']);

    // عرض مراجعة المستخدم لمنتج معين
    Route::get('/products/{product:slug}/my-review', [ReviewController::class, 'myReviews']);

    // عرض كل مراجعات المستخدم
    // Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
});

Route::middleware(['auth:api'])
    ->prefix('v1/customer')
    ->group(function () {
            Route::apiResource('products', ProductController::class)
            ->only(['index', 'show'])
            ->parameter('products', 'slug');

            Route::apiResource('daily-deals', DailyProductController::class)
            ->only(['index', 'show'])
            ->parameters(['daily-deals' => 'id']);

            Route::apiResource('flash-sales', FlashSaleController::class)
            ->only(['index', 'show']);



        // Route::apiResource('daily-deals', DailyProductController::class)->parameters(['daily-deals' => 'id']);
            // Route::apiResource('flash-sales', FlashSaleController::class);
        // Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']);
        // Route::get('/reviews', [ReviewController::class, 'myReviews']);
        Route::get('reviews', [ReviewController::class, 'index']);
        // الموافقة على مراجعة معينة

    });


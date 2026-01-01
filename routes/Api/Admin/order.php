<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\Admin\Order\OrderController;


Route::middleware(['auth:api', AdminMiddleware::class])
    ->prefix('v1/admin/orders')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('{orderNumber}', [OrderController::class, 'show']);
        Route::delete('{orderNumber}', [OrderController::class, 'destroy']);
        Route::patch('{orderNumber}/status', [OrderController::class, 'updateStatus']);
        Route::patch('{orderNumber}/payment-status', [OrderController::class, 'updatePaymentStatus']);
        Route::patch('{orderNumber}/delivery-status', [OrderController::class, 'updateDeliveryStatus']);
    });






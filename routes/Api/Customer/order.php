<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\customer\Order\OrderController;
Route::middleware(['auth:api'])
    ->prefix('v1/orders')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('{orderNumber}', [OrderController::class, 'show']);
        Route::post('{orderNumber}/cancel', [OrderController::class, 'cancel']);
    });

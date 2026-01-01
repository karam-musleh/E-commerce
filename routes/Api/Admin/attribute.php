<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\admin\Attribute\AttributeController;
use App\Http\Controllers\Api\admin\Attribute\AttributeValueController;

Route::middleware('auth:api',)->prefix('v1/admin')->group(function () {
Route::apiResource('attributes', AttributeController::class)->parameter('attributes', 'slug');

    Route::prefix('attributes/{slug}/values')->group(function () {
        Route::get('', [AttributeValueController::class, 'index']);
        Route::post('', [AttributeValueController::class, 'store']);
        Route::put('{valueSlug}', [AttributeValueController::class, 'update']);
        Route::delete('{valueSlug}', [AttributeValueController::class, 'destroy']);
    });

});

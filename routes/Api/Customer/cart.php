<?php




use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\Carts\CartController;

Route::prefix('v1/cart')->group(function () {

    Route::get('/show', [CartController::class, 'show']);

    Route::post('/add', [CartController::class, 'add']);

    Route::put('/update', [CartController::class, 'update']);

    Route::delete('/remove', [CartController::class, 'remove']);

});

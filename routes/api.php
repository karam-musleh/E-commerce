<?php


use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\Payment\PaymentService;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\RegisterController;


$directory = new RecursiveDirectoryIterator(__DIR__ . '/api');
$iterator  = new RecursiveIteratorIterator($directory);
$files     = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    require $file[0];
}
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// payment route

Route::post('/orders/{order}/pay', [PaymentController::class, 'pay']);

Route::get('/fake-pay/{payment}', function (Payment $payment) {
    return redirect()->route('fake.webhook', $payment);
})->name('fake.pay');

// Route::post('/fake-webhook/{payment}', function (
//     Payment $payment,
//     PaymentService $service
// ) {
//     $service->markAsPaid($payment, [
//         'fake' => true,
//     ]);

//     return response()->json(['ok' => true]);
// })->name('fake.webhook');

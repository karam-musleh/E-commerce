<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
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

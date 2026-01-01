<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            $modelName = class_basename($exception->getModel());

            $message = match ($modelName) {
                'Product' => 'Product not found',
                'Review' => 'Review not found',
                default => 'Resource not found',
            };

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        }

        return parent::render($request, $exception);
    }
}

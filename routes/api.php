<?php

use App\Http\Controllers\FakePaymentWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\SanctumController;
use Illuminate\Support\Facades\Route;

// Auth App
Route::post('/register', [SanctumController::class, 'register']);
Route::post('/login', [SanctumController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [SanctumController::class, 'me']);
    Route::post('/refresh', [SanctumController::class, 'refresh']);
    Route::post('/logout', [SanctumController::class, 'logout']);
});

// Payment App
// POST /api/webhooks/fake-payment
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('orders', OrderController::class)->only(['store', 'show']);
    Route::post('orders/{order}/payments', [OrderPaymentController::class, 'store']);
});
if (app()->environment(['local', 'testing'])) {
    Route::post(
        '/webhooks/fake-payment',
        FakePaymentWebhookController::class
    );
}
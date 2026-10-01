<?php

use App\Http\Controllers\OrderController;
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
// POST /api/orders
// GET  /api/orders/{order}
// POST /api/orders/{order}/pay
// POST /api/webhooks/fake-payment

Route::apiResource('orders', OrderController::class)->only(['store', 'show']);



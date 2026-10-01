<?php

<<<<<<< HEAD
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
=======
use App\Http\Controllers\SanctumController;
>>>>>>> feat/auth-sanctum
use Illuminate\Support\Facades\Route;



<<<<<<< HEAD
// POST /api/orders
// GET  /api/orders/{order}
// POST /api/orders/{order}/pay
// POST /api/webhooks/fake-payment


Route::apiResource('orders', OrderController::class)->only(['store', 'show']);



=======



Route::post('/register', [SanctumController::class, 'register']);
Route::post('/login', [SanctumController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [SanctumController::class, 'me']);
    Route::post('/refresh', [SanctumController::class, 'refresh']);
    Route::post('/logout', [SanctumController::class, 'logout']);
});
>>>>>>> feat/auth-sanctum

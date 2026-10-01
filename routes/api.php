<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// POST /api/orders
// GET  /api/orders/{order}
// POST /api/orders/{order}/pay
// POST /api/webhooks/fake-payment


Route::apiResource('orders', OrderController::class)->only(['store', 'show']);




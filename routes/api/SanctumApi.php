<?php



use App\Http\Controllers\SanctumController;
use Illuminate\Support\Facades\Route;






Route::post('/register', [SanctumController::class, 'register']);
Route::post('/login', [SanctumController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [SanctumController::class, 'me']);
    Route::post('/refresh', [SanctumController::class, 'refresh']);
    Route::post('/logout', [SanctumController::class, 'logout']);
});
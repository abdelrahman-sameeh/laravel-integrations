<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\FriendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.store');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/{user}/requests', [FriendController::class, 'store'])->name('friends.store');
    Route::patch('/friend-requests/{friendship}/accept', [FriendController::class, 'accept'])->name('friend-requests.accept');
    Route::delete('/friend-requests/{friendship}', [FriendController::class, 'destroy'])->name('friend-requests.destroy');

    Route::get('/conversation/{user}', [ConversationController::class, 'show'])->name('conversation.show');
    Route::post('/conversation/{user}/messages', [MessageController::class, 'store'])->name('message.store');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::redirect('/', '/chat');

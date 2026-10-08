<?php

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
    Route::view('/chat', 'chat')->name('chat');
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/{user}/requests', [FriendController::class, 'store'])->name('friends.store');
    Route::patch('/friend-requests/{friendship}/accept', [FriendController::class, 'accept'])->name('friend-requests.accept');
    Route::delete('/friend-requests/{friendship}', [FriendController::class, 'destroy'])->name('friend-requests.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::redirect('/', '/chat');

// GET     /api/conversations	يجيب كل الـconversations بتاعة الـuser
// POST	/api/conversations	يبدأ conversation جديدة مع user تاني
// GET	/api/conversations/{conversation}	يجيب معلومات الـconversation
// GET	/api/conversations/{conversation}/messages	يجيب الرسائل، ويفضل Pagination
// POST	/api/conversations/{conversation}/messages  يبعت Message جديدة

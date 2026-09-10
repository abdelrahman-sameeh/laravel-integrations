<?php

use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;


Route::get('auth/google', [GoogleController::class, 'redirect']);
Route::get('auth/google/callback', [GoogleController::class, 'callback']);

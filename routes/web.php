<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'edit', 'update']);
Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

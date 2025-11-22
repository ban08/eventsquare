<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EventController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

// Public event browsing
Route::controller(EventController::class)->group(function () {
    Route::get('/', 'home')->name('home'); // R200

    Route::get('/events', 'index')->name('events.index'); // R201
    Route::get('/events/{event}', 'show')->name('events.show'); // R203

    Route::get('/api/events', 'searchApi')->name('api.events.search'); // R206
});


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
});

Route::controller(LogoutController::class)->group(function () {
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminUserController;
 
// Public event browsing
Route::controller(EventController::class)->group(function () {
    Route::get('/', 'home')->name('home'); // R200

    Route::get('/events', 'index')->name('events.index'); // R201
    Route::get('/events/{event}', 'show')->name('events.show'); // R203

    Route::get('/api/events', 'searchApi')->name('api.events.search'); // R206
});

// Authentication routes (AuthController version from main)
Route::controller(AuthController::class)->group(function () {
    // Show login form
    Route::get('/login', 'showLogin')->name('login');

    // Handle login form submission
    Route::post('/login', 'login');

    // Log the user out
    Route::post('/logout', 'logout')->name('logout');

    // Show registration form
    Route::get('/register', 'showRegister')->name('register');

    // Handle registration form submission
    Route::post('/register', 'register');
});

Route::middleware(['auth', 'can:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

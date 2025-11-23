<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
 
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

// Plain events page for logged-in users
Route::get('/events', function () {
    return view('events.index');
})->name('events.index')->middleware('auth');  // User must be logged in

// Event management routes 
Route::controller(EventController::class)->group(function () {
    Route::get('/', 'home')->name('home'); // R200

    Route::get('/events', 'index')->name('events.index'); // R201

    // Show the "create event" form.
    // Only logged-in users can access this page.    
    Route::get('/events/create', 'create')->name('events.create')->middleware('auth');

    // Handle the "create event" form when submitted.
    // This saves the event in the database.    
    Route::post('/events', 'store')->name('events.store')->middleware('auth');

    // Show a single event by its ID (e.g. /events/5).
    Route::get('/events/{event}', 'show')->name('events.show');

    Route::get('/api/events', 'searchApi')->name('api.events.search'); // R206
});

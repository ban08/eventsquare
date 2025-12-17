<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\RecoveryController;
 
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
    Route::post('/register', 'register');
});

// Password recovery routes (security question flow)
Route::get('/password/forgot', [RecoveryController::class, 'showRequest'])->name('password.request');
Route::post('/password/forgot', [RecoveryController::class, 'showQuestion'])->name('password.question');
Route::post('/password/reset/security', [RecoveryController::class, 'resetWithAnswer'])->name('password.reset.security');


// Home - redirect to events browse page (main landing)
Route::redirect('/', '/events');


// Admin user management (AD07, if required by ER/EBD)
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
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy'); // AD06
    });

// Event management routes 
Route::controller(EventController::class)->group(function () {
    Route::get('/events', 'index')->name('events.index'); // R201 - Main landing page

    // Show the "create event" form.
    // Only logged-in users can access this page.    
    Route::get('/events/create', 'create')->name('events.create')->middleware('auth');

    // Handle the "create event" form when submitted.
    // This saves the event in the database.    
    Route::post('/events', 'store')->name('events.store')->middleware('auth');

    // Show a single event by its ID (e.g. /events/5).
    Route::get('/events/{event}', 'show')->name('events.show');

    Route::get('/api/events', 'searchApi')->name('api.events.search'); // R206

    // Page to list only MY events (events where I am the organizer)
    Route::get('/my-events', 'mine')->name('events.mine')->middleware('auth');    

    // Show edit form for an existing event (OR02)
    Route::get('/events/{event}/edit', 'edit')->name('events.edit')->middleware('auth');

    // Handle edit form submission (update existing event) (OR02)
    Route::put('/events/{event}', 'update')->name('events.update')->middleware('auth');

    // Delete an event (OR08) - only for authenticated users
    Route::delete('/events/{event}', 'destroy')->name('events.destroy')->middleware('auth');    

    // Cancel an event - only for authenticated users
    Route::post('/events/{event}/cancel', 'cancel')->name('events.cancel')->middleware('auth');
    
    // Apply to event (RU09)
    Route::post('/events/{event}/apply', 'apply')->name('events.apply')->middleware('auth');

});

// Invitations (OR03 invite, RU10 respond)
Route::middleware('auth')->controller(InvitationController::class)->group(function () {
    Route::post('/events/{event}/invite', 'invite')->name('invitations.invite'); // OR03
    Route::post('/invitations/{invitation}/accept', 'accept')->name('invitations.accept'); // RU10
    Route::post('/invitations/{invitation}/decline', 'decline')->name('invitations.decline'); // RU10
});

// Notifications (includes RU10 invitations list)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('markRead');
    Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('markAllRead');
});

// Profile
Route::middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'show')->name('profile.show');       // RU01
    Route::get('/profile/edit', 'edit')->name('profile.edit');  // RU02
    Route::put('/profile/update', 'update')->name('profile.update');
});
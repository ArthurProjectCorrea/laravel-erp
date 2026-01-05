<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('private/home', [
        'auth' => [
            'user' => request()->user(),
        ],
    ]);
})->middleware(['auth.token'])->name('home');

// Public authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('public/auth/login');
    })->name('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';

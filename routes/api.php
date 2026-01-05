<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
});

// Protected routes
Route::middleware(['auth:sanctum', 'token.not.revoked', 'auth.active'])->group(function () {
    Route::post('/sign-out', [AuthController::class, 'logout'])->name('sign-out');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/me', [AuthController::class, 'me'])->name('me.post');
});

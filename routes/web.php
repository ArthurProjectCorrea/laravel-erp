<?php

use App\Http\Controllers\PasswordResetCodeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home route - requer autenticação via session (Fortify)
Route::middleware(['auth', 'auth.active'])->group(function () {
    Route::get('/', function () {
        $user = request()->user();

        \Illuminate\Support\Facades\Log::info('[WEB-HOME] Acessando rota home', [
            'user_authenticated' => (bool) $user,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
        ]);

        return Inertia::render('private/home', [
            'auth' => [
                'user' => $user,
            ],
        ]);
    })->name('home');

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Logout route - usa Fortify para logout (invalida sessão)
    // Fortify já registra POST /logout automaticamente via FortifyServiceProvider
});

// Public authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('public/auth/login');
    })->name('login');

    // Password Reset with Code Flow
    Route::get('/forgot-password', [PasswordResetCodeController::class, 'showForgotPasswordForm'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetCodeController::class, 'sendCode'])
        ->name('password.email');

    Route::get('/verify-code', [PasswordResetCodeController::class, 'showVerifyCodeForm'])
        ->name('password.verify-code');

    Route::post('/verify-code', [PasswordResetCodeController::class, 'verifyCode'])
        ->name('password.verify-code.submit');

    Route::get('/reset-password', [PasswordResetCodeController::class, 'showResetPasswordForm'])
        ->name('password.reset-form');

    Route::post('/reset-password', [PasswordResetCodeController::class, 'resetPassword'])
        ->name('password.update');

    Route::post('/resend-code', [PasswordResetCodeController::class, 'resendCode'])
        ->name('password.resend-code');
});

require __DIR__.'/settings.php';

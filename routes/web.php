<?php

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
});

require __DIR__.'/settings.php';

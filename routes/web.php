<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home route - requer autenticação via token Bearer
Route::middleware(['auth.from-token', 'token.not.revoked', 'auth.active'])->group(function () {
    Route::get('/', function () {
        $user = request()->user();
        $authHeader = request()->header('Authorization');
        $token = null;

        // Extrair o token do header Authorization: Bearer xxx
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        \Illuminate\Support\Facades\Log::info('[WEB-HOME] Acessando rota home', [
            'user_authenticated' => (bool) $user,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'token_received' => $token ? 'YES' : 'NO',
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

    // Logout route
    Route::post('/logout', function () {
        $user = request()->user();

        \Illuminate\Support\Facades\Log::info('[WEB-LOGOUT] ===== INICIANDO LOGOUT PELA ROTA WEB =====', [
            'user_id' => $user?->id,
            'email' => $user?->email,
        ]);

        if ($user) {
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
                \Illuminate\Support\Facades\Log::info('[WEB-LOGOUT] Token revogado com sucesso', [
                    'user_id' => $user->id,
                ]);
            }
        }

        return redirect('/login');
    })->name('logout');
});

// Public authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('public/auth/login');
    })->name('login');
});

require __DIR__.'/settings.php';

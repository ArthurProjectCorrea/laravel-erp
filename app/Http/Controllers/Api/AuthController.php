<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request): JsonResponse
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Find user by email
        $user = User::where('email', $email)->first();

        // Check if user exists, is active, and password is correct
        if (! $user || ! Hash::check($password, $user->password) || ! $user->is_active) {
            Log::warning('Failed login attempt', [
                'email' => $email,
                'reason' => ! $user ? 'user_not_found' : (! $user->is_active ? 'user_inactive' : 'invalid_password'),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas são inválidas.'],
            ]);
        }

        // Create new Sanctum token
        $token = $user->createToken('web')->plainTextToken;

        Log::info('User login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        Log::info('User logout successful', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ], 200);
    }

    /**
     * Get the authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}

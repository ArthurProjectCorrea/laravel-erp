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
        Log::info('[API-LOGIN] ===== INICIANDO LOGIN =====', [
            'email_provided' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        // Validate input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        Log::info('[API-LOGIN] Validação passou');

        $email = $validated['email'];
        $password = $validated['password'];

        // Find user by email
        $user = User::where('email', $email)->first();
        Log::info('[API-LOGIN] Procurando usuário por email', [
            'email' => $email,
            'user_found' => (bool) $user,
            'user_id' => $user?->id,
            'user_is_active' => $user?->is_active,
        ]);

        // Check if user exists, is active, and password is correct
        if (! $user) {
            Log::warning('[API-LOGIN] Falha: Usuário não encontrado', ['email' => $email]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            Log::warning('[API-LOGIN] Falha: Senha incorreta', [
                'email' => $email,
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            Log::warning('[API-LOGIN] Falha: Usuário inativo', [
                'email' => $email,
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Log::info('[API-LOGIN] Credenciais validadas com sucesso');

        // Create new Sanctum token
        Log::info('[API-LOGIN] Criando token Sanctum');
        $token = $user->createToken('web')->plainTextToken;
        Log::info('[API-LOGIN] Token criado', [
            'token_preview' => substr($token, 0, 20).'...',
            'user_id' => $user->id,
        ]);

        Log::info('[API-LOGIN] Login bem-sucedido', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token_preview' => substr($token, 0, 20).'...',
            'ip' => $request->ip(),
        ]);

        $response = [
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ];

        Log::info('[API-LOGIN] Enviando resposta', [
            'has_token' => (bool) ($response['token'] ?? null),
        ]);

        return response()->json($response, 200);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('[API-LOGOUT] ===== INICIANDO LOGOUT =====', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => $request->ip(),
        ]);

        if (! $user) {
            Log::warning('[API-LOGOUT] Tentativa de logout sem usuário autenticado', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'No authenticated user',
            ], 401);
        }

        $token = $user->currentAccessToken();
        Log::info('[API-LOGOUT] Token atual encontrado', [
            'user_id' => $user->id,
            'token_preview' => $token ? substr($token->token, 0, 20) : 'null',
        ]);

        if ($token) {
            $token->delete();
            Log::info('[API-LOGOUT] Token revogado com sucesso', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } else {
            Log::warning('[API-LOGOUT] Nenhum token para revogar', [
                'user_id' => $user->id,
            ]);
        }

        Log::info('[API-LOGOUT] Logout bem-sucedido', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Logout successful',
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

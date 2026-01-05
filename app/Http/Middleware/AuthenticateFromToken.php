<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log all headers received on reload
        $authHeader = $request->header('Authorization');
        \Log::info('[AUTH-MIDDLEWARE-DEBUG] Headers recebidos', [
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'has_auth_header' => $authHeader ? 'YES' : 'NO',
            'auth_header_preview' => $authHeader ? substr($authHeader, 0, 30) : 'NONE',
            'all_headers' => array_keys($request->headers->all()),
        ]);

        // Try to authenticate using Sanctum token from Authorization header
        $token = $request->bearerToken();

        if ($token) {
            \Log::info('[AUTH-MIDDLEWARE] Bearer token encontrado', [
                'token_preview' => substr($token, 0, 20),
            ]);

            // Find the token
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if ($personalAccessToken && ! $personalAccessToken->revoked) {
                \Log::info('[AUTH-MIDDLEWARE] Token válido encontrado', [
                    'user_id' => $personalAccessToken->tokenable_id,
                ]);

                // Authenticate the user
                $request->setUserResolver(function () use ($personalAccessToken) {
                    return $personalAccessToken->tokenable;
                });
            } else {
                \Log::warning('[AUTH-MIDDLEWARE] Token inválido ou revogado');
            }
        } else {
            \Log::info('[AUTH-MIDDLEWARE] Nenhum bearer token encontrado');
        }

        return $next($request);
    }
}

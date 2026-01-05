<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenNotRevoked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check if user is authenticated via token
        if ($request->user() !== null) {
            // Get the current access token from the request
            $token = $request->user()->currentAccessToken();

            // Check if token exists and is revoked
            if ($token && $token->revoked) {
                return response()->json([
                    'message' => 'Token has been revoked.',
                ], 401);
            }
        }

        return $next($request);
    }
}

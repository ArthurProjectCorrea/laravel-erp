<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetAuthHeaderMeta
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Se há um user autenticado, passar o token via meta tag
        if ($request->user()) {
            $authHeader = $request->header('Authorization');
            Log::info('[SET-AUTH-META] Middleware executado', [
                'has_user' => true,
                'auth_header' => $authHeader ? 'YES' : 'NO',
                'content_type' => $response->headers->get('content-type'),
            ]);

            if ($authHeader) {
                Log::info('[SET-AUTH-META] Tentando injetar meta tag', [
                    'auth_header_preview' => substr($authHeader, 0, 30),
                ]);

                // Injetar a meta tag de auth-header na resposta HTML
                if ($response->headers->get('content-type') && str_contains($response->headers->get('content-type'), 'text/html')) {
                    $content = $response->getContent();
                    // Adicionar meta tag após o CSRF token
                    $metaTag = '<meta name="auth-header" content="'.htmlspecialchars($authHeader).'">'."\n";
                    $newContent = str_replace('<meta name="csrf-token"', $metaTag.'<meta name="csrf-token"', $content);

                    if ($newContent !== $content) {
                        Log::info('[SET-AUTH-META] Meta tag injetada com sucesso');
                        $response->setContent($newContent);
                    } else {
                        Log::warning('[SET-AUTH-META] Falha ao injetar meta tag - padrão não encontrado');
                    }
                }
            }
        }

        return $response;
    }
}

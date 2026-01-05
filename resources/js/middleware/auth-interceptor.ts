import { getAuthToken } from '@/utils/auth';

/**
 * Configura headers de autorização para todas as requisições fetch
 * NOTA: Este é um fallback em caso de o app.tsx não estar funcionando
 */
export function setupAuthInterceptor(): void {
    // Skip setup se já tiver sido feito em app.tsx
    if (
        typeof window !== 'undefined' &&
        (window as Record<string, unknown>).__authInterceptorSetup
    ) {
        console.log(
            '[AUTH-INTERCEPTOR] Interceptor já foi configurado em app.tsx',
        );
        return;
    }

    const originalFetch = window.fetch;
    let requestCount = 0;

    window.fetch = function (input: RequestInfo | URL, init?: RequestInit) {
        requestCount++;
        const token = getAuthToken();
        const url = typeof input === 'string' ? input : input.toString();

        // Criar uma cópia do init se existir, ou criar novo objeto
        const newInit: RequestInit = init ? { ...init } : {};

        // Adicionar header de autorização se houver token
        if (token) {
            if (requestCount <= 5) {
                // Log apenas das primeiras 5 requisições para evitar spam
                console.log(
                    '[AUTH-INTERCEPTOR] Adicionando token ao header - URL:',
                    url.substring(0, 50),
                );
            }
            // Fazer merge seguro dos headers, preservando headers existentes
            const existingHeaders = newInit.headers || {};
            newInit.headers = {
                ...existingHeaders,
                Authorization: `Bearer ${token}`,
            } as HeadersInit;
        }

        return originalFetch(input, newInit);
    } as typeof fetch;

    (window as Record<string, unknown>).__authInterceptorSetup = true;
    console.log('[AUTH-INTERCEPTOR] Interceptor configurado');
}

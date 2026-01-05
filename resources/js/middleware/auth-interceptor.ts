import { getAuthToken } from '@/utils/auth';

/**
 * Configura headers de autorização para todas as requisições
 */
export function setupAuthInterceptor(): void {
    const originalFetch = window.fetch;

    window.fetch = function (input: RequestInfo | URL, init?: RequestInit) {
        const token = getAuthToken();

        // Criar uma cópia do init se existir, ou criar novo objeto
        const newInit: RequestInit = init ? { ...init } : {};

        // Adicionar header de autorização se houver token
        if (token) {
            newInit.headers = {
                ...newInit.headers,
                Authorization: `Bearer ${token}`,
            } as HeadersInit;
        }

        return originalFetch(input, newInit);
    } as typeof fetch;
}

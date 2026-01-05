import { router } from '@inertiajs/react';

/**
 * Armazena o token de autenticação no localStorage
 */
export function setAuthToken(token: string): void {
    console.log(
        '[AUTH] setAuthToken - Salvando token:',
        token?.substring(0, 20) + '...',
    );
    localStorage.setItem('auth_token', token);

    const savedToken = localStorage.getItem('auth_token');
    console.log('[AUTH] setAuthToken - Token salvo com sucesso?', !!savedToken);
    console.log(
        '[AUTH] setAuthToken - Token armazenado:',
        savedToken?.substring(0, 20) + '...',
    );
}

/**
 * Recupera o token de autenticação do localStorage
 */
export function getAuthToken(): string | null {
    const token = localStorage.getItem('auth_token');
    if (token) {
        console.log(
            '[AUTH] getAuthToken - Token encontrado no localStorage:',
            token.substring(0, 20) + '...',
        );
    }
    return token;
}

/**
 * Remove o token de autenticação do localStorage
 */
export function removeAuthToken(): void {
    console.log('[AUTH] removeAuthToken - Removendo token do localStorage');
    localStorage.removeItem('auth_token');

    const tokenAfterRemove = localStorage.getItem('auth_token');
    console.log(
        '[AUTH] removeAuthToken - Token removido com sucesso?',
        !tokenAfterRemove,
    );
}

/**
 * Define o header de autorização nas requisições
 */
export function setupAuthHeader(token: string): void {
    // Se estiver usando fetch, adicionar token no header
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
        const [resource, config = {}] = args;
        const newConfig = {
            ...config,
            headers: {
                ...((config as RequestInit).headers || {}),
                Authorization: `Bearer ${token}`,
            },
        };
        return originalFetch(resource, newConfig);
    };
}

/**
 * Redireciona para a página de login
 */
export function redirectToLogin(): void {
    console.log('[AUTH] redirectToLogin - Redirecionando para login');
    window.location.href = '/login';
}

/**
 * Redireciona para a página inicial após login
 */
export function redirectToDashboard(): void {
    const token = getAuthToken();
    console.log('[AUTH] redirectToDashboard - Iniciando redirecionamento');
    console.log(
        '[AUTH] redirectToDashboard - Token antes de redirecionar:',
        token ? 'SIM' : 'NÃO',
    );
    console.log(
        '[AUTH] redirectToDashboard - Redirecionando para: / usando Inertia',
    );
    // Usar Inertia para redirecionar mantendo o contexto
    router.visit('/', {
        method: 'get',
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });
}

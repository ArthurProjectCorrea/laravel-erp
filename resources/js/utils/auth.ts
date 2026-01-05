import { router } from '@inertiajs/react';

/**
 * Armazena o token de autenticação no localStorage
 */
export function setAuthToken(token: string): void {
    localStorage.setItem('auth_token', token);
}

/**
 * Recupera o token de autenticação do localStorage
 */
export function getAuthToken(): string | null {
    return localStorage.getItem('auth_token');
}

/**
 * Remove o token de autenticação do localStorage
 */
export function removeAuthToken(): void {
    localStorage.removeItem('auth_token');
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
    router.visit('/login');
}

/**
 * Redireciona para a página inicial após login
 */
export function redirectToDashboard(): void {
    // Usar window.location para redirecionar completamente
    window.location.href = '/';
}

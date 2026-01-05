import { useAuth } from '@/hooks/use-auth';
import { redirectToLogin, removeAuthToken } from '@/utils/auth';
import { useEffect } from 'react';

/**
 * Hook para redirecionar usuários não autenticados
 * Útil para proteger rotas no frontend
 */
export function useRequireAuth() {
    const { isAuthenticated, isActive } = useAuth();

    useEffect(() => {
        if (!isAuthenticated) {
            removeAuthToken();
            redirectToLogin();
        }

        if (!isActive) {
            removeAuthToken();
            redirectToLogin();
        }
    }, [isAuthenticated, isActive]);

    return {
        isAuthenticated,
        isActive,
        hasAccess: isAuthenticated && isActive,
    };
}

/**
 * Hook para redirecionar usuários autenticados
 * Útil para redirecionar da página de login se já estiver autenticado
 */
export function useRequireGuest() {
    const { isAuthenticated } = useAuth();

    useEffect(() => {
        if (isAuthenticated) {
            redirectToDashboard();
        }
    }, [isAuthenticated]);

    return {
        isAuthenticated,
    };
}

function redirectToDashboard() {
    import('@inertiajs/react').then(({ router }) => {
        router.visit('/');
    });
}

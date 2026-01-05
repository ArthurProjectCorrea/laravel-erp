import { Button } from '@/components/ui/button';
import { getAuthToken, redirectToLogin, removeAuthToken } from '@/utils/auth';
import { ReactNode, useState } from 'react';
import { toast } from 'sonner';

interface LogoutButtonProps {
    variant?:
        | 'default'
        | 'destructive'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link';
    size?: 'default' | 'sm' | 'lg';
    children?: ReactNode;
    className?: string;
}

export function LogoutButton({
    variant = 'outline',
    size = 'default',
    children = 'Logout',
    className,
}: LogoutButtonProps) {
    const [isLoading, setIsLoading] = useState(false);

    const handleLogout = async () => {
        console.log('[LOGOUT] ===== INICIANDO LOGOUT =====');
        const token = getAuthToken();
        console.log('[LOGOUT] Token Bearer:', token ? 'SIM' : 'NÃO');

        setIsLoading(true);

        try {
            // Fazer POST para /api/logout usando Bearer token (não precisa de CSRF)
            const response = await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
                body: JSON.stringify({}),
            });

            console.log('[LOGOUT] Resposta do servidor:', response.status);

            if (!response.ok) {
                console.error(
                    '[LOGOUT] Erro ao fazer logout:',
                    response.status,
                );
                toast.error('Erro ao fazer logout', {
                    description: 'Tente novamente',
                });
                return;
            }

            console.log(
                '[LOGOUT] Logout sucesso, removendo token do localStorage',
            );
            removeAuthToken();
            console.log('[LOGOUT] Token removido do localStorage');

            toast.success('Logout realizado com sucesso!', {
                description: 'Redirecionando...',
            });

            // Redirecionar para login
            redirectToLogin();
        } catch (error) {
            console.error('[LOGOUT] Erro durante logout:', error);
            toast.error('Erro ao fazer logout', {
                description: 'Tente novamente mais tarde',
            });
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <Button
            variant={variant}
            size={size}
            onClick={handleLogout}
            disabled={isLoading}
            className={className}
        >
            {isLoading ? 'Saindo...' : children}
        </Button>
    );
}

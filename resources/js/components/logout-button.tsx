import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { ReactNode, useState } from 'react';

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

    const handleLogout = () => {
        setIsLoading(true);

        // Usar Inertia para fazer logout via Fortify (session-based)
        // POST /logout invalida a sessão e redireciona para login
        router.post(
            '/logout',
            {},
            {
                onFinish: () => {
                    setIsLoading(false);
                },
            },
        );
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

import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { removeAuthToken } from '@/utils/auth';
import { useForm } from '@inertiajs/react';
import { ReactNode } from 'react';

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
    const { post, processing } = useForm({});

    const handleLogout = () => {
        post(logout.url(), {
            onSuccess: () => {
                removeAuthToken();
            },
        });
    };

    return (
        <Button
            variant={variant}
            size={size}
            onClick={handleLogout}
            disabled={processing}
            className={className}
        >
            {processing ? 'Logging out...' : children}
        </Button>
    );
}

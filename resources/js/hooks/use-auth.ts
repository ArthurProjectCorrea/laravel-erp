import { usePage } from '@inertiajs/react';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
}

interface AuthPageProps {
    auth: {
        user: AuthUser | null;
    };
}

export function useAuth() {
    const { props } = usePage<AuthPageProps>();
    const user = props.auth?.user || null;

    return {
        user,
        isAuthenticated: !!user,
        isActive: user?.is_active ?? false,
    };
}

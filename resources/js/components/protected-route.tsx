import { useAuth } from '@/hooks/use-auth';
import { Navigate } from '@inertiajs/react';
import { ReactNode } from 'react';

interface ProtectedRouteProps {
    children: ReactNode;
    requiredRole?: string;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
    const { isAuthenticated, isActive } = useAuth();

    if (!isAuthenticated) {
        return <Navigate href="/login" />;
    }

    if (!isActive) {
        return <Navigate href="/login" />;
    }

    return children;
}

export function PublicRoute({ children }: ProtectedRouteProps) {
    const { isAuthenticated } = useAuth();

    if (isAuthenticated) {
        return <Navigate href="/" />;
    }

    return children;
}

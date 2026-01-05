import { useRequireAuth } from '@/hooks/use-session';
import AppLayout from './app-layout';

interface PrivateLayoutProps {
    children: React.ReactNode;
}

export default function PrivateLayout({ children }: PrivateLayoutProps) {
    const { hasAccess } = useRequireAuth();

    if (!hasAccess) {
        return null;
    }

    return <AppLayout>{children}</AppLayout>;
}

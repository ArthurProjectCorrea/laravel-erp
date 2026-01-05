import { Toaster } from '@/components/ui/sonner';

interface AppLayoutProps {
    children: React.ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    return (
        <>
            {children}
            <Toaster />
        </>
    );
}

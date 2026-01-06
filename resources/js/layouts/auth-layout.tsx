import AppLayout from '@/layouts/app-layout';

interface AuthLayoutProps {
    children: React.ReactNode;
}

export default function AuthLayout({ children }: AuthLayoutProps) {
    return (
        <AppLayout>
            <div className="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div className="w-full max-w-sm">{children}</div>
            </div>
        </AppLayout>
    );
}

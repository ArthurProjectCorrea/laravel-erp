import AppLayout from '@/layouts/app-layout';

interface AuthLayoutProps {
    children: React.ReactNode;
}

export default function AuthLayout({ children }: AuthLayoutProps) {
    return (
        <AppLayout>
            <div className="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10">
                <div className="w-full max-w-sm md:max-w-4xl">{children}</div>
            </div>
        </AppLayout>
    );
}

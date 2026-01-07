import { GalleryVerticalEnd } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { cn } from '@/lib/utils';
import { router, usePage } from '@inertiajs/react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface PageProps {
    flash?: {
        status?: string;
    };
    errors?: {
        email?: string;
    };
}

export function ForgotPassword({
    className,
    ...props
}: React.ComponentProps<'div'>) {
    const { flash, errors: serverErrors } = usePage<PageProps>().props;
    const [email, setEmail] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [localErrors, setLocalErrors] = useState<{ email?: string }>({});

    // Derivar fieldErrors do serverErrors usando useMemo (evita setState em useEffect)
    const fieldErrors = useMemo(
        () => ({ ...serverErrors, ...localErrors }),
        [serverErrors, localErrors],
    );

    useEffect(() => {
        if (flash?.status) {
            toast.success(flash.status);
        }
    }, [flash]);

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setLocalErrors({});

        if (!email) {
            setLocalErrors({ email: 'O e-mail é obrigatório.' });
            return;
        }

        setIsLoading(true);

        router.post(
            '/forgot-password',
            { email },
            {
                onError: (errors) => {
                    if (errors.email) {
                        setLocalErrors({ email: errors.email });
                        toast.error('Erro', {
                            description: errors.email,
                        });
                    }
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            },
        );
    };

    return (
        <AuthLayout>
            <div className={cn('flex flex-col gap-6', className)} {...props}>
                <form onSubmit={handleSubmit}>
                    <FieldGroup>
                        <div className="flex flex-col items-center gap-2 text-center">
                            <a
                                href="/"
                                className="flex flex-col items-center gap-2 font-medium"
                            >
                                <div className="flex size-8 items-center justify-center rounded-md">
                                    <GalleryVerticalEnd className="size-6" />
                                </div>
                                <span className="sr-only">Acme Inc.</span>
                            </a>
                            <h1 className="text-xl font-bold">
                                Esqueceu sua senha?
                            </h1>
                            <FieldDescription>
                                Informe seu e-mail para receber o código de
                                verificação
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel htmlFor="email">E-mail</FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                placeholder="seu@email.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                disabled={isLoading}
                                required
                            />
                            {fieldErrors.email && (
                                <p className="mt-1 text-sm text-red-600">
                                    {fieldErrors.email}
                                </p>
                            )}
                        </Field>
                        <Field>
                            <Button
                                type="submit"
                                className="w-full"
                                disabled={isLoading}
                            >
                                {isLoading ? (
                                    <>
                                        <Spinner className="mr-2" />
                                        Enviando...
                                    </>
                                ) : (
                                    'Enviar código'
                                )}
                            </Button>
                        </Field>
                        <FieldDescription className="text-center">
                            Lembrou sua senha?{' '}
                            <a
                                href="/login"
                                className="underline underline-offset-2"
                            >
                                Entrar
                            </a>
                        </FieldDescription>
                    </FieldGroup>
                </form>
                <FieldDescription className="px-6 text-center">
                    Ao continuar, você concorda com nossos{' '}
                    <a href="#">Termos de Serviço</a> e{' '}
                    <a href="#">Política de Privacidade</a>.
                </FieldDescription>
            </div>
        </AuthLayout>
    );
}

export default ForgotPassword;

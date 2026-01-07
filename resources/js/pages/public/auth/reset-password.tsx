import { GalleryVerticalEnd } from 'lucide-react';

import { InputPassword } from '@/components/input-password';
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
    email?: string;
    code?: string;
    flash?: {
        status?: string;
    };
    errors?: {
        email?: string;
        code?: string;
        password?: string;
    };
}

interface FieldErrors {
    email?: string;
    code?: string;
    password?: string;
    password_confirmation?: string;
}

export function ResetPassword({
    className,
    ...props
}: React.ComponentProps<'div'>) {
    const {
        email: initialEmail,
        code: initialCode,
        flash,
        errors: serverErrors,
    } = usePage<PageProps>().props;

    const [email, setEmail] = useState(initialEmail || '');
    const [code, setCode] = useState(initialCode || '');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [localErrors, setLocalErrors] = useState<FieldErrors>({});

    // Derivar fieldErrors do serverErrors usando useMemo (evita setState em useEffect)
    const fieldErrors = useMemo(
        () => ({ ...serverErrors, ...localErrors }),
        [serverErrors, localErrors],
    );

    useEffect(() => {
        if (flash?.status) {
            toast.success(flash.status);
        }
        if (serverErrors?.code) {
            toast.error('Erro', { description: serverErrors.code });
        }
        if (serverErrors?.password) {
            toast.error('Erro', { description: serverErrors.password });
        }
    }, [flash, serverErrors]);

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setLocalErrors({});

        // Validação local
        const errors: FieldErrors = {};

        if (!email) {
            errors.email = 'O e-mail é obrigatório.';
        }

        if (!code) {
            errors.code = 'O código é obrigatório.';
        }

        if (!password) {
            errors.password = 'A senha é obrigatória.';
        } else if (password.length < 8) {
            errors.password = 'A senha deve ter pelo menos 8 caracteres.';
        }

        if (password !== passwordConfirmation) {
            errors.password_confirmation = 'As senhas não conferem.';
        }

        if (Object.keys(errors).length > 0) {
            setLocalErrors(errors);
            return;
        }

        setIsLoading(true);

        router.post(
            '/reset-password',
            {
                email,
                code,
                password,
                password_confirmation: passwordConfirmation,
            },
            {
                onSuccess: () => {
                    toast.success('Senha redefinida!', {
                        description: 'Faça login com sua nova senha.',
                    });
                },
                onError: (errors) => {
                    setLocalErrors(errors);
                    if (errors.code) {
                        toast.error('Erro', { description: errors.code });
                    }
                    if (errors.password) {
                        toast.error('Erro', { description: errors.password });
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
                                Redefinir senha
                            </h1>
                            <FieldDescription>
                                Defina sua nova senha
                            </FieldDescription>
                        </div>

                        {!initialEmail && (
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
                        )}

                        {initialEmail && (
                            <input type="hidden" name="email" value={email} />
                        )}

                        {!initialCode && (
                            <Field>
                                <FieldLabel htmlFor="code">
                                    Código de verificação
                                </FieldLabel>
                                <Input
                                    id="code"
                                    type="text"
                                    name="code"
                                    placeholder="000000"
                                    maxLength={6}
                                    value={code}
                                    onChange={(e) => setCode(e.target.value)}
                                    disabled={isLoading}
                                    required
                                />
                                {fieldErrors.code && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {fieldErrors.code}
                                    </p>
                                )}
                            </Field>
                        )}

                        {initialCode && (
                            <input type="hidden" name="code" value={code} />
                        )}

                        <Field>
                            <FieldLabel htmlFor="password">
                                Nova senha
                            </FieldLabel>
                            <InputPassword
                                id="password"
                                name="password"
                                placeholder="Sua nova senha"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                disabled={isLoading}
                                required
                            />
                            {fieldErrors.password && (
                                <p className="mt-1 text-sm text-red-600">
                                    {fieldErrors.password}
                                </p>
                            )}
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="password_confirmation">
                                Confirme a senha
                            </FieldLabel>
                            <InputPassword
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Confirme sua nova senha"
                                value={passwordConfirmation}
                                onChange={(e) =>
                                    setPasswordConfirmation(e.target.value)
                                }
                                disabled={isLoading}
                                required
                            />
                            {fieldErrors.password_confirmation && (
                                <p className="mt-1 text-sm text-red-600">
                                    {fieldErrors.password_confirmation}
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
                                        Redefinindo...
                                    </>
                                ) : (
                                    'Redefinir senha'
                                )}
                            </Button>
                        </Field>

                        <FieldDescription className="text-center">
                            <a
                                href="/login"
                                className="underline underline-offset-2"
                            >
                                Voltar ao login
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

export default ResetPassword;

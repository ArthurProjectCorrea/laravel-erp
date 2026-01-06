import { InputPassword } from '@/components/input-password';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { router } from '@inertiajs/react';
import { GalleryVerticalEnd } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { toast } from 'sonner';

interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginErrors {
    email?: string;
    password?: string;
}

export function Login({ className }: React.ComponentProps<'div'>) {
    const [formData, setFormData] = useState<LoginFormData>({
        email: '',
        password: '',
        remember: false,
    });
    const [fieldErrors, setFieldErrors] = useState<LoginErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        console.log('[LOGIN] ===== INICIANDO LOGIN =====');
        console.log('[LOGIN] Email:', formData.email);

        // Clear previous field errors
        setFieldErrors({});

        // Validation
        const newErrors: LoginErrors = {};
        if (!formData.email) newErrors.email = 'Email is required';
        if (!formData.password) newErrors.password = 'Password is required';

        if (Object.keys(newErrors).length > 0) {
            console.log('[LOGIN] Validação falhou:', newErrors);
            setFieldErrors(newErrors);
            return;
        }

        setIsLoading(true);
        console.log(
            '[LOGIN] Enviando requisição para /login (Fortify - session based)',
        );

        // Usar Inertia router para fazer login via Fortify (session-based)
        // Isso cria uma sessão e cookie, que persiste após reload
        router.post('/login', formData, {
            onSuccess: () => {
                console.log('[LOGIN] Login bem-sucedido via Fortify!');
                toast.success('Login realizado com sucesso!', {
                    description: 'Redirecionando para o painel...',
                });
            },
            onError: (errors) => {
                console.log('[LOGIN] Falha no login - Erros:', errors);
                if (errors.email) {
                    setFieldErrors({ email: errors.email });
                }
                if (errors.password) {
                    setFieldErrors((prev) => ({
                        ...prev,
                        password: errors.password,
                    }));
                }
                toast.error('Credenciais inválidas', {
                    description: 'Verifique seu e-mail e senha',
                });
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    return (
        <AuthLayout>
            <div className={cn('flex flex-col gap-6', className)}>
                <form onSubmit={handleSubmit}>
                    <FieldGroup>
                        <div className="flex flex-col items-center gap-2 text-center">
                            <a
                                href="#"
                                className="flex flex-col items-center gap-2 font-medium"
                            >
                                <div className="flex size-8 items-center justify-center rounded-md">
                                    <GalleryVerticalEnd className="size-6" />
                                </div>
                                <span className="sr-only">Acme Inc.</span>
                            </a>
                            <h1 className="text-xl font-bold">Bem-vindo de volta Acme Inc.</h1>

                        </div>
                        <Field>
                            <FieldLabel htmlFor="email">
                                E-mail
                            </FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                placeholder="seu@email.com"
                                value={formData.email}
                                onChange={(e) =>
                                    setFormData({
                                        ...formData,
                                        email: e.target.value,
                                    })
                                }
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
                            <div className="flex items-center">
                                <FieldLabel htmlFor="password">
                                    Senha
                                </FieldLabel>
                                <a
                                    href="/forgot-password"
                                    className="ml-auto text-sm underline-offset-2 hover:underline"
                                >
                                    Esqueceu sua senha?
                                </a>
                            </div>
                            <InputPassword
                                id="password"
                                name="password"
                                placeholder="Sua senha"
                                value={formData.password}
                                onChange={(e) =>
                                    setFormData({
                                        ...formData,
                                        password: e.target.value,
                                    })
                                }
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
                            <Button
                                type="submit"
                                className="w-full"
                                disabled={isLoading}
                            >
                                {isLoading ? (
                                    <>
                                        <Spinner className="mr-2" />
                                        Entrando...
                                    </>
                                ) : (
                                    'Entrar'
                                )}
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
                <div className="relative hidden bg-muted md:block"></div>
                <FieldDescription className="px-6 text-center">
                    Ao continuar, você concorda com nossos{' '}
                    <a href="#">Termos de Serviço</a> e{' '}
                    <a href="#">Política de Privacidade</a>.
                </FieldDescription>
            </div>
        </AuthLayout>
    );
}

export default Login;

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
import { redirectToDashboard, setAuthToken } from '@/utils/auth';
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

export function Login({ className, ...props }: React.ComponentProps<'div'>) {
    const [formData, setFormData] = useState<LoginFormData>({
        email: '',
        password: '',
        remember: false,
    });
    const [fieldErrors, setFieldErrors] = useState<LoginErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        // Clear previous field errors
        setFieldErrors({});

        // Validation
        const newErrors: LoginErrors = {};
        if (!formData.email) newErrors.email = 'Email is required';
        if (!formData.password) newErrors.password = 'Password is required';

        if (Object.keys(newErrors).length > 0) {
            setFieldErrors(newErrors);
            return;
        }

        setIsLoading(true);

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const responseData = await response.json();

            if (!response.ok) {
                if (responseData.errors?.email) {
                    setFieldErrors({ email: responseData.errors.email[0] });
                }
                toast.error('Credenciais inválidas', {
                    description: 'Verifique seu e-mail e senha',
                });
                return;
            }

            // Store token from response
            if (responseData?.token) {
                setAuthToken(responseData.token);
            }

            toast.success('Login realizado com sucesso!', {
                description: 'Redirecionando para o painel...',
            });

            // Redirect to dashboard
            redirectToDashboard();
        } catch (error) {
            console.error('Login error:', error);
            toast.error('Erro ao fazer login', {
                description: 'Tente novamente mais tarde',
            });
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AuthLayout>
            <div className={cn('flex flex-col gap-6', className)} {...props}>
                <Card className="overflow-hidden p-0">
                    <CardContent className="grid p-0 md:grid-cols-2">
                        <form className="p-6 md:p-8" onSubmit={handleSubmit}>
                            <FieldGroup>
                                <div className="flex flex-col items-center gap-2 text-center">
                                    <h1 className="text-2xl font-bold">
                                        Bem-vindo de volta
                                    </h1>
                                    <p className="text-balance text-muted-foreground">
                                        Faça login na sua conta
                                    </p>
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
                                            href="#"
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
                        <div className="relative hidden bg-muted md:block">
                            <img
                                src="/placeholder.svg"
                                alt="Image"
                                className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
                            />
                        </div>
                    </CardContent>
                </Card>
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

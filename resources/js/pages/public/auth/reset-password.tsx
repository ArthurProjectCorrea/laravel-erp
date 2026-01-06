import { GalleryVerticalEnd } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
    FieldSeparator,
} from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { InputPassword } from "@/components/input-password"
import AuthLayout from "@/layouts/auth-layout"

export function ResetPassword({
    className,
    ...props
}: React.ComponentProps<"div">) {
    return (
        <AuthLayout>
            <div className={cn("flex flex-col gap-6", className)} {...props}>
                <form>
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
                            <h1 className="text-xl font-bold">Redefinir senha</h1>
                            <FieldDescription>
                                Insira seu e-mail e defina uma nova senha
                            </FieldDescription>
                        </div>

                        <Field>
                            <FieldLabel htmlFor="email">E-mail</FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                placeholder="m@example.com"
                                required
                            />
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="password">Senha</FieldLabel>
                            <InputPassword id="password" required />
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="password_confirmation">Confirme a senha</FieldLabel>
                            <InputPassword id="password_confirmation" required />
                        </Field>

                        <Field>
                            <Button type="submit">Redefinir senha</Button>
                        </Field>
                    </FieldGroup>
                </form>

                <FieldDescription className="px-6 text-center">
                    Ao continuar, você concorda com nossos <a href="#">Termos de Serviço</a>
                    e <a href="#">Política de Privacidade</a>.
                </FieldDescription>
            </div>
        </AuthLayout>
    )
}

export default ResetPassword

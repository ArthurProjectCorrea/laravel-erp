import { GalleryVerticalEnd } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from "@/components/ui/field"
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSeparator,
    InputOTPSlot,
} from "@/components/ui/input-otp"
import AuthLayout from "@/layouts/auth-layout"

export function TwoFactor({ className, ...props }: React.ComponentProps<"div">) {
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
                            <h1 className="text-xl font-bold">Insira o código de verificação</h1>
                            <FieldDescription>
                                Enviamos um código de 6 dígitos para o seu endereço de e-mail
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel htmlFor="otp" className="sr-only">
                                Código de verificação
                            </FieldLabel>
                            <InputOTP
                                maxLength={6}
                                id="otp"
                                required
                                containerClassName="gap-4"
                            >
                                <InputOTPGroup className="gap-2.5 *:data-[slot=input-otp-slot]:h-16 *:data-[slot=input-otp-slot]:w-12 *:data-[slot=input-otp-slot]:rounded-md *:data-[slot=input-otp-slot]:border *:data-[slot=input-otp-slot]:text-xl">
                                    <InputOTPSlot index={0} />
                                    <InputOTPSlot index={1} />
                                    <InputOTPSlot index={2} />
                                </InputOTPGroup>
                                <InputOTPSeparator />
                                <InputOTPGroup className="gap-2.5 *:data-[slot=input-otp-slot]:h-16 *:data-[slot=input-otp-slot]:w-12 *:data-[slot=input-otp-slot]:rounded-md *:data-[slot=input-otp-slot]:border *:data-[slot=input-otp-slot]:text-xl">
                                    <InputOTPSlot index={3} />
                                    <InputOTPSlot index={4} />
                                    <InputOTPSlot index={5} />
                                </InputOTPGroup>
                            </InputOTP>
                            <FieldDescription className="text-center">
                                Não recebeu o código? <a href="#">Reenviar</a>
                            </FieldDescription>
                        </Field>
                        <Field>
                            <Button type="submit">Verificar</Button>
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

export default TwoFactor;
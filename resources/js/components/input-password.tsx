'use client';

import { EyeIcon, EyeOffIcon } from 'lucide-react';
import * as React from 'react';

import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';

const InputPassword = React.forwardRef<
    React.ElementRef<typeof InputGroupInput>,
    React.ComponentProps<typeof InputGroupInput>
>(({ className, ...props }, ref) => {
    const [showPassword, setShowPassword] = React.useState(false);

    return (
        <InputGroup>
            <InputGroupInput
                type={showPassword ? 'text' : 'password'}
                className={className}
                ref={ref}
                {...props}
            />
            <InputGroupAddon align="inline-end">
                <InputGroupButton
                    type="button"
                    size="icon-xs"
                    variant="ghost"
                    onClick={() => setShowPassword(!showPassword)}
                    aria-label={
                        showPassword ? 'Ocultar senha' : 'Mostrar senha'
                    }
                >
                    {showPassword ? (
                        <EyeOffIcon className="h-4 w-4" />
                    ) : (
                        <EyeIcon className="h-4 w-4" />
                    )}
                </InputGroupButton>
            </InputGroupAddon>
        </InputGroup>
    );
});

InputPassword.displayName = 'InputPassword';

export { InputPassword };

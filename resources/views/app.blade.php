<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline script to setup fetch interceptor BEFORE any requests are made --}}
        <script>
            (function() {
                console.log('[BLADE-FETCH-INTERCEPTOR] Configurando fetch interceptor no reload');
                const originalFetch = window.fetch;
                
                window.fetch = function(input, init) {
                    const token = localStorage.getItem('auth_token');
                    if (token) {
                        console.log('[BLADE-FETCH-INTERCEPTOR] Token encontrado no localStorage, adicionando ao fetch');
                        const newInit = init ? { ...init } : {};
                        const existingHeaders = newInit.headers || {};
                        newInit.headers = {
                            ...existingHeaders,
                            'Authorization': `Bearer ${token}`,
                        };
                        return originalFetch(input, newInit);
                    }
                    return originalFetch(input, init);
                };
                console.log('[BLADE-FETCH-INTERCEPTOR] Fetch interceptor pronto');
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

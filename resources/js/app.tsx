import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { setupAuthInterceptor } from './middleware/auth-interceptor';
import { getAuthToken } from './utils/auth';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Setup auth interceptor ANTES de criar a app Inertia
console.log('[APP] Inicializando setupAuthInterceptor ANTES de Inertia');
setupAuthInterceptor();
console.log('[APP] setupAuthInterceptor iniciado com sucesso');

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <App {...props} />
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

// Add authorization header to all Inertia requests
// This ensures Bearer token is sent on reload and navigation
console.log('[APP] Configurando middleware de headers do Inertia');
const originalFetch = window.fetch;
window.fetch = function (input, init) {
    const token = getAuthToken();
    if (token) {
        console.log(
            '[INERTIA-FETCH] Adicionando Bearer token à requisição Inertia',
        );
        const newInit = init ? { ...init } : {};
        const existingHeaders = newInit.headers || {};
        newInit.headers = {
            ...existingHeaders,
            Authorization: `Bearer ${token}`,
        };
        return originalFetch(input, newInit);
    }
    return originalFetch(input, init);
};
console.log('[APP] Middleware de headers configurado');

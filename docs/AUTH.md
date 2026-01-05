# Autenticação Frontend

Este diretório contém toda a lógica de autenticação do frontend da aplicação.

## Componentes

### `LogoutButton`
Botão de logout reutilizável que limpa o token de autenticação.

```tsx
import { LogoutButton } from "@/components/logout-button"

export function Header() {
    return (
        <div>
            <LogoutButton variant="outline" size="default" />
        </div>
    )
}
```

### `ProtectedRoute` e `PublicRoute`
Componentes para proteger rotas baseado no estado de autenticação.

```tsx
import { ProtectedRoute, PublicRoute } from "@/components/protected-route"
import { Login } from "@/pages/public/auth/login"
import { Dashboard } from "@/pages/private/dashboard"

// Proteger rota privada
<ProtectedRoute>
    <Dashboard />
</ProtectedRoute>

// Redirecionar usuários autenticados da página de login
<PublicRoute>
    <Login />
</PublicRoute>
```

## Hooks

### `useAuth()`
Hook para acessar informações do usuário autenticado.

```tsx
import { useAuth } from "@/hooks/use-auth"

export function UserProfile() {
    const { user, isAuthenticated, isActive } = useAuth()

    if (!isAuthenticated) {
        return <div>Not authenticated</div>
    }

    return (
        <div>
            <p>Welcome, {user?.name}</p>
            <p>Email: {user?.email}</p>
            <p>Active: {isActive ? "Yes" : "No"}</p>
        </div>
    )
}
```

### `useRequireAuth()` e `useRequireGuest()`
Hooks para redirecionar automáticamente baseado no estado de autenticação.

```tsx
import { useRequireAuth } from "@/hooks/use-session"

// Redireciona para login se não estiver autenticado
export function ProtectedPage() {
    const { hasAccess } = useRequireAuth()

    if (!hasAccess) {
        return null // Será redirecionado para login
    }

    return <div>Protected content</div>
}

// Redireciona para dashboard se estiver autenticado
export function LoginPage() {
    const { isAuthenticated } = useRequireGuest()

    if (isAuthenticated) {
        return null // Será redirecionado para dashboard
    }

    return <div>Login form</div>
}
```

## Utilitários

### `auth.ts`
Utilitários para gerenciar tokens e redirecionamentos.

```tsx
import {
    setAuthToken,
    getAuthToken,
    removeAuthToken,
    redirectToLogin,
    redirectToDashboard,
} from "@/utils/auth"

// Armazenar token
setAuthToken("seu-token-aqui")

// Recuperar token
const token = getAuthToken()

// Remover token
removeAuthToken()

// Redirecionar
redirectToLogin()
redirectToDashboard()
```

## Fluxo de Autenticação

### Login
1. Usuário preenche email e senha
2. Validação no cliente
3. POST para `/api/login`
4. Backend retorna `{ token, token_type }`
5. Token é armazenado no localStorage
6. Usuário é redirecionado para dashboard

### Logout
1. Usuário clica em logout
2. POST para `/api/logout` com token no header
3. Backend revoga o token
4. Token é removido do localStorage
5. Usuário é redirecionado para login

## Fluxo de Requisições Autenticadas

Todas as requisições para endpoints protegidos devem incluir o token no header:

```
Authorization: Bearer {token}
```

O Inertia automaticamente adiciona este header quando você usa `post()`, `get()`, etc., desde que o token esteja armazenado corretamente.

## Segurança

- Tokens são armazenados no localStorage (considere usar httpOnly cookies no futuro)
- Tokens são incluídos em todas as requisições autenticadas
- Usuários inativos (`is_active=false`) são redirecionados para login
- Tokens revogados no backend impedem acesso mesmo se armazenados localmente

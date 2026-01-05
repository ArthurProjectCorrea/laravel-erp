---

# 🧪 Plano de Implementação

## Testes de Autenticação – Backend (Laravel + Pest)

---

## 1️⃣ Objetivo dos Testes

Garantir que o **fluxo completo de autenticação**:

* Funcione corretamente em cenários de sucesso
* Falhe corretamente em cenários inválidos
* Aplique todas as regras de segurança
* Gere e revogue tokens corretamente
* Produza respostas HTTP previsíveis
* Funcione sem frontend, apenas via API

---

## 2️⃣ Premissas de Teste

1. Testes serão executados via:

   ```bash
   php artisan test
   ```
2. Banco de dados:

   * PostgreSQL
   * Banco exclusivo para testes
3. Testes **geram dados reais no banco**
4. Cada teste é:

   * Isolado
   * Reversível (rollback)
5. Redis será usado em testes (rate-limit / sessão)
6. Autenticação via **Sanctum**

---

## 3️⃣ Configuração Inicial de Testes

### 3.1 `.env.testing`

```env
APP_ENV=testing
APP_KEY=base64:testingkey

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_erp_test
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

---

### 3.2 Banco de Testes

* Criar banco:

  ```sql
  CREATE DATABASE laravel_erp_test;
  ```
* Testes devem usar:

  * `RefreshDatabase`

---

## 4️⃣ Organização dos Testes

### Estrutura de diretórios

```
tests/
└── Feature/
    └── Auth/
        ├── LoginTest.php
        ├── LogoutTest.php
        └── MeTest.php
```

---

## 5️⃣ Dados de Teste (Factories)

### Requisitos obrigatórios

* Factory para `User`
* Usuário criado com:

  * Email válido
  * Senha hasheada
  * `is_active = true | false`

---

## 6️⃣ Casos de Teste — LOGIN

### 6.1 Login com sucesso

**Cenário**

* Usuário ativo
* Email e senha corretos

**Validações**

* HTTP 200
* Retorno contém:

  * `access_token`
  * `token_type`
* Token salvo no banco (`personal_access_tokens`)
* Token associado ao usuário correto

---

### 6.2 Login com senha incorreta

**Cenário**

* Email existente
* Senha inválida

**Validações**

* HTTP 422 ou 401
* Mensagem genérica de erro
* Nenhum token criado

---

### 6.3 Login com email inexistente

**Cenário**

* Email não cadastrado

**Validações**

* HTTP 422 ou 401
* Nenhum token criado

---

### 6.4 Login com usuário inativo

**Cenário**

* `is_active = false`

**Validações**

* HTTP 403
* Nenhum token criado

---

### 6.5 Rate Limit no login

**Cenário**

* Múltiplas tentativas inválidas consecutivas

**Validações**

* HTTP 429
* Bloqueio temporário aplicado
* Mensagem de rate-limit retornada

---

## 7️⃣ Casos de Teste — LOGOUT

### 7.1 Logout com sucesso

**Cenário**

* Usuário autenticado
* Token válido

**Validações**

* HTTP 200
* Token removido do banco
* Usuário permanece ativo

---

### 7.2 Logout sem token

**Cenário**

* Requisição sem autenticação

**Validações**

* HTTP 401

---

### 7.3 Logout com token inválido

**Cenário**

* Token inexistente ou revogado

**Validações**

* HTTP 401 ou 200 (idempotência, conforme implementação)
* Nenhuma exceção lançada

---

## 8️⃣ Casos de Teste — `/api/me`

### 8.1 Consulta com token válido

**Cenário**

* Usuário autenticado

**Validações**

* HTTP 200
* Retorno contém:

  * `id`
  * `name`
  * `email`
* Não retorna senha ou dados sensíveis

---

### 8.2 Consulta sem token

**Cenário**

* Requisição anônima

**Validações**

* HTTP 401

---

### 8.3 Consulta com usuário inativo

**Cenário**

* Usuário foi desativado após login

**Validações**

* HTTP 403
* Acesso negado

---

## 9️⃣ Testes de Segurança Complementares

### 9.1 Token não reutilizável após logout

* Login → gera token
* Logout → revoga token
* Nova requisição com token antigo
* Resultado: **401**

---

### 9.2 Tokens múltiplos

* Login duas vezes
* Validar:

  * Dois tokens criados
* Logout com um token
* Validar:

  * Apenas o token atual foi revogado

---

## 🔟 Execução e Ordem dos Testes

1. Migrations
2. Factories
3. Login
4. Rate limit
5. Logout
6. Me

Execução:

```bash
php artisan test --testsuite=Feature
```

---

## 1️⃣1️⃣ Critérios de Aceitação Final

O backend está pronto para frontend quando:

* Todos os testes passam
* Nenhum teste depende de frontend
* Banco de testes é populado e limpo automaticamente
* Tokens são criados e revogados corretamente
* Respostas HTTP são previsíveis
* Segurança mínima validada por testes

---

## 1️⃣2️⃣ O que NÃO será testado agora

* ❌ Reset de senha
* ❌ Envio real de email
* ❌ ACL / permissões
* ❌ Expiração avançada de token
* ❌ Refresh token

---

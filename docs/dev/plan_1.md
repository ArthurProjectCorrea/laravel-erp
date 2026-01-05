---

# 📋 Levantamento de Requisitos

## Autenticação de Usuário – Backend (Laravel)

---

## 1️⃣ Premissas do Projeto

1. O backend será **API-first**.
2. Não haverá **registro público (signup)**.
3. Usuários serão **criados exclusivamente de forma interna** (seeder, comando ou painel futuro).
4. O sistema será **interno e corporativo**, mas deve seguir **boas práticas de segurança**.
5. O frontend **não faz parte deste escopo**.
6. Banco de dados: **PostgreSQL**.
7. Serviços auxiliares (DB, Redis, Mailhog) via **Docker**.
8. O app Laravel será executado localmente via:

   * `php artisan serve`
   * **sem Docker para o app**

---

## 2️⃣ Stack Técnica Obrigatória

### Backend

* Laravel **10 ou 11**
* PHP versão compatível com a versão do Laravel escolhida

### Banco de Dados

* PostgreSQL (via Docker)

### Cache / Sessão

* Redis (via Docker)

### Email

* Mailhog (via Docker)

### Autenticação

* **Laravel Sanctum**
* Tokens do tipo **personal access token**

---

## 3️⃣ Requisitos Funcionais de Autenticação

### 3.1 Login

* Endpoint para login com:

  * `email`
  * `password`
* O email deve ser validado como:

  * existente
  * ativo
* A senha deve:

  * ser comparada usando **Hash nativo do Laravel**
* Em login bem-sucedido:

  * Gerar **token Sanctum**
  * Associar token ao usuário
* Em login inválido:

  * Retornar erro genérico (não revelar se email ou senha está incorreto)

---

### 3.2 Logout

* Endpoint de logout autenticado
* No logout:

  * O token atual **deve ser revogado**
* Não deve invalidar outros tokens do mesmo usuário
* Retornar sucesso mesmo se o token já estiver inválido (idempotência)

---

### 3.3 Controle de Sessão / Tokens

* Cada login gera um **novo token**
* Tokens devem:

  * Ter nome identificável (ex: `web`, `mobile`)
  * Ser armazenados no banco conforme padrão do Sanctum
* Possibilidade futura de:

  * Listar tokens ativos (não implementar agora, apenas permitir estruturalmente)

---

## 4️⃣ Requisitos de Segurança

### 4.1 Rate Limiting

* Aplicar **rate limit** no endpoint de login
* Política obrigatória:

  * Limitar tentativas por IP
  * Limitar tentativas por email
* Bloqueio temporário após exceder tentativas
* Usar **Throttle nativo do Laravel**

---

### 4.2 Cookies e Headers

* Autenticação via **Authorization: Bearer**
* Configurar:

  * Cookies seguros (`secure`, `httpOnly`) para uso futuro
* Não usar autenticação baseada em sessão tradicional (web guard)

---

### 4.3 Hash e Criptografia

* Senhas devem:

  * Usar `bcrypt` (padrão Laravel)
* Nunca armazenar:

  * senha em texto
  * tokens em texto puro fora do padrão Sanctum

---

## 5️⃣ Estrutura de Usuário

### 5.1 Tabela `users`

Campos obrigatórios:

* `id`
* `name`
* `email` (único)
* `email_verified_at` (opcional, preparado)
* `password`
* `is_active` (boolean)
* `created_at`
* `updated_at`

---

### 5.2 Estados do Usuário

* Usuário **inativo**:

  * Não pode logar
* Usuário **ativo**:

  * Pode logar normalmente

---

## 6️⃣ Criação de Usuários (Interna)

* Não existir endpoint público de cadastro
* Usuários devem ser criados via:

  * Seeder
  * Factory
  * Comando Artisan
* A senha inicial deve:

  * Ser definida no momento da criação
  * Estar obrigatoriamente hasheada

---

## 7️⃣ Endpoints Obrigatórios

| Método | Endpoint      | Autenticado | Descrição                   |
| ------ | ------------- | ----------- | --------------------------- |
| POST   | `/api/login`  | ❌           | Login do usuário            |
| POST   | `/api/logout` | ✅           | Logout (revoga token)       |
| GET    | `/api/me`     | ✅           | Retorna usuário autenticado |

---

## 8️⃣ Middleware Obrigatórios

* `auth:sanctum`
* `throttle`
* Middleware customizado para:

  * Bloquear usuários inativos

---

## 9️⃣ Logs e Auditoria (mínimo)

* Registrar:

  * Tentativas de login falhas
  * Login bem-sucedido
  * Logout
* Utilizar:

  * Log nativo do Laravel (`storage/logs`)

---

## 🔟 Docker – Serviços Externos

### Serviços obrigatórios

* PostgreSQL
* Redis
* Mailhog

### Regras

* Docker **apenas para serviços**
* Nenhum container para:

  * Laravel
  * Node
* Conexão via `.env`

---

## 1️⃣1️⃣ Configurações de Ambiente

Obrigatório configurar:

* `DB_CONNECTION=pgsql`
* `CACHE_DRIVER=redis`
* `SESSION_DRIVER=redis`
* `QUEUE_CONNECTION=sync`
* `MAIL_MAILER=smtp`
* `MAIL_HOST=mailhog`
* `MAIL_PORT=1025`

---

## 1️⃣2️⃣ O que NÃO deve ser implementado agora

* ❌ Frontend
* ❌ Registro público
* ❌ Reset de senha
* ❌ Verificação de email
* ❌ ACL / permissões
* ❌ Social login
* ❌ Refresh token customizado

---

## 1️⃣3️⃣ Critérios de Aceitação

A autenticação está correta quando:

* Login funciona apenas para usuários internos
* Rate-limit bloqueia brute force
* Token é criado no login
* Token é revogado no logout
* Usuário inativo não autentica
* Nenhuma dependência de frontend existe
* Todo o fluxo funciona via HTTP client (Postman, Insomnia, curl)

---


# Laravel ERP (Backend + Inertia/React)

Visão geral rápida e guia para desenvolvimento.

## Visão Geral
- Backend: Laravel v12 (app/*), API REST com tokens Sanctum.
- Frontend: Inertia + React (resources/js/*), empacotado com Vite.
- Ambiente de Desenvolvimento: Recursos locais com SQLite. Docker (Sail) é usado apenas para produção com PostgreSQL, Redis e Mailpit.
- Testes: Pest / PHPUnit em `tests/Feature/`.

## Pré-requisitos
- PHP (versão compatível com Laravel v12)
- Composer
- Node.js e npm
- SQLite (incluído no PHP)

**Nota:** Não use Docker para desenvolvimento. Use apenas recursos locais com SQLite. Docker é exclusivo para produção.

## Guia Após Clonar o Repositório
1. Clone o repositório:
   ```bash
   git clone <url-do-repo>
   cd laravel-erp
   ```

2. Instale dependências do PHP:
   ```bash
   composer install
   ```

3. Instale dependências do Node.js:
   ```bash
   npm install
   ```

4. Configure o ambiente:
   - Copie o arquivo `.env.example` para `.env`:
     ```bash
     cp .env.example .env
     ```
   - Edite `.env` para desenvolvimento local com SQLite:
     - `DB_CONNECTION=sqlite`
     - `DB_DATABASE=./database/database.sqlite`
     - Ajuste outras configurações se necessário (ex.: APP_URL=http://localhost:8000)

5. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```

6. Crie o banco de dados SQLite:
   ```bash
   touch database/database.sqlite
   ```

7. Execute as migrações:
   ```bash
   php artisan migrate
   ```

8. Execute as seeds (opcional, para dados iniciais):
   ```bash
   php artisan db:seed
   ```

9. Inicie o servidor backend:
   ```bash
   php artisan serve
   ```
   - Disponível em http://localhost:8000

10. Inicie o servidor frontend (HMR):
    ```bash
    npm run dev
    ```
    - Disponível em http://localhost:5173

## Comandos Úteis
- Executar migrações: `php artisan migrate`
- Executar seeds: `php artisan db:seed`
- Rodar testes: `php artisan test`
- Limpar cache: `php artisan cache:clear`
- Build frontend para produção: `npm run build`

## Ambiente
- Arquivo principal: `.env`
  - Desenvolvimento: SQLite (`DB_CONNECTION=sqlite`, `DB_DATABASE=./database/database.sqlite`)
  - Produção: PostgreSQL via Docker Sail
- Ambiente de teste: `.env.testing` (usa SQLite por padrão, veja `phpunit.xml`)

## API de Autenticação (referência rápida)
- POST `/api/login` — body: `{ email, password }` → retorna token
- POST `/api/logout` — requer `Authorization: Bearer <token>` → revoga token
- GET `/api/me` — requer token → retorna usuário autenticado

Ordem de middleware para rotas protegidas:
1. `auth:sanctum`
2. `token.not.revoked` (custom, verifica tokens revogados)
3. `auth.active` (custom, verifica `is_active` no User)

Veja `routes/api.php` para uso concreto.

## Testes e Padrões
- Testes em `tests/Feature/` (compatível com Pest)
- Testes de auth: `tests/Feature/Auth/`
- Use factories de modelo (`database/factories/`) e `RefreshDatabase`
- Para testes API, use `$this->postJson()` / `$this->getJson()` e passe header: `['Authorization' => 'Bearer ' . $token]`

## Convenções e Diretrizes
- Use método `casts()` nos modelos ao invés de propriedade `$casts`
- Registre aliases de middleware custom em `bootstrap/app.php`
- Ao adicionar endpoints API:
  - Crie controller em `app/Http/Controllers/Api/`
  - Adicione rota em `routes/api.php` e aplique grupo de middleware
  - Adicione testes em `tests/Feature/Api/` e rode com `php artisan test`

## Troubleshooting
- Erro de manifest Vite: rode `npm run build` ou `npm run dev`
- Problemas com banco: verifique se `database/database.sqlite` existe e permissões
- Testes falhando: certifique-se de que o banco de teste está configurado corretamente

## Onde Olhar
- Controller e middleware de auth: `app/Http/Controllers/Api/AuthController.php`, `app/Http/Middleware/`
- Rotas: `routes/api.php`, `routes/web.php`
- Entrada frontend: `resources/js/app.tsx`, páginas em `resources/js/pages/`
- Testes: `tests/Feature/Auth/`

---
Para um README mais detalhado (deploy, CI, etc.), solicite expansões.

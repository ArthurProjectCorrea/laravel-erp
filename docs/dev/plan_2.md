---

## 🐳 docker-compose.yml — Serviços Externos

---

> **Local sugerido**: raiz do projeto
> `laravel-erp/docker-compose.yml`

```yaml
version: "3.9"

services:
  postgres:
    image: postgres:16-alpine
    container_name: laravel-erp-postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: laravel_erp
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: secret
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U laravel"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: laravel-erp-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  mailhog:
    image: mailhog/mailhog:v1.0.1
    container_name: laravel-erp-mailhog
    restart: unless-stopped
    ports:
      - "1025:1025"   # SMTP
      - "8025:8025"   # Web UI
    healthcheck:
      test: ["CMD", "nc", "-z", "localhost", "1025"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  postgres_data:
  redis_data:
```

---

## 📌 Decisões Técnicas (objetivas)

* **Postgres 16 Alpine**

  * Estável, leve e atual
* **Redis 7**

  * Cache + sessão prontos
* **Mailhog**

  * SMTP local para auth e reset futuro
* **Volumes nomeados**

  * Persistência entre restarts
* **Healthchecks**

  * Facilita debug e evolução futura

---

## 🔌 Configuração do `.env` do Laravel

> Laravel roda **fora do Docker**

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_erp
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=no-reply@laravel-erp.local
MAIL_FROM_NAME="Laravel ERP"
```

---

## ▶️ Comandos de Uso

Subir serviços:

```bash
docker compose up -d
```

Ver status:

```bash
docker compose ps
```

Logs (exemplo):

```bash
docker compose logs postgres
```

Parar serviços:

```bash
docker compose down
```

---

## 🌐 Acessos Locais

* **Postgres**

  * Host: `127.0.0.1`
  * Porta: `5432`
* **Redis**

  * Host: `127.0.0.1`
  * Porta: `6379`
* **Mailhog**

  * SMTP: `localhost:1025`
  * UI: [http://localhost:8025](http://localhost:8025)

---

## ✅ Critério de Aceitação

* `php artisan migrate` conecta no Postgres ✔️
* Cache/session funcionando via Redis ✔️
* Emails aparecem no Mailhog ✔️
* Laravel roda com `php artisan serve` sem Docker ✔️

---

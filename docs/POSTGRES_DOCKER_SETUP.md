# Configuração PostgreSQL com Docker

## ✅ Implementação Completa

O projeto agora está totalmente configurado para usar **PostgreSQL com Docker**!

## Como Funciona

### Stack Implementado
- **PostgreSQL 16-Alpine**: Banco de dados principal
- **Redis 7-Alpine**: Cache e sessões
- **Mailhog v1.0.1**: SMTP local para testes de email
- **PHP 8.4 Docker**: Container customizado com `pdo_pgsql` instalado

### Arquivo Docker Compose
O `docker-compose.yml` define 4 serviços:

1. **app** (PHP 8.4): Executa Laravel/Artisan
2. **postgres**: Banco de dados PostgreSQL
3. **redis**: Cache e sessões
4. **mailhog**: Interface web para testar emails

## Iniciando o Ambiente

### 1. Subir Containers
```bash
docker compose up -d
```

### 2. Executar Migrações
```bash
docker compose run --rm app artisan migrate:fresh --seed
```

### 3. Rodar o Servidor Laravel
```bash
docker compose run --rm app artisan serve --host=0.0.0.0
```

## Acessar Serviços

### PostgreSQL
- **Host**: `localhost` ou `127.0.0.1`
- **Porta**: `5432`
- **Usuário**: `laravel`
- **Senha**: `secret`
- **Banco**: `laravel_erp`

### Redis
- **Host**: `localhost`
- **Porta**: `6379`

### Mailhog
- **SMTP**: `localhost:1025`
- **Web UI**: http://localhost:8025

## Comandos Úteis

```bash
# Ver logs de um serviço
docker compose logs postgres
docker compose logs app

# Executar comando artisan
docker compose run --rm app artisan tinker

# Executar testes
docker compose run --rm app artisan test

# Parar todos os containers
docker compose down

# Parar e remover volumes (reset da DB)
docker compose down -v
```

## Variáveis de Ambiente

As credenciais no `.env` são:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel_erp
DB_USERNAME=laravel
DB_PASSWORD=secret
```

**Nota**: O `DB_HOST=postgres` refere-se ao nome do serviço Docker (não localhost).

## Dockerfile Customizado

Criamos um `Dockerfile` que estende `php:8.4-cli-alpine` com:
- PostgreSQL dev libraries
- PDO PostgreSQL extension (`pdo_pgsql`)

Isso permite que o PHP dentro do Docker se conecte ao PostgreSQL!

## Estrutura de Volumes

- `postgres_data/`: Persiste dados do PostgreSQL entre restarts
- `redis_data/`: Persiste dados do Redis

Remova com `docker compose down -v` se precisar resetar.

## Próximas Etapas

1. ✅ PostgreSQL funcionando
2. ✅ Redis para cache/session
3. ✅ Mailhog para testes de email
4. ⬜ Implementar endpoints da API de autenticação
5. ⬜ Testes integrados

---

# Laravel Sail Setup - PostgreSQL + Vite

## Configuração Completa ✅

Laravel Sail foi instalado e configurado com sucesso para desenvolvimento local com:

### Serviços Docker Rodando:
- **laravel.test** - PHP 8.5 + Laravel com Supervisor
  - Porta 80 (HTTP)
  - Porta 5173 (Vite - para React HMR)
- **postgres** - PostgreSQL 16-Alpine
  - Porta 5432
  - Database: laravel_erp
  - User: laravel / Password: secret
- **redis** - Redis 7-Alpine
  - Porta 6379
- **mailpit** - Email testing
  - SMTP: localhost:1025
  - Dashboard: http://localhost:8025
- **selenium** - Browser testing (Pest v4)
  - Porta 4444

## Comandos Úteis

### Iniciar serviços (já está rodando)
```bash
docker compose up -d
```

### Parar serviços
```bash
docker compose down
```

### Executar artisan dentro do container
```bash
docker compose exec -T laravel.test php artisan <command>
```

### Ver logs em tempo real
```bash
docker compose logs -f laravel.test
```

### Acessar shell do container
```bash
docker compose exec laravel.test bash
```

## Desenvolvimento

### API (Laravel + Sanctum)
- Roda automaticamente na porta 80 com `php artisan serve`
- Logs visíveis em: `docker compose logs -f laravel.test`
- Base URL: `http://localhost`
- Endpoints configurados em `routes/api.php`

### Frontend (React + Vite)
- **Status**: Precisa ser iniciado manualmente via npm run dev
- Porta: 5173 (HMR já está exposta no compose.yaml)
- Comando: `npm run dev`
- Build para produção: `npm run build`

## Próximas Etapas

1. Iniciar o React dev server em outro terminal:
   ```bash
   npm run dev
   ```

2. Acessar a aplicação em `http://localhost:5173` (Vite) ou `http://localhost` (Laravel)

3. Testar login na API:
   ```bash
   curl -X POST http://localhost/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

## Dados de Teste

**Usuário ativo**:
- Email: `test@example.com`
- Password: `password`

**Usuário inativo**:
- Email: `inactive@example.com`
- Password: `password`

## Migrations & Seeding

Já executadas! Para resetar o banco:
```bash
docker compose exec -T laravel.test php artisan migrate:fresh --seed
```

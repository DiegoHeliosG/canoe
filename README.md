# Silitech

## Stack

| Service    | Version    | Host Port |
|------------|------------|-----------|
| Nginx      | latest     | 8080      |
| PHP (FPM)  | 8.4        | -         |
| MySQL      | latest     | 8306      |
| Redis      | latest     | 6379      |
| RabbitMQ   | latest     | 5672 / 15672 |

## Frontend

- Vue 3
- Vite
- Tailwind CSS

## Backend

- Laravel 12

## Getting Started

```bash
docker compose up -d --build
docker compose exec php php artisan migrate
npm run build  # inside the php container
```

The app is available at http://localhost:8080

## Credentials

### MySQL

- Host: `mysql` (internal) / `localhost:8306` (external)
- Database: `silitech`
- User: `silitech`
- Password: `silitech`

### RabbitMQ

- Host: `rabbitmq` (internal) / `localhost:5672` (external)
- Management UI: http://localhost:15672
- User: `silitech`
- Password: `silitech`

#!/usr/bin/env bash
set -e

echo "==> Starting Docker services..."
docker compose up -d --build

echo "==> Waiting for MySQL to be ready..."
until docker compose exec php php -r "new PDO('mysql:host=mysql;dbname=silitech', 'root', 'root');" >/dev/null 2>&1; do
    sleep 2
done

echo "==> Installing PHP dependencies..."
docker compose exec php composer install --no-interaction

echo "==> Setting up environment..."
docker compose exec php cp -n .env.example .env || true
docker compose exec php php artisan key:generate --no-interaction --force

echo "==> Clearing caches..."
docker compose exec php php artisan config:clear --no-interaction
docker compose exec php php artisan route:clear --no-interaction
docker compose exec php php artisan cache:clear --no-interaction

echo "==> Running migrations and seeding..."
docker compose exec php php artisan migrate:fresh --seed --no-interaction

echo "==> Updating npm and installing Node dependencies..."
docker compose exec php npm install -g npm@latest
docker compose exec php npm install

echo "==> Building frontend assets..."
docker compose exec php npm run build

echo "==> Running tests..."
docker compose exec php php artisan test

echo "==> Starting queue worker in background..."
docker compose exec -d php php artisan queue:work

echo ""
echo "==> Done! Application is available at http://localhost:8080"
echo "    RabbitMQ Management UI: http://localhost:15672 (silitech/silitech)"
